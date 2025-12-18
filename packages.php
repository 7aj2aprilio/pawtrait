<?php require_once 'includes/header.php'; ?>

<main>
    <section class="packages-section">
        <div class="container">
            <h1 class="section-title">Choose Your Package</h1>
            <p class="section-subtitle">Select the perfect package for your photobooth experience</p>
            
            <?php
            $stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price ASC");
            $packages = $stmt->fetchAll();
            ?>
            
            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card">
                        <div class="package-header">
                            <h3><?= htmlspecialchars($package['name']) ?></h3>
                            <div class="package-price">
                                <span class="currency">Rp</span>
                                <span class="amount"><?= number_format($package['price'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="package-body">
                            <p class="package-description"><?= htmlspecialchars($package['description']) ?></p>
                            <div class="package-info">
                                <p><strong>📸 <?= $package['photo_count'] ?> Photos</strong></p>
                            </div>
                            <ul class="package-features">
                                <?php
                                $features = json_decode(str_replace('JSON:', '', $package['features']), true);
                                if ($features) {
                                    foreach ($features as $feature) {
                                        echo '<li>✓ ' . htmlspecialchars($feature) . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="package-footer">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                    <button type="submit" name="add_to_cart" class="btn-primary btn-block">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn-secondary btn-block">Login to Purchase</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<style>
.package-info {
    margin: var(--spacing-md) 0;
    padding: var(--spacing-sm);
    background: rgba(99, 102, 241, 0.1);
    border-radius: var(--radius-sm);
    text-align: center;
}

.package-info p {
    margin: 0;
    color: var(--primary-light);
}
</style>

<?php require_once 'includes/footer.php'; ?>
