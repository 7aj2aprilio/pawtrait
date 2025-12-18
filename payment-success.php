<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['order_id'];

// Get transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
$stmt->execute([$order_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: index.php');
    exit;
}

// Clear cart
$_SESSION['cart'] = [];

require_once 'includes/header.php';
?>

<main>
    <section class="payment-result-section">
        <div class="container">
            <div class="result-card success">
                <div class="result-icon">✅</div>
                <h1>Payment Successful!</h1>
                <p>Thank you for your purchase. Your payment has been processed successfully.</p>
                
                <div class="transaction-details">
                    <h3>Transaction Details</h3>
                    <div class="detail-row">
                        <span>Order ID:</span>
                        <span><?= htmlspecialchars($transaction['order_id']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Amount:</span>
                        <span>Rp <?= number_format($transaction['gross_amount'], 0, ',', '.') ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Status:</span>
                        <span class="status-badge success"><?= ucfirst($transaction['transaction_status']) ?></span>
                    </div>
                </div>
                
                <div class="result-actions">
                    <a href="dashboard.php" class="btn-primary">View Dashboard</a>
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

.result-card.success {
    border-color: var(--success);
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

.transaction-details {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    text-align: left;
}

.transaction-details h3 {
    margin-bottom: var(--spacing-md);
    text-align: center;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row:last-child {
    border-bottom: none;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    font-weight: 600;
}

.status-badge.success {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.result-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
}
</style>

<?php require_once 'includes/footer.php'; ?>
