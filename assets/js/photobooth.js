// Photobooth Camera Functionality
let cameraStream = null;
let currentFilter = 'none';
let capturedPhotos = [];
let framePhotos = [null, null, null]; // 3 slots for frame
let activeSlot = null;

const videoElement = document.getElementById('camera-stream');
const canvasElement = document.getElementById('camera-canvas');
const startCameraBtn = document.getElementById('start-camera');
const captureBtnBtn = document.getElementById('capture-photo');
const stopCameraBtn = document.getElementById('stop-camera');
const filterControls = document.getElementById('filter-controls');
const photoGallery = document.getElementById('photo-gallery');
const frameCanvas = document.getElementById('frame-canvas');
const frameImage = document.getElementById('frame-image');
const countdownOverlay = document.getElementById('countdown-overlay');
const flashOverlay = document.getElementById('flash-overlay');

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

// Capture photo with countdown
captureBtnBtn.addEventListener('click', () => {
    // Start countdown
    let timeLeft = 5;
    countdownOverlay.textContent = timeLeft;
    countdownOverlay.style.display = 'flex';
    captureBtnBtn.disabled = true;

    const countdownInterval = setInterval(() => {
        timeLeft--;
        if (timeLeft > 0) {
            countdownOverlay.textContent = timeLeft;
        } else {
            clearInterval(countdownInterval);
            countdownOverlay.style.display = 'none';
            performCapture();
        }
    }, 1000);
});

// Actual capture logic
async function performCapture() {
    // Set canvas size to match video
    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;

    const ctx = canvasElement.getContext('2d');

    // Apply filter to canvas
    ctx.filter = filters[currentFilter];

    // Draw video frame to canvas
    ctx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

    // Show flash animation
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
}

// Display photo in gallery
function displayPhoto(photoData) {
    // Remove empty message if exists
    const emptyMsg = photoGallery.querySelector('.empty-gallery');
    if (emptyMsg) {
        emptyMsg.remove();
    }

    const photoItem = document.createElement('div');
    photoItem.className = 'photo-item';
    photoItem.dataset.photoId = photoData.id;
    photoItem.innerHTML = `
        <img src="${photoData.url}" alt="Captured photo">
        <div class="photo-actions">
            <button class="btn-primary" onclick="event.stopPropagation(); downloadPhoto('${photoData.url}')">⬇️</button>
            <button class="btn-logout" onclick="event.stopPropagation(); deletePhoto(${photoData.id})">🗑️</button>
        </div>
    `;

    // Click to add to frame
    photoItem.addEventListener('click', () => {
        if (activeSlot !== null) {
            addPhotoToSlot(activeSlot, photoData);
            closePhotoSelectModal();
        }
    });

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
            const index = capturedPhotos.findIndex(p => p.id == photoId);
            if (index > -1) {
                // Revoke object URL to free memory
                if (capturedPhotos[index].url && capturedPhotos[index].url.startsWith('blob:')) {
                    URL.revokeObjectURL(capturedPhotos[index].url);
                }
                capturedPhotos.splice(index, 1);

                // Remove from DOM
                const photoItem = photoGallery.querySelector(`[data-photo-id="${photoId}"]`);
                if (photoItem) {
                    photoItem.remove();
                }

                if (capturedPhotos.length === 0) {
                    photoGallery.innerHTML = '<p class="empty-gallery">No photos captured yet. Start your camera to begin!</p>';
                }
            }

            // Also remove from frame if it was used
            framePhotos.forEach((photo, idx) => {
                if (photo && photo.id == photoId) {
                    framePhotos[idx] = null;
                    updateSlotDisplay(idx);
                }
            });
        } else {
            alert('Failed to delete photo: ' + result.message);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Error deleting photo');
    }
}

// Capture animation with full screen flash
function showCaptureAnimation() {
    flashOverlay.classList.add('active');
    setTimeout(() => {
        flashOverlay.classList.remove('active');
    }, 100);
}

// ==================== FRAME FUNCTIONS ====================

// Frame Selection Logic
// Frame Selection Logic
const defaultLayout = [ // Base Default
    { top: 0.185, left: 0.097, width: 0.805, height: 0.208 },
    { top: 0.423, left: 0.097, width: 0.805, height: 0.209 },
    { top: 0.659, left: 0.097, width: 0.805, height: 0.208 }
];

