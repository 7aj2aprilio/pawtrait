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
                    $isPremium = $package['is_premium'] ?? false;

                    ?>
            <div class="package-card <?= $isTrial ? 'trial' : '' ?> <?= $isPopular ? 'popular' : '' ?> <?= $isPremium ? 'premium' : '' ?>">
                    <?php if ($isPopular): ?>
                <div class="package-badge popular-badge">🔥 Paling Populer</div>
                    <?php elseif ($isPremium): ?>
                <div class="package-badge premium-badge">👑 Best Value</div>
                    <?php elseif ($isTrial): ?>
                <div class="package-badge trial-badge">🎁 Gratis</div>
                    <?php endif; ?>

    

    <!-- HEADER -->
    <div class="package-header">
        <h3><?= htmlspecialchars($package['name']) ?></h3>

        <div class="package-price">
            <?php if ($package['price'] == 0): ?>
    <span class="currency">Rp</span>
    <span class="amount">0</span>
<?php else: ?>

                <span class="currency">Rp</span>
                <span class="amount"><?= number_format($package['price'], 0, ',', '.') ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- BODY -->
    <div class="package-body">
        <p class="package-description">
            <?= htmlspecialchars($package['description']) ?>
        </p>

        <!-- STATS (tetap ada, tapi rapi & konsisten) -->
        <div class="package-stats">
            <div class="stat-item">
                <span class="stat-value">
                    <?= $package['duration_days'] ? $package['duration_days'] . ' hari' : 'Selamanya' ?>
                </span>
                <span class="stat-label">Durasi</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $package['max_frames'] ?? '∞' ?></span>
                <span class="stat-label">Frame</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $package['max_photos'] ?? '∞' ?></span>
                <span class="stat-label">Foto</span>
            </div>
        </div>

        <!-- FEATURES -->
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

    <!-- FOOTER -->
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
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.package-card {
    background: #fff;
    border-radius: 16px;
    border: 2px solid transparent; 
    overflow: visible;               
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}

.package-header {
    background: #3498db;
    color: #1f1e1e;
    padding: 2.2rem 1.5rem;
    text-align: center;
    min-height: 140px;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;

}ackage-header h3 {
    margin-bottom: .5rem;
    font-size: 1.4rem;
}
.package-price .amount {
    font-size: 2rem;
    font-weight: 700;
}
.package-body {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.package-description {
    font-size: .9rem;
    color: #666;
    margin-bottom: 1.5rem;
}
.package-features {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1; 
}

.package-features li {
    padding: .6rem 0;
    border-bottom: 1px solid #eee;
    font-size: .9rem;
}

.package-card:hover {
    border-color: #3498db;           
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.25);
    transform: translateY(-5px);
}


.package-card.premium {
    background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,165,0,0.05));
    border-color: #ffd700;
}

/* BADGE UTAMA */
.package-badge {
    position: absolute;
    top: 15px;             
    left: 50%;
    transform: translateX(-50%);
    background: #3498db;
    color: #fff;
    padding: 4px 14px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 999px;
    z-index: 10;
    white-space: nowrap;
    box-shadow: 0 6px 16px rgba(0,0,0,.15);
}
.popular-badge { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
.premium-badge { background: linear-gradient(135deg, #ffd700, #ffa500); color: #000; }
.trial-badge { background: rgba(11, 10, 10, 0.2); }

.package-header { text-align: center; margin-bottom: var(--spacing-md); }
.package-header h3 { font-size: 1.5rem; margin-bottom: var(--spacing-sm); }
.package-price { display: flex; align-items: baseline; justify-content: center; gap: 0.2rem; }
.package-price .amount { font-size: 2.5rem; font-weight: 700; }
.package-price .amount.free { color: #4ade80; font-size: 1.8rem; }
.package-price .currency {color: #000; font-weight: 500;}

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

