<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_auth.php';

if (isset($_GET['code'])) {
    try {
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);
            $google_service = new Google_Service_Oauth2($google_client);
            $data = $google_service->userinfo->get();
            
            // Check local DB
            $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
            $stmt->execute([$data['id'], $data['email']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // User exists
                if (!$user['google_id']) {
                    // Link Google ID to existing email
                    $update = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                    $update->execute([$data['id'], $user['id']]);
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
            } else {
                // New User
                // Create unique username from name
                $base_username = strtolower(str_replace(' ', '', $data['given_name']));
                $username = $base_username;
                $counter = 1;
                
                // Ensure unique username
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if (!$stmt->fetch()) break;
                    $username = $base_username . $counter++;
                }
                
                $password = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT); // Random dummy password
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, full_name, google_id, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $username,
                    $data['email'],
                    $data['name'],
                    $data['id'],
                    $password
                ]);
                
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $data['name'];
            }
            
            header('Location: dashboard.php');
            exit;
        }
    } catch (Exception $e) {
        // Handle error silently or redirect with error
        $error = "Google Login Failed";
        header('Location: login.php?error=' . urlencode($error));
        exit;
    }
}
header('Location: login.php');
exit;
