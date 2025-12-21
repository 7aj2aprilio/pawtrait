<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/subscription.php';
require_once 'includes/header.php';

// Get subscription info
$subscriptionStatus = getSubscriptionStatus($pdo, $_SESSION['user_id']);
$framesData = getAccessibleFrames($pdo, $_SESSION['user_id']);
$photoCheck = canTakePhoto($pdo, $_SESSION['user_id']);
?>

<main>
    <section class="photobooth-section maximized">
        <div class="container-fluid">
            <h1 class="section-title">Photo Booth</h1>
            <p class="section-subtitle">Capture your amazing moments with our professional photobooth</p>
            
            <!-- Subscription Status Banner -->
            <div class="subscription-banner <?= $subscriptionStatus['is_trial'] ? 'trial' : 'paid' ?>">
                <div class="banner-info">
                    <span class="banner-package"><?= htmlspecialchars($subscriptionStatus['package_name']) ?></span>
                    <?php if ($subscriptionStatus['is_trial']): ?>
                        <span class="banner-quota">📸 <?= $subscriptionStatus['photo_remaining'] ?>/<?= $subscriptionStatus['photo_max'] ?> foto tersisa</span>
                    <?php elseif (isset($subscriptionStatus['expires_in'])): ?>
                        <span class="banner-expires">⏱️ Berlaku <?= $subscriptionStatus['expires_in'] ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($subscriptionStatus['is_trial'] && $subscriptionStatus['photo_remaining'] <= 3): ?>
                    <a href="packages.php" class="btn-upgrade">Upgrade Paket</a>
                <?php elseif ($subscriptionStatus['can_upload_frames']): ?>
                    <button id="upload-frame-btn" class="btn-upload-frame">📤 Upload Frame</button>
                <?php endif; ?>
            </div>
            
            <?php if (!$photoCheck['can_take']): ?>
            <div class="limit-reached-alert">
                <span>⚠️ <?= htmlspecialchars($photoCheck['message']) ?></span>
                <a href="packages.php" class="btn-primary">Beli Paket</a>
            </div>
            <?php endif; ?>
            
            <div class="photobooth-container">
                <!-- Frame Editor Section -->
                <div class="frame-section">
                    <h3>📸 Frame Editor</h3>
                    <p class="frame-subtitle">Add 3 photos to your frame</p>
                    
                    <div class="frame-editor">
                        <div class="frame-container">
                            <img src="assets/images/frames/default-frame.png" alt="Frame" class="frame-image" id="frame-image">
                            
                            <!-- Photo Slots -->
                            <div class="photo-slot" id="slot-0" data-slot="0">
                                <span class="slot-label">+</span>
                                <span class="slot-text">Photo 1</span>
                            </div>
                            <div class="photo-slot" id="slot-1" data-slot="1">
                                <span class="slot-label">+</span>
                                <span class="slot-text">Photo 2</span>
                            </div>
                            <div class="photo-slot" id="slot-2" data-slot="2">
                                <span class="slot-label">+</span>
                                <span class="slot-text">Photo 3</span>
                            </div>
                        </div>

                        <!-- Frame Selector (Dynamic based on subscription) -->
                        <div class="frame-selector-container">
                            <h4>Pilih Frame (<?= count($framesData['frames']) ?>/<?= $framesData['max_frames'] ?>)</h4>
                            <div class="frame-selector" id="frame-selector">
                                <?php foreach ($framesData['frames'] as $index => $frame): ?>
                                <div class="frame-option <?= $index === 0 ? 'active' : '' ?>" 
                                     data-src="<?= htmlspecialchars($frame['path']) ?>"
                                     <?= isset($frame['is_custom']) ? 'data-custom="true"' : '' ?>>
                                    <img src="<?= htmlspecialchars($frame['path']) ?>" alt="<?= htmlspecialchars($frame['filename']) ?>">
                                    <?php if (isset($frame['is_custom'])): ?>
                                    <span class="custom-badge">Custom</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($framesData['can_upload'] && $framesData['current_count'] < $framesData['max_frames']): ?>
                            <p class="frame-upload-hint">💡 Kamu bisa upload <?= $framesData['max_frames'] - $framesData['current_count'] ?> frame lagi</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="frame-actions">
                            <button id="clear-frame" class="btn-logout">
                                <span>🗑️</span> Clear
                            </button>
                            <button id="download-frame" class="btn-primary">
                                <span>💾</span> Download
                            </button>
                        </div>
                    </div>
                    
                    <canvas id="frame-canvas" style="display: none;"></canvas>
                </div>

                <div class="camera-section">
                    <div class="camera-wrapper">
                        <video id="camera-stream" autoplay playsinline></video>
                        <canvas id="camera-canvas" style="display: none;"></canvas>
                        <div id="camera-overlay" class="camera-overlay">
                            <div class="camera-frame"></div>
                            <div id="countdown-overlay" class="countdown-overlay" style="display: none;">5</div>
                        </div>
                    </div>
                    <div id="flash-overlay" class="flash-overlay"></div>
                    
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
                    <p class="gallery-hint">Click a photo to add it to the frame</p>
                    <div id="photo-gallery" class="photo-gallery">
                        <p class="empty-gallery">No photos captured yet. Start your camera to begin!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.photobooth-section.maximized .container-fluid { max-width: 1600px; margin: 0 auto; padding: 0 var(--spacing-md); }
