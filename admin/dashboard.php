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
                <li><a href="users.php">Users</a></li>
                <li><a href="analytics.php">Analytics</a></li>
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
    
    <style>
    .dashboard-section {
        padding: 2rem 0;
        min-height: 80vh;
    }
    
    .section-title {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .section-subtitle {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        font-size: 3rem;
    }
    
    .stat-info h3 {
        font-size: 2.5rem;
        margin: 0 0 0.25rem 0;
        color: white;
    }
    
    .stat-info p {
        margin: 0;
        color: rgba(255, 255, 255, 0.9);
        font-size: 1rem;
    }
    
    .dashboard-section-block {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .dashboard-section-block h2 {
        margin: 0 0 1.5rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        font-size: 1.5rem;
    }
    
    .transactions-table {
        overflow-x: auto;
        background: white;
        border-radius: 8px;
        padding: 1rem;
    }
    
    .transactions-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .transactions-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e9ecef;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .transactions-table td {
        padding: 1rem;
        color: #333;
        border-bottom: 1px solid #e9ecef;
    }
    
    .transactions-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-badge.success {
        background: #d4edda;
        color: #155724;
    }
    
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-badge.failed {
        background: #f8d7da;
        color: #721c24;
    }
    </style>
</body>
</html>