const frame1Layout = [ // Frame 1 (Memories)
    { top: 0.150, left: 0.110, width: 0.805, height: 0.208 },
    { top: 0.388, left: 0.110, width: 0.805, height: 0.209 },
    { top: 0.626, left: 0.110, width: 0.805, height: 0.208 }
];

const frame2Layout = [ // Frame 2 (Black Cats)
    { top: 0.10, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.33, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.55, left: 0.10, width: 0.80, height: 0.20 }
];

const frame3Layout = [ // Frame 3 (Red Bull - Black)
    { top: 0.21, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.42, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.61, left: 0.10, width: 0.80, height: 0.20 }
];

const frame4Layout = [ // Frame 4 (PAW Trait - Pink/Checkered)
    { top: 0.19, left: 0.10, width: 0.80, height: 0.209 },
    { top: 0.42, left: 0.10, width: 0.80, height: 0.209 },
    { top: 0.65, left: 0.10, width: 0.80, height: 0.209 }
];

const frame5Layout = [ // Frame 5 (Cool Cat)
    { top: 0.218, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.446, left: 0.10, width: 0.80, height: 0.20 },
    { top: 0.676, left: 0.10, width: 0.80, height: 0.20 }
];

const frameConfigs = {
    'default-frame.png': defaultLayout,
    'frame-1.png': frame1Layout,
    'frame-2.png': frame2Layout,
    'frame-3.png': frame3Layout,
    'frame-4.png': frame4Layout,
    'frame-5.png': frame5Layout
};

document.querySelectorAll('.frame-option').forEach(option => {
    option.addEventListener('click', () => {
        // Remove active class from all options
        document.querySelectorAll('.frame-option').forEach(opt => opt.classList.remove('active'));

        // Add active class to clicked option
        option.classList.add('active');

        // Update frame image
        const newSrc = option.dataset.src;
        frameImage.src = newSrc;

        // Get filename for config lookup
        const filename = newSrc.split('/').pop();
        const config = frameConfigs[filename] || defaultLayout;
        applyFrameLayout(config);
    });
});

// Helper to apply layout
function applyFrameLayout(config) {
    config.forEach((pos, index) => {
        const slot = document.getElementById(`slot-${index}`);
        if (slot) {
            slot.style.top = (pos.top * 100) + '%';
            slot.style.left = (pos.left * 100) + '%';
            slot.style.width = (pos.width * 100) + '%';
            slot.style.height = (pos.height * 100) + '%';
        }
    });
}

// Initialize layout on load
window.addEventListener('DOMContentLoaded', () => {
    // Determine current frame filename from src (initially default)
    let currentFrameFile = frameImage.src.split('/').pop() || 'default-frame.png';
    const config = frameConfigs[currentFrameFile] || defaultLayout;
    applyFrameLayout(config);
});


// Photo slot click handlers
document.querySelectorAll('.photo-slot').forEach(slot => {
    slot.addEventListener('click', () => {
        const slotIndex = parseInt(slot.dataset.slot);
        openPhotoSelectModal(slotIndex);
    });
});

// Open photo selection modal
function openPhotoSelectModal(slotIndex) {
    activeSlot = slotIndex;
    document.getElementById('selected-slot-num').textContent = slotIndex + 1;

    const grid = document.getElementById('photo-select-grid');
    grid.innerHTML = '';

    if (capturedPhotos.length === 0) {
        grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: var(--text-muted);">No photos available. Capture some photos first!</p>';
    } else {
        capturedPhotos.forEach(photo => {
            const item = document.createElement('div');
            item.className = 'photo-select-item';
            item.innerHTML = `<img src="${photo.url}" alt="Photo">`;
            item.addEventListener('click', () => {
                addPhotoToSlot(slotIndex, photo);
                closePhotoSelectModal();
            });
            grid.appendChild(item);
        });
    }

    document.getElementById('photo-select-overlay').classList.add('show');

    // Highlight active slot
    document.querySelectorAll('.photo-slot').forEach(s => s.classList.remove('active'));
    document.getElementById(`slot-${slotIndex}`).classList.add('active');
}

