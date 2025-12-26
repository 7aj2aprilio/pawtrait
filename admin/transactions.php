<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get all transactions
$stmt = $pdo->query("
    SELECT t.*, u.username, u.email, p.name as package_name 
    FROM transactions t 
    LEFT JOIN users u ON t.user_id = u.id 
    LEFT JOIN packages p ON t.package_id = p.id 
    ORDER BY t.created_at DESC
");
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="transactions.php" class="active">Transactions</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main>
        <section class="admin-section">
            <div class="container">
                <h1 class="section-title">All Transactions</h1>
                
                <div class="admin-card">
                    <div class="transactions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Payment Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($transaction['order_id']) ?></td>
                                        <td><?= htmlspecialchars($transaction['username']) ?></td>
                                        <td><?= htmlspecialchars($transaction['email']) ?></td>
                                        <td><?= htmlspecialchars($transaction['package_name']) ?></td>
                                        <td>Rp <?= number_format($transaction['gross_amount'], 0, ',', '.') ?></td>
                                        <td><?= $transaction['payment_type'] ? ucfirst(str_replace('_', ' ', $transaction['payment_type'])) : '-' ?></td>
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
    .admin-section {
        padding: 2rem 0;
        min-height: 80vh;
    }
    
    .section-title {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .admin-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 2rem;
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
        transition: background 0.2s ease;
    }
    
    .transactions-table tbody tr:last-child td {
        border-bottom: none;
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
