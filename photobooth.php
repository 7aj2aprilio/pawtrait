<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';
?>

<main>
    <section class="photobooth-section">
        <div class="container">
            <h1 class="section-title">Photo Booth</h1>
            <p class="section-subtitle">Capture your amazing moments with our professional photobooth</p>
            
            <div class="photobooth-container">
                <div class="camera-section">
                    <div class="camera-wrapper">
                        <video id="camera-stream" autoplay playsinline></video>
                        <canvas id="camera-canvas" style="display: none;"></canvas>
                        <div id="camera-overlay" class="camera-overlay">
                            <div class="camera-frame"></div>
                        </div>
                    </div>
                    
                    <div class="camera-controls">
                        <button id="start-camera" class="btn-primary">
                            <span>📷</span> Start Camera
                        </button>
                        <button id="capture-photo" class="btn-secondary" style="display: none;">
                            <span>📸</span> Capture Photo
                        </button>
                        <button id="stop-camera" class="btn-logout" style="display: none;">
                            <span>⏹️</span> Stop Camera
                        </button>
                    </div>
                    
                    <div class="filter-controls" id="filter-controls" style="display: none;">
                        <h3>Apply Filters</h3>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="none">None</button>
                            <button class="filter-btn" data-filter="grayscale">B&W</button>
                            <button class="filter-btn" data-filter="sepia">Sepia</button>
                            <button class="filter-btn" data-filter="blur">Blur</button>
                            <button class="filter-btn" data-filter="brightness">Bright</button>
                            <button class="filter-btn" data-filter="contrast">Contrast</button>
                        </div>
                    </div>
                </div>
                
                <div class="gallery-section">
                    <h3>Captured Photos</h3>
                    <div id="photo-gallery" class="photo-gallery">
                        <p class="empty-gallery">No photos captured yet. Start your camera to begin!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.photobooth-section {
    padding: var(--spacing-xl) 0;
    min-height: 80vh;
}

.photobooth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.camera-section {
    background: rgba(255, 255, 255, 0.05);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.camera-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #000;
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-bottom: var(--spacing-md);
}

#camera-stream {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.camera-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.camera-frame {
    position: absolute;
    inset: 10%;
    border: 3px solid rgba(255, 255, 255, 0.5);
    border-radius: var(--radius-md);
}

.camera-controls {
    display: flex;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
}

.camera-controls button {
    flex: 1;
}

.filter-controls {
    margin-top: var(--spacing-md);
}

.filter-controls h3 {
    margin-bottom: var(--spacing-sm);
    font-size: 1.2rem;
}

.filter-buttons {
    display: flex;
    gap: var(--spacing-xs);
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition-fast);
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary);
    border-color: var(--primary);
}

.gallery-section {
    background: rgba(255, 255, 255, 0.05);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.gallery-section h3 {
    margin-bottom: var(--spacing-md);
    font-size: 1.5rem;
}

.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--spacing-sm);
    max-height: 600px;
    overflow-y: auto;
}

.empty-gallery {
    grid-column: 1 / -1;
    text-align: center;
    color: var(--text-muted);
    padding: var(--spacing-xl);
}

.photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition-fast);
}

.photo-item:hover {
    border-color: var(--primary);
    transform: scale(1.05);
}

.photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    padding: 0.5rem;
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: var(--transition-fast);
}

.photo-item:hover .photo-actions {
    opacity: 1;
}

.photo-actions button {
    flex: 1;
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .photobooth-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="assets/js/photobooth.js"></script>

<?php require_once 'includes/footer.php'; ?>