// Close photo selection modal
function closePhotoSelectModal() {
    document.getElementById('photo-select-overlay').classList.remove('show');
    document.querySelectorAll('.photo-slot').forEach(s => s.classList.remove('active'));
    activeSlot = null;
}

// Add photo to slot
function addPhotoToSlot(slotIndex, photoData) {
    framePhotos[slotIndex] = photoData;
    updateSlotDisplay(slotIndex);
}

// Update slot visual display
function updateSlotDisplay(slotIndex) {
    const slot = document.getElementById(`slot-${slotIndex}`);
    const photo = framePhotos[slotIndex];

    if (photo) {
        slot.innerHTML = `<img src="${photo.url}" alt="Slot ${slotIndex + 1}">`;
        slot.classList.add('filled');
    } else {
        slot.innerHTML = `
            <span class="slot-label">+</span>
            <span class="slot-text">Photo ${slotIndex + 1}</span>
        `;
        slot.classList.remove('filled');
    }
}

// Clear all frame photos
document.getElementById('clear-frame').addEventListener('click', () => {
    framePhotos = [null, null, null];
    for (let i = 0; i < 3; i++) {
        updateSlotDisplay(i);
    }
});

// Download frame with photos
document.getElementById('download-frame').addEventListener('click', async () => {
    // Check if at least one photo is added
    const hasPhotos = framePhotos.some(p => p !== null);
    if (!hasPhotos) {
        alert('Please add at least one photo to the frame before downloading.');
        return;
    }

    // Wait for frame image to load
    const frameImg = new Image();
    frameImg.crossOrigin = 'anonymous';
    frameImg.src = frameImage.src;

    frameImg.onload = async () => {
        const canvas = frameCanvas;
        const ctx = canvas.getContext('2d');

        // Set canvas size to match frame proportions
        canvas.width = frameImg.naturalWidth;
        canvas.height = frameImg.naturalHeight;



        // Frame-specific configurations
        // Removed local override to use global `frameConfigs`


        // Determine current frame filename from src
        let currentFrameFile = frameImage.src.split('/').pop() || 'default-frame.png';

        // Use config for current frame, or fallback to default
        const slotPositions = frameConfigs[currentFrameFile] || defaultLayout;

        // Load and draw each photo
        for (let i = 0; i < 3; i++) {
            if (framePhotos[i]) {
                const photo = framePhotos[i];
                const pos = slotPositions[i];

                const img = new Image();
                img.crossOrigin = 'anonymous';

                await new Promise((resolve) => {
                    img.onload = () => {
                        const x = pos.left * canvas.width;
                        const y = pos.top * canvas.height;
                        const w = pos.width * canvas.width;
                        const h = pos.height * canvas.height;

                        // Draw photo with cover-like behavior
                        drawImageCover(ctx, img, x, y, w, h);
                        resolve();
                    };
                    img.onerror = resolve;
                    img.src = photo.url;
                });
            }
        }

        // Draw frame on top
        ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);

        // Download the result
        const link = document.createElement('a');
        link.download = `pawtrait_frame_${Date.now()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    };

    frameImg.onerror = () => {
        alert('Failed to load frame image. Please try again.');
    };
});

// Helper function to draw image with cover behavior
function drawImageCover(ctx, img, x, y, w, h) {
    const imgRatio = img.width / img.height;
    const slotRatio = w / h;

    let sx, sy, sw, sh;

    if (imgRatio > slotRatio) {
        sh = img.height;
        sw = sh * slotRatio;
        sx = (img.width - sw) / 2;
        sy = 0;
    } else {
        sw = img.width;
        sh = sw / slotRatio;
        sx = 0;
        sy = (img.height - sh) / 2;
    }

    ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
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
                capturedPhotos.push(photoData);
                displayPhoto(photoData);
            });
        }
    } catch (error) {
        console.error('Error loading photos:', error);
    }
});

// Close modal when clicking outside
document.getElementById('photo-select-overlay').addEventListener('click', (e) => {
    if (e.target.id === 'photo-select-overlay') {
        closePhotoSelectModal();
    }
});
