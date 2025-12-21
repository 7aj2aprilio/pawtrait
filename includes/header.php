<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Photobooth profesional dengan berbagai paket menarik dan pembayaran mudah">
    <title>Photobooth - Capture Your Moments</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">
                    <img src="assets/images/logo.jpg" alt="Pawtrait Logo" class="logo-image">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="packages.php" class="<?= $current_page == 'packages.php' ? 'active' : '' ?>">Packages</a></li>
                <li><a href="photobooth.php" class="<?= $current_page == 'photobooth.php' ? 'active' : '' ?>">Photo Booth</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="cart.php" class="<?= $current_page == 'cart.php' ? 'active' : '' ?>">Cart</a></li>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a></li>
                    <li><a href="register.php" class="btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