.photobooth-container { display: grid; grid-template-columns: 280px 1fr 300px; gap: var(--spacing-lg); margin-top: var(--spacing-xl); align-items: start; }
.camera-section, .frame-section, .gallery-section { background: rgba(255,255,255,0.05); padding: var(--spacing-lg); border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.1); }
.camera-wrapper { position: relative; width: 100%; aspect-ratio: 4/3; background: #000; border-radius: var(--radius-md); overflow: hidden; margin-bottom: var(--spacing-md); }
#camera-stream { width: 100%; height: 100%; object-fit: cover; }
.camera-overlay { position: absolute; inset: 0; pointer-events: none; }
.camera-frame { position: absolute; inset: 10%; border: 3px solid rgba(255,255,255,0.5); border-radius: var(--radius-md); }
.countdown-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 8rem; font-weight: 800; color: white; text-shadow: 0 0 20px rgba(0,0,0,0.5); z-index: 10; font-family: 'Inter', sans-serif; pointer-events: none; }
.flash-overlay { position: fixed; inset: 0; background: white; opacity: 0; pointer-events: none; z-index: 9999; transition: opacity 0.1s ease-out; }
.flash-overlay.active { opacity: 1; transition: none; }
.camera-controls { display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md); }
.camera-controls button { flex: 1; }
.filter-controls { margin-top: var(--spacing-md); }
.filter-controls h3 { margin-bottom: var(--spacing-sm); font-size: 1.2rem; }
.filter-buttons { display: flex; gap: var(--spacing-xs); flex-wrap: wrap; }
.filter-btn { padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: var(--radius-sm); color: var(--text-primary); cursor: pointer; transition: var(--transition-fast); }
.filter-btn:hover, .filter-btn.active { background: var(--primary); border-color: var(--primary); }

