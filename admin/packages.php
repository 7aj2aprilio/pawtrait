<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Handle package operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_package'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $photo_count = (int)$_POST['photo_count'];
        $features = $_POST['features'];
        
        $features_json = 'JSON:' . json_encode(array_filter(array_map('trim', explode("\n", $features))));
        
        $stmt = $pdo->prepare("INSERT INTO packages (name, description, price, photo_count, features) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $photo_count, $features_json])) {
            $success = 'Package added successfully';
        } else {
            $error = 'Failed to add package';
        }
    } elseif (isset($_POST['update_package'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $photo_count = (int)$_POST['photo_count'];
        $features = $_POST['features'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $features_json = 'JSON:' . json_encode(array_filter(array_map('trim', explode("\n", $features))));
        
        $stmt = $pdo->prepare("UPDATE packages SET name = ?, description = ?, price = ?, photo_count = ?, features = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $photo_count, $features_json, $is_active, $id])) {
            $success = 'Package updated successfully';
        } else {
            $error = 'Failed to update package';
        }
    } elseif (isset($_POST['delete_package'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Package deleted successfully';
        } else {
            $error = 'Failed to delete package';
        }
    }
}

// Get all packages
$stmt = $pdo->query("SELECT * FROM packages ORDER BY price ASC");
$packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin</title>
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
                <li><a href="packages.php" class="active">Packages</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <main>
        <section class="admin-section">
            <div class="container">
                <h1 class="section-title">Manage Packages</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Add Package Form -->
                <div class="admin-card">
                    <h3>Add New Package</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Package Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Price (Rp)</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Photo Count</label>
                                <input type="number" name="photo_count" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Features (one per line)</label>
                            <textarea name="features" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="add_package" class="btn-primary">Add Package</button>
                    </form>
                </div>
                
                <!-- Packages List -->
                <div class="admin-card">
                    <h3>Existing Packages</h3>
                    <div class="packages-list">
                        <?php foreach ($packages as $package): ?>
                            <div class="package-item">
                                <div class="package-item-header">
                                    <h4><?= htmlspecialchars($package['name']) ?></h4>
                                    <span class="status-badge <?= $package['is_active'] ? 'success' : 'failed' ?>">
                                        <?= $package['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <p><?= htmlspecialchars($package['description']) ?></p>
                                <p><strong>Price:</strong> Rp <?= number_format($package['price'], 0, ',', '.') ?> | 
                                   <strong>Photos:</strong> <?= $package['photo_count'] ?></p>
                                <div class="package-actions">
                                    <button onclick="editPackage(<?= $package['id'] ?>)" class="btn-secondary">Edit</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="id" value="<?= $package['id'] ?>">
                                        <button type="submit" name="delete_package" class="btn-logout">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <style>
    .admin-section {
        padding: var(--spacing-xl) 0;
        min-height: 80vh;
    }
    
    .admin-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: var(--radius-lg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    
    .admin-card h3 {
        margin-bottom: var(--spacing-md);
        padding-bottom: var(--spacing-md);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }
    
    .packages-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .package-item {
        background: rgba(255, 255, 255, 0.03);
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .package-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-sm);
    }
    
    .package-item h4 {
        margin: 0;
    }
    
    .package-item p {
        color: var(--text-secondary);
        margin: 0.5rem 0;
    }
    
    .package-actions {
        margin-top: var(--spacing-sm);
        display: flex;
        gap: var(--spacing-sm);
    }
    </style>
</body>
</html>
