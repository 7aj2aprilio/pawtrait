<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions");
$total_transactions = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE transaction_status = 'success'");
$successful_transactions = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(gross_amount) as total FROM transactions WHERE transaction_status = 'success'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM photos");
$total_photos = $stmt->fetch()['total'];

// Recent transactions
$stmt = $pdo->query("
    SELECT t.*, u.username, p.name as package_name 
    FROM transactions t 
    LEFT JOIN users u ON t.user_id = u.id 
    LEFT JOIN packages p ON t.package_id = p.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Photobooth</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">
                    <span class="logo-icon">⚙️</span>
                    <span class="logo-text">Admin Panel</span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main>
        <section class="dashboard-section">
            <div class="container">
                <h1 class="section-title">Admin Dashboard</h1>
                <p class="section-subtitle">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</p>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3><?= $total_users ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🛒</div>
                        <div class="stat-info">
                            <h3><?= $total_transactions ?></h3>
                            <p>Total Transactions</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?= $successful_transactions ?></h3>
                            <p>Successful Payments</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3>Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📸</div>
                        <div class="stat-info">
                            <h3><?= $total_photos ?></h3>
                            <p>Total Photos</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="dashboard-section-block">
                    <h2>Recent Transactions</h2>
                    <div class="transactions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['order_id']) ?></td>
                                        <td><?= htmlspecialchars($transaction['username']) ?></td>
                                        <td><?= htmlspecialchars($transaction['package_name']) ?></td>
                                        <td>Rp <?= number_format($transaction['gross_amount'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="status-badge <?= $transaction['transaction_status'] ?>">
                                                <?= ucfirst($transaction['transaction_status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y H:i', strtotime($transaction['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
