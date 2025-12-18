<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $package_id = (int)$_POST['package_id'];
    
    // Check if package exists
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    
    if ($package) {
        $_SESSION['cart'][$package_id] = [
            'id' => $package['id'],
            'name' => $package['name'],
            'price' => $package['price'],
            'photo_count' => $package['photo_count'],
            'quantity' => 1
        ];
        header('Location: cart.php');
        exit;
    }
}

// Remove from cart
if (isset($_GET['remove'])) {
    $package_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$package_id]);
    header('Location: cart.php');
    exit;
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once 'includes/header.php';
?>

<main>
    <section class="cart-section">
        <div class="container">
            <h1 class="section-title">Shopping Cart</h1>
            
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="empty-cart">
                    <p>Your cart is empty</p>
                    <a href="packages.php" class="btn-primary">Browse Packages</a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-info">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <p><?= $item['photo_count'] ?> Photos</p>
                                </div>
                                <div class="cart-item-price">
                                    <p class="price">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                </div>
                                <div class="cart-item-actions">
                                    <a href="cart.php?remove=<?= $item['id'] ?>" class="btn-logout">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <a href="checkout.php" class="btn-primary btn-block">Proceed to Checkout</a>
                        <a href="packages.php" class="btn-secondary btn-block">Continue Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
.cart-section {
    padding: var(--spacing-xl) 0;
    min-height: 70vh;
}

.empty-cart {
    text-align: center;
    padding: var(--spacing-xl);
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.empty-cart p {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-lg);
    color: var(--text-secondary);
}

.cart-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.cart-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-md);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.cart-item-info {
    flex: 1;
}

.cart-item-info h3 {
    margin-bottom: 0.5rem;
}

.cart-item-info p {
    color: var(--text-secondary);
}

.cart-item-price .price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-light);
}

.cart-summary {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: var(--spacing-lg);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.cart-summary h3 {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    color: var(--text-secondary);
}

.summary-row.total {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    padding-top: var(--spacing-md);
    margin-top: var(--spacing-md);
    border-top: 2px solid rgba(255, 255, 255, 0.2);
}

.cart-summary .btn-block {
    margin-top: var(--spacing-md);
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
