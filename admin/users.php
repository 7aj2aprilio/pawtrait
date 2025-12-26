<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get users with their subscription status
$stmt = $pdo->query("
    SELECT 
        u.id, 
        u.username, 
        u.email, 
        u.full_name, 
        u.phone,
        u.created_at,
        p.name as package_name,
        us.is_active,
        us.expires_at,
        us.started_at,
        CASE 
            WHEN us.is_active = 1 THEN 'Subscribed'
            ELSE 'Not Subscribed'
        END as subscription_status
    FROM users u
    LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.is_active = 1
    LEFT JOIN packages p ON us.package_id = p.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM user_subscriptions WHERE is_active = 1");
$subscribed_users = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-mini {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
        }
        .stat-mini h3 {
            font-size: 2rem;
            margin: 0 0 0.5rem 0;
        }
        .stat-mini p {
            margin: 0;
            opacity: 0.9;
        }
        .users-table-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }
        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .users-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-subscribed {
            background: #d4edda;
            color: #155724;
        }
        .badge-not-subscribed {
            background: #f8d7da;
            color: #721c24;
        }
        .package-badge {
            background: #e7f3ff;
            color: #0066cc;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
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
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="analytics.php">Analytics</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main>
        <section class="dashboard-section">
            <div class="container">
                <h1 class="section-title">Users Management</h1>
                <p class="section-subtitle">Manage registered users and their subscription status</p>
                
                <!-- Mini Stats -->
                <div class="users-stats">
                    <div class="stat-mini">
                        <h3><?= $total_users ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-mini" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h3><?= $subscribed_users ?></h3>
                        <p>Subscribed Users</p>
                    </div>
                    <div class="stat-mini" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h3><?= $total_users - $subscribed_users ?></h3>
                        <p>Not Subscribed</p>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="users-table-container">
                    <h2 style="margin-top: 0;">Registered Users</h2>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Package</th>
                                <th>Status</th>
                                <th>Expires At</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td>
                                        <?php if ($user['package_name']): ?>
                                            <span class="package-badge"><?= htmlspecialchars($user['package_name']) ?></span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $user['subscription_status'] == 'Subscribed' ? 'badge-subscribed' : 'badge-not-subscribed' ?>">
                                            <?= $user['subscription_status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['expires_at']): ?>
                                            <?= date('d M Y', strtotime($user['expires_at'])) ?>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
