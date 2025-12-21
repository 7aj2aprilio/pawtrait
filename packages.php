<?php 
require_once 'config/subscription.php';
require_once 'includes/header.php'; 

// Get current user subscription if logged in
$currentSubscription = null;
if (isset($_SESSION['user_id'])) {
    $currentSubscription = getSubscriptionStatus($pdo, $_SESSION['user_id']);
}
?>

<main>
    <section class="packages-section">
        <div class="container">
            <h1 class="section-title">Pilih Paket Kamu</h1>
            <p class="section-subtitle">Pilih paket yang sesuai dengan kebutuhan photobooth kamu</p>
            
            <?php if ($currentSubscription): ?>
            <div class="current-subscription-banner">
                <span class="banner-icon">✨</span>
                <span>Paket aktif: <strong><?= htmlspecialchars($currentSubscription['package_name']) ?></strong>
                <?php if ($currentSubscription['is_trial']): ?>
                    (<?= $currentSubscription['photo_remaining'] ?> foto tersisa)
                <?php elseif (isset($currentSubscription['expires_in'])): ?>
                    (Berlaku <?= $currentSubscription['expires_in'] ?>)
                <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php
            $stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price ASC");
            $packages = $stmt->fetchAll();
            ?>
            
            <div class="packages-grid">
                <?php foreach ($packages as $index => $package): ?>
                    <?php 
                    $isTrial = $package['is_trial'] == 1;
                    $isPopular = $package['name'] === 'Standard';
                    $isPremium = $package['name'] === 'Premium';
                    ?>
                    <div class="package-card <?= $isTrial ? 'trial' : '' ?> <?= $isPopular ? 'popular' : '' ?> <?= $isPremium ? 'premium' : '' ?>">
                        <?php if ($isPopular): ?>
                            <div class="package-badge popular-badge">🔥 Paling Populer</div>
                        <?php elseif ($isPremium): ?>
                            <div class="package-badge premium-badge">👑 Best Value</div>
                        <?php elseif ($isTrial): ?>
                            <div class="package-badge trial-badge">🎁 Gratis</div>
                        <?php endif; ?>
                        
                        <div class="package-header">
                            <h3><?= htmlspecialchars($package['name']) ?></h3>
                            <div class="package-price">
                                <?php if ($package['price'] == 0): ?>
                                    <span class="amount free">GRATIS</span>
                                <?php else: ?>
                                    <span class="currency">Rp</span>
                                    <span class="amount"><?= number_format($package['price'], 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="package-body">
                            <p class="package-description"><?= htmlspecialchars($package['description']) ?></p>
                            
                            <div class="package-stats">
                                <div class="stat-item">
                                    <span class="stat-icon">⏱️</span>
                                    <span class="stat-value">
                                        <?php if ($package['duration_days']): ?>
                                            <?= $package['duration_days'] ?> hari
                                        <?php else: ?>
                                            Selamanya
                                        <?php endif; ?>
                                    </span>
                                    <span class="stat-label">Durasi</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-icon">🖼️</span>
                                    <span class="stat-value"><?= $package['max_frames'] ?? '∞' ?></span>
                                    <span class="stat-label">Frame</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-icon">📸</span>
                                    <span class="stat-value">
                                        <?php if ($package['max_photos']): ?>
                                            <?= $package['max_photos'] ?>
                                        <?php else: ?>
                                            ∞
                                        <?php endif; ?>
                                    </span>
                                    <span class="stat-label">Foto</span>
                                </div>
                            </div>
                            
                            <ul class="package-features">
                                <?php
                                $features = json_decode(str_replace('JSON:', '', $package['features']), true);
                                if ($features) {
                                    foreach ($features as $feature) {
                                        echo '<li>✓ ' . htmlspecialchars($feature) . '</li>';
                                    }
                                }
                                if ($package['can_upload_frames']) {
                                    echo '<li class="premium-feature">✓ Upload frame custom</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        
                        <div class="package-footer">
                            <?php if ($isTrial): ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="photobooth.php" class="btn-secondary btn-block">Mulai Coba Gratis</a>
                                <?php else: ?>
                                    <a href="register.php" class="btn-secondary btn-block">Daftar Gratis</a>
                                <?php endif; ?>
                            <?php elseif (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                    <button type="submit" name="add_to_cart" class="btn-primary btn-block">
                                        <?= $isPremium ? '👑 ' : '' ?>Beli Sekarang
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn-secondary btn-block">Login untuk Beli</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<style>
.current-subscription-banner {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    padding: var(--spacing-md) var(--spacing-lg);
    border-radius: var(--radius-md);
    text-align: center;
    margin-bottom: var(--spacing-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}
.banner-icon { font-size: 1.5rem; }

.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-xl);
}

.package-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}
.package-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.2);
}
.package-card.popular {
    border-color: var(--primary);
    box-shadow: 0 0 30px rgba(99, 102, 241, 0.3);
}
.package-card.premium {
    background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,165,0,0.05));
    border-color: #ffd700;
}
.package-card.trial {
    border-style: dashed;
}

.package-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.3rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}
.popular-badge { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
.premium-badge { background: linear-gradient(135deg, #ffd700, #ffa500); color: #000; }
.trial-badge { background: rgba(255,255,255,0.2); }

.package-header { text-align: center; margin-bottom: var(--spacing-md); }
.package-header h3 { font-size: 1.5rem; margin-bottom: var(--spacing-sm); }
.package-price { display: flex; align-items: baseline; justify-content: center; gap: 0.2rem; }
.package-price .currency { font-size: 1rem; color: var(--text-muted); }
.package-price .amount { font-size: 2.5rem; font-weight: 700; }
.package-price .amount.free { color: #4ade80; font-size: 1.8rem; }

.package-body { flex: 1; }
.package-description { 
    text-align: center; 
    color: var(--text-muted); 
    margin-bottom: var(--spacing-md);
    font-size: 0.9rem;
}

.package-stats {
    display: flex;
    justify-content: space-around;
    background: rgba(255,255,255,0.05);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}
.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
}
.stat-icon { font-size: 1.2rem; }
.stat-value { font-size: 1.3rem; font-weight: 700; color: var(--primary-light); }
.stat-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; }

.package-features {
    list-style: none;
    padding: 0;
    margin: 0;
}
.package-features li {
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.package-features li:last-child { border-bottom: none; }
.package-features .premium-feature { color: #ffd700; }

.package-footer { margin-top: var(--spacing-md); }
</style>

<?php require_once 'includes/footer.php'; ?>

