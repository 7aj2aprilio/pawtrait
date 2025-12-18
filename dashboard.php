<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user transactions
$stmt = $pdo->prepare("
    SELECT t.*, p.name as package_name 
    FROM transactions t 
    LEFT JOIN packages p ON t.package_id = p.id 
    WHERE t.user_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();

// Get user photos
$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC LIMIT 12");
$stmt->execute([$user_id]);
$photos = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main>
    <section class="dashboard-section">
        <div class="container">
            <h1 class="section-title">My Dashboard</h1>
            <p class="section-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📸</div>
                    <div class="stat-info">
                        <h3><?= count($photos) ?></h3>
                        <p>Total Photos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?= count($transactions) ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <?php
                        $successful = array_filter($transactions, fn($t) => $t['transaction_status'] == 'success');
                        ?>
                        <h3><?= count($successful) ?></h3>
                        <p>Successful Payments</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="dashboard-section-block">
                <h2>Recent Transactions</h2>
                <?php if (empty($transactions)): ?>
                    <p class="empty-message">No transactions yet. <a href="packages.php">Browse packages</a> to get started!</p>
                <?php else: ?>
                    <div class="transactions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['order_id']) ?></td>
                                        <td><?= htmlspecialchars($transaction['package_name']) ?></td>
                                        <td>Rp <?= number_format($transaction['gross_amount'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="status-badge <?= $transaction['transaction_status'] ?>">
                                                <?= ucfirst($transaction['transaction_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($transaction['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Photos -->
            <div class="dashboard-section-block">
                <h2>Recent Photos</h2>
                <?php if (empty($photos)): ?>
                    <p class="empty-message">No photos yet. <a href="photobooth.php">Start capturing</a> amazing moments!</p>
                <?php else: ?>
                    <div class="photos-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-card">
                                <img src="<?= htmlspecialchars($photo['file_path']) ?>" alt="Photo">
                                <div class="photo-info">
                                    <p><?= date('d M Y', strtotime($photo['created_at'])) ?></p>
                                    <?php if ($photo['filter_applied'] != 'none'): ?>
                                        <span class="filter-tag"><?= ucfirst($photo['filter_applied']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center" style="margin-top: 2rem;">
                        <a href="photobooth.php" class="btn-primary">Capture More Photos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
.dashboard-section {
    padding: var(--spacing-xl) 0;
    min-height: 80vh;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin: var(--spacing-xl) 0;
}

.stat-card {
    background: var(--gradient-primary);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    font-size: 3rem;
}

.stat-info h3 {
    font-size: 2.5rem;
    margin-bottom: 0.25rem;
}

.stat-info p {
    color: rgba(255, 255, 255, 0.9);
}

.dashboard-section-block {
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.dashboard-section-block h2 {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.empty-message {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-secondary);
}

.empty-message a {
    color: var(--primary-light);
    text-decoration: underline;
}

.transactions-table {
    overflow-x: auto;
}

.transactions-table table {
    width: 100%;
    border-collapse: collapse;
}

.transactions-table th,
.transactions-table td {
    padding: var(--spacing-sm);
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.transactions-table th {
    color: var(--text-secondary);
    font-weight: 600;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.success {
    background: rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.status-badge.failed {
    background: rgba(239, 68, 68, 0.2);
    color: var(--error);
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.photo-card {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition-fast);
}

.photo-card:hover {
    transform: scale(1.05);
    border-color: var(--primary);
}

.photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    padding: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.photo-info p {
    font-size: 0.85rem;
    margin: 0;
}

.filter-tag {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: var(--primary);
    border-radius: var(--radius-sm);
}
</style>

<?php require_once 'includes/footer.php'; ?>
