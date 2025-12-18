<?php
session_start();
require_once 'config/database.php';

require_once 'includes/header.php';
?>

<main>
    <section class="payment-result-section">
        <div class="container">
            <div class="result-card failed">
                <div class="result-icon">❌</div>
                <h1>Payment Failed</h1>
                <p>Unfortunately, your payment could not be processed. Please try again.</p>
                
                <div class="result-actions">
                    <a href="checkout.php" class="btn-primary">Try Again</a>
                    <a href="index.php" class="btn-secondary">Back to Home</a>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.payment-result-section {
    padding: var(--spacing-xl) 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.result-card {
    max-width: 600px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: var(--spacing-xl);
    text-align: center;
}

.result-card.failed {
    border-color: var(--error);
}

.result-icon {
    font-size: 5rem;
    margin-bottom: var(--spacing-md);
}

.result-card h1 {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-md);
}

.result-card p {
    font-size: 1.2rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

.result-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
}
</style>

<?php require_once 'includes/footer.php'; ?>
