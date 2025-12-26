<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_auth.php';

$google_login_url = $google_client->createAuthUrl();

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
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>OR</p>
                <a href="<?= filter_var($google_login_url, FILTER_SANITIZE_URL) ?>" class="btn-google">
                    <svg class="google-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        <path fill="none" d="M0 0h48v48H0z"/>
                    </svg>
                    <span>Login with Google</span>
                </a>
                
                <style>
                    .btn-google {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: white;
                        color: #757575;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        padding: 10px 16px;
                        text-decoration: none;
                        font-family: 'Roboto', sans-serif;
                        font-weight: 500;
                        transition: background-color 0.3s, box-shadow 0.3s;
                        width: 100%;
                        margin-top: 10px;
                    }
                    .btn-google:hover {
                        background-color: #f7f8f9;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                        color: #333;
                    }
                    .google-logo {
                        width: 18px;
                        height: 18px;
                        margin-right: 12px;
                    }
                </style>
            </div>
            
            <!-- Admin Login Button -->
            <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="/admin/login.php" class="btn-admin-login">
                    <span style="margin-right: 8px;">⚙️</span>
                    <span>Admin Login</span>
                </a>
                
                <style>
                    .btn-admin-login {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        border-radius: 8px;
                        padding: 10px 20px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                        font-size: 0.95rem;
                    }
                    .btn-admin-login:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
                    }
                </style>
            </div>
            
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
