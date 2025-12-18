<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

require_once 'includes/header.php';
?>

<main>
    <section class="auth-section">
        <div class="form-container">
            <h2 class="text-center" style="margin-bottom: 2rem;">Login to Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary btn-block">Login</button>
            </form>
            
            <p class="text-center" style="margin-top: 1.5rem; color: var(--text-secondary);">
                Don't have an account? <a href="register.php" style="color: var(--primary-light);">Register here</a>
            </p>
        </div>
    </section>
</main>

<style>
.auth-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: var(--spacing-xl) 0;
}
</style>

<?php require_once 'includes/footer.php'; ?>
