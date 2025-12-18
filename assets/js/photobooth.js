// Photobooth Camera Functionality
let cameraStream = null;
let currentFilter = 'none';
let capturedPhotos = [];

const videoElement = document.getElementById('camera-stream');
const canvasElement = document.getElementById('camera-canvas');
const startCameraBtn = document.getElementById('start-camera');
const captureBtnBtn = document.getElementById('capture-photo');
const stopCameraBtn = document.getElementById('stop-camera');
const filterControls = document.getElementById('filter-controls');
const photoGallery = document.getElementById('photo-gallery');

// Filter definitions
const filters = {
    none: '',
    grayscale: 'grayscale(100%)',
    sepia: 'sepia(100%)',
    blur: 'blur(2px)',
    brightness: 'brightness(1.3)',
    contrast: 'contrast(1.5)'
};

// Start camera
startCameraBtn.addEventListener('click', async () => {
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 1280, height: 720 },
            audio: false
        });

        videoElement.srcObject = cameraStream;

        // Update UI
        startCameraBtn.style.display = 'none';
        captureBtnBtn.style.display = 'block';
        stopCameraBtn.style.display = 'block';
        filterControls.style.display = 'block';

    } catch (error) {
        alert('Error accessing camera: ' + error.message);
        console.error('Camera error:', error);
    }
});

// Stop camera
stopCameraBtn.addEventListener('click', () => {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        videoElement.srcObject = null;

        // Update UI
        startCameraBtn.style.display = 'block';
        captureBtnBtn.style.display = 'none';
        stopCameraBtn.style.display = 'none';
        filterControls.style.display = 'none';
    }
});

// Apply filter
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Update active state
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Apply filter
        currentFilter = btn.dataset.filter;
        videoElement.style.filter = filters[currentFilter];
    });
});

// Capture photo
captureBtnBtn.addEventListener('click', () => {
    // Set canvas size to match video
    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;

    const ctx = canvasElement.getContext('2d');

    // Apply filter to canvas
    ctx.filter = filters[currentFilter];

    // Draw video frame to canvas
    ctx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

    // Convert to blob and save
    // Show capture animation immediately for feedback
    showCaptureAnimation();

    // Disable button and show loading state
    const originalBtnText = captureBtnBtn.innerHTML;
    captureBtnBtn.disabled = true;
    captureBtnBtn.innerHTML = '<span>⏳</span> Saving...';

    // Convert to blob and save
    canvasElement.toBlob(async (blob) => {
        // Upload to server first to get ID
        const uploadResult = await uploadPhoto(blob, currentFilter);

        // Reset button
        captureBtnBtn.disabled = false;
        captureBtnBtn.innerHTML = originalBtnText;

        if (uploadResult && uploadResult.success) {
            const photoData = {
                id: uploadResult.photo_id,
                blob: blob,
                url: URL.createObjectURL(blob),
                filter: currentFilter,
                timestamp: Date.now()
            };

            capturedPhotos.push(photoData);
            displayPhoto(photoData);

        } else {
            alert('Failed to save photo. Please try again.');
        }
    }, 'image/jpeg', 0.95);
});

// Display photo in gallery
function displayPhoto(photoData) {
    // Remove empty message if exists
    const emptyMsg = photoGallery.querySelector('.empty-gallery');
    if (emptyMsg) {
        emptyMsg.remove();
    }

    const photoItem = document.createElement('div');
    photoItem.className = 'photo-item';
    photoItem.innerHTML = `
        <img src="${photoData.url}" alt="Captured photo">
        <div class="photo-actions">
            <button class="btn-primary" onclick="downloadPhoto('${photoData.url}')">Download</button>
            <button class="btn-logout" onclick="deletePhoto(${photoData.id})">Delete</button>
        </div>
    `;

    photoGallery.insertBefore(photoItem, photoGallery.firstChild);
}

// Upload photo to server
async function uploadPhoto(blob, filter) {
    const formData = new FormData();
    formData.append('photo', blob, `photo_${Date.now()}.jpg`);
    formData.append('filter', filter);

    try {
        const response = await fetch('api/upload-photo.php', {
            method: 'POST',
            body: formData
        });

        return await response.json();
    } catch (error) {
        console.error('Upload error:', error);
        return { success: false };
    }
}

// Download photo
function downloadPhoto(url) {
    const a = document.createElement('a');
    a.href = url;
    a.download = `photobooth_${Date.now()}.jpg`;
    a.click();
}

// Delete photo
async function deletePhoto(photoId) {
    if (!confirm('Are you sure you want to delete this photo?')) return;

    try {
        const response = await fetch('api/delete-photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ photo_id: photoId })
        });

        const result = await response.json();

        if (result.success) {
            const index = capturedPhotos.findIndex(p => p.id === photoId);
            if (index > -1) {
                // Revoke object URL to free memory
                if (capturedPhotos[index].url.startsWith('blob:')) {
                    URL.revokeObjectURL(capturedPhotos[index].url);
                }
                capturedPhotos.splice(index, 1);

                // Remove from DOM - Find element by button click context or reload gallery
                // Simpler: reload gallery from array since we just removed it
                photoGallery.innerHTML = '';
                if (capturedPhotos.length === 0) {
                    photoGallery.innerHTML = '<p class="empty-gallery">No photos captured yet. Start your camera to begin!</p>';
                } else {
                    // Re-render in reverse order (newest first)
                    [...capturedPhotos].reverse().forEach(photo => displayPhoto(photo));
                }
            }
        } else {
            alert('Failed to delete photo: ' + result.message);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Error deleting photo');
    }
}

// Capture animation
function showCaptureAnimation() {
    const overlay = document.querySelector('.camera-overlay');
    overlay.style.background = 'rgba(255, 255, 255, 0.8)';
    setTimeout(() => {
        overlay.style.background = '';
    }, 150);
}

// Load existing photos on page load
window.addEventListener('load', async () => {
    try {
        const response = await fetch('api/get-photos.php');
        const result = await response.json();

        if (result.success && result.photos.length > 0) {
            result.photos.forEach(photo => {
                const photoData = {
                    id: photo.id,
                    url: photo.file_path,
                    filter: photo.filter_applied,
                    timestamp: new Date(photo.created_at).getTime()
                };
                displayPhoto(photoData);
            });
        }
    } catch (error) {
        console.error('Error loading photos:', error);
    }
});
