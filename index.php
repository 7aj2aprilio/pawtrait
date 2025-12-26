<?php require_once 'includes/header.php'; ?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background"></div>
        <div class="container hero-content">
            <h1 class="hero-title">Capture Your <span class="gradient-text">Perfect Moments</span></h1>
            <p class="hero-subtitle">Professional photobooth service with instant results and secure payment</p>
            <div class="hero-buttons">
                <a href="photobooth.php" class="btn-primary btn-large">Start Photo Session</a>
                <a href="packages.php" class="btn-secondary btn-large">View Packages</a>
            </div>
        </div>
        <div class="hero-decoration">
            <div class="floating-card card-1">📸</div>
            <div class="floating-card card-2">✨</div>
            <div class="floating-card card-3">🎨</div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📷</div>
                    <h3>High Quality Photos</h3>
                    <p>Professional camera equipment for crystal clear images</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Instant Results</h3>
                    <p>Get your photos immediately after the session</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎨</div>
                    <h3>Creative Filters</h3>
                    <p>Multiple filters and effects to enhance your photos</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Secure Payment</h3>
                    <p>Safe and easy payment through Midtrans gateway</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Preview -->
    <section class="packages-preview">
        <div class="container">
            <h2 class="section-title">Our Packages</h2>
            <p class="section-subtitle">Choose the perfect package for your needs</p>
            
            <?php
            $stmt = $pdo->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price ASC LIMIT 3");
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
                            <a href="packages.php" class="btn-primary btn-block">Choose Package</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center" style="margin-top: 2rem;">
                <a href="packages.php" class="btn-secondary">View All Packages</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Capture Amazing Photos?</h2>
                <p>Start your photo session now and create unforgettable memories</p>
                <a href="photobooth.php" class="btn-primary btn-large">Get Started</a>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/visitor-tracker.php'; ?>
<?php require_once 'includes/footer.php'; ?>
