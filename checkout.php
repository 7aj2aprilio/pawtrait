<?php
session_start();
require_once 'config/database.php';
require_once 'config/midtrans.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Calculate total
$total = 0;
$items = [];
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
    $items[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'price' => $item['price'],
        'quantity' => $item['quantity']
    ];
}

require_once 'includes/header.php';
?>

<main>
    <section class="checkout-section">
        <div class="container">
            <h1 class="section-title">Checkout</h1>
            
            <div class="checkout-container">
                <div class="checkout-details">
                    <h3>Order Details</h3>
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <span>Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        <span>Total</span>
                        <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </div>
                
                <div class="payment-section">
                    <h3>Payment Information</h3>
                    <div class="customer-info">
                        <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                    </div>
                    
                    <button id="pay-button" class="btn-primary btn-block btn-large">
                        Pay with Midtrans
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.checkout-section {
    padding: var(--spacing-xl) 0;
    min-height: 70vh;
}

.checkout-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.checkout-details,
.payment-section {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: var(--spacing-lg);
}

.checkout-details h3,
.payment-section h3 {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    color: var(--text-secondary);
}

.order-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.5rem;
    font-weight: 700;
    padding-top: var(--spacing-md);
    margin-top: var(--spacing-md);
    border-top: 2px solid rgba(255, 255, 255, 0.2);
}

.customer-info {
    margin-bottom: var(--spacing-lg);
}

.customer-info p {
    padding: var(--spacing-xs) 0;
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<script src="assets/js/payment.js"></script>

<script>
// Pass data to payment.js
window.checkoutData = {
    total: <?= $total ?>,
    items: <?= json_encode($items) ?>,
    customer: {
        name: "<?= htmlspecialchars($user['full_name']) ?>",
        email: "<?= htmlspecialchars($user['email']) ?>",
        phone: "<?= htmlspecialchars($user['phone'] ?? '') ?>"
    }
};
</script>

<?php require_once 'includes/footer.php'; ?>