.frame-section { display: flex; flex-direction: column; }
.frame-section h3 { margin-bottom: var(--spacing-xs); font-size: 1.3rem; text-align: center; }
.frame-subtitle { text-align: center; color: var(--text-muted); margin-bottom: var(--spacing-md); font-size: 0.9rem; }
.frame-editor { display: flex; flex-direction: column; align-items: center; flex: 1; }
.frame-container { position: relative; width: 100%; max-width: 280px; margin: 0 auto; }
.frame-image { width: 100%; height: auto; border-radius: var(--radius-md); box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; z-index: 2; pointer-events: none; }
.photo-slot { position: absolute; background: rgba(255,255,255,0.1); border: 2px dashed rgba(255,255,255,0.4); border-radius: var(--radius-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; overflow: hidden; z-index: 1; }
.photo-slot:hover { background: rgba(255,255,255,0.2); border-color: var(--primary); transform: scale(1.02); }
.photo-slot.active { border-color: var(--primary); box-shadow: 0 0 15px rgba(255,100,150,0.5); }
.photo-slot.filled { border-style: solid; border-color: var(--primary); }
.photo-slot.filled .slot-label, .photo-slot.filled .slot-text { display: none; }
.photo-slot img { width: 100%; height: 100%; object-fit: cover; }
.slot-label { font-size: 1.5rem; color: var(--text-muted); }
.slot-text { font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; }

/* Slot Positions - Adjusted */
#slot-0 { top: 19.2%; left: 9.8%; width: 80.5%; height: 20.8%; }
#slot-1 { top: 42.1%; left: 9.8%; width: 80.5%; height: 20.9%; }
#slot-2 { top: 65.4%; left: 9.8%; width: 80.5%; height: 20.8%; }

#slot-2 { top: 65.4%; left: 9.8%; width: 80.5%; height: 20.8%; }

.frame-selector-container { width: 100%; margin: var(--spacing-md) 0; }
.frame-selector-container h4 { text-align: center; margin-bottom: var(--spacing-sm); font-size: 0.9rem; color: var(--text-muted); }
.frame-selector { display: flex; gap: var(--spacing-sm); overflow-x: auto; padding-bottom: 5px; scrollbar-width: thin; }
.frame-option { flex: 0 0 60px; height: 120px; border: 2px solid transparent; border-radius: var(--radius-sm); cursor: pointer; overflow: hidden; transition: all 0.2s; opacity: 0.7; }
.frame-option.active { border-color: var(--primary); opacity: 1; transform: scale(1.05); }
.frame-option:hover { opacity: 1; }
.frame-option img { width: 100%; height: 100%; object-fit: cover; }

.frame-actions { display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-md); width: 100%; }
.frame-actions button { flex: 1; padding: 0.6rem 1rem; font-size: 0.9rem; }

.gallery-section h3 { margin-bottom: var(--spacing-xs); font-size: 1.5rem; }
.gallery-hint { color: var(--text-muted); font-size: 0.85rem; margin-bottom: var(--spacing-md); }
.photo-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: var(--spacing-sm); max-height: 500px; overflow-y: auto; }
.empty-gallery { grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: var(--spacing-xl); }
.photo-item { position: relative; aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; border: 2px solid rgba(255,255,255,0.1); transition: var(--transition-fast); cursor: pointer; }
.photo-item:hover { border-color: var(--primary); transform: scale(1.05); }
.photo-item img { width: 100%; height: 100%; object-fit: cover; }
.photo-actions { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); padding: 0.5rem; display: flex; gap: 0.5rem; opacity: 0; transition: var(--transition-fast); }
.photo-item:hover .photo-actions { opacity: 1; }
.photo-actions button { flex: 1; padding: 0.25rem 0.5rem; font-size: 0.75rem; }

.photo-select-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
.photo-select-overlay.show { display: flex; }
.photo-select-modal { background: var(--bg-secondary); padding: var(--spacing-lg); border-radius: var(--radius-lg); max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
.photo-select-modal h3 { margin-bottom: var(--spacing-md); }
.photo-select-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-sm); }
.photo-select-item { aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease; }
.photo-select-item:hover { border-color: var(--primary); transform: scale(1.05); }
.photo-select-item img { width: 100%; height: 100%; object-fit: cover; }
.modal-actions { margin-top: var(--spacing-md); text-align: right; }

@media (max-width: 1024px) { .photobooth-container { grid-template-columns: 1fr 1fr; } .frame-section { order: 3; grid-column: span 2; } .frame-container { max-width: 250px; } }
@media (max-width: 768px) { .photobooth-container { grid-template-columns: 1fr; } .frame-section { grid-column: span 1; } }
</style>

<div class="photo-select-overlay" id="photo-select-overlay">
    <div class="photo-select-modal">
        <h3>Select a Photo for Slot <span id="selected-slot-num">1</span></h3>
        <div class="photo-select-grid" id="photo-select-grid"></div>
        <div class="modal-actions">
            <button class="btn-logout" onclick="closePhotoSelectModal()">Cancel</button>
        </div>
    </div>
</div>

<script src="assets/js/photobooth.js"></script>

<?php require_once 'includes/footer.php'; ?>
