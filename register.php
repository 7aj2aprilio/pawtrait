<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_auth.php';

$google_login_url = $google_client->createAuthUrl();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, full_name, phone, password) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $full_name, $phone, $hashed_password])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main>
    <section class="auth-section">
        <div class="form-container">
            <h2 class="text-center" style="margin-bottom: 2rem;">Create Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required
                           value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary btn-block">Register</button>
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
                    <span>Register with Google</span>
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

            <p class="text-center" style="margin-top: 1.5rem; color: var(--text-secondary);">
                Already have an account? <a href="login.php" style="color: var(--primary-light);">Login here</a>
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
