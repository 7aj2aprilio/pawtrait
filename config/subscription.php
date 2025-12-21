<?php
/**
 * Subscription Helper Functions
 * Handles subscription validation, frame access, and photo limits
 */

/**
 * Get user's active subscription
 * @param PDO $pdo
 * @param int $user_id
 * @return array|null
 */
function getUserSubscription($pdo, $user_id) {
    // First check for active paid subscription
    $stmt = $pdo->prepare("
        SELECT us.*, p.name as package_name, p.duration_days, p.max_frames, 
               p.max_photos, p.can_upload_frames, p.is_trial, p.price
        FROM user_subscriptions us
        JOIN packages p ON us.package_id = p.id
        WHERE us.user_id = ? 
          AND us.is_active = 1
          AND (us.expires_at IS NULL OR us.expires_at > NOW())
        ORDER BY p.price DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();
    
    if ($subscription) {
        return $subscription;
    }
    
    // If no active subscription, create/return Trial
    return getOrCreateTrialSubscription($pdo, $user_id);
}

/**
 * Get or create Trial subscription for user
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function getOrCreateTrialSubscription($pdo, $user_id) {
    // Get Trial package
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE is_trial = 1 AND is_active = 1 LIMIT 1");
    $stmt->execute();
    $trialPackage = $stmt->fetch();
    
    if (!$trialPackage) {
        return null;
    }
    
    // Check if user already has Trial subscription
    $stmt = $pdo->prepare("
        SELECT us.*, p.name as package_name, p.duration_days, p.max_frames, 
               p.max_photos, p.can_upload_frames, p.is_trial, p.price
        FROM user_subscriptions us
        JOIN packages p ON us.package_id = p.id
        WHERE us.user_id = ? AND p.is_trial = 1
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        return $existing;
    }
    
    // Create new Trial subscription
    $stmt = $pdo->prepare("
        INSERT INTO user_subscriptions (user_id, package_id, expires_at, is_active, photo_count)
        VALUES (?, ?, NULL, 1, 0)
    ");
    $stmt->execute([$user_id, $trialPackage['id']]);
    
    // Return the new subscription
    return getUserSubscription($pdo, $user_id);
}

/**
 * Check if user can take a photo
 * @param PDO $pdo
 * @param int $user_id
 * @return array ['can_take' => bool, 'message' => string, 'remaining' => int|null]
 */
function canTakePhoto($pdo, $user_id) {
    $subscription = getUserSubscription($pdo, $user_id);
    
    if (!$subscription) {
        return [
            'can_take' => false,
            'message' => 'No subscription found. Please purchase a package.',
            'remaining' => 0
        ];
    }
    
    // Check if subscription expired (for paid packages)
    if (!$subscription['is_trial'] && $subscription['expires_at']) {
        if (strtotime($subscription['expires_at']) < time()) {
            return [
                'can_take' => false,
                'message' => 'Your subscription has expired. Please renew your package.',
                'remaining' => 0
            ];
        }
    }
    
    // For Trial, check photo limit
    if ($subscription['is_trial'] && $subscription['max_photos']) {
        $remaining = $subscription['max_photos'] - $subscription['photo_count'];
        if ($remaining <= 0) {
            return [
                'can_take' => false,
                'message' => 'You have reached your Trial limit of ' . $subscription['max_photos'] . ' photos. Please purchase a package to continue.',
                'remaining' => 0,
                'show_packages' => true
            ];
        }
        return [
            'can_take' => true,
            'message' => 'OK',
            'remaining' => $remaining
        ];
    }
    
    // Paid packages have unlimited photos
    return [
        'can_take' => true,
        'message' => 'OK',
        'remaining' => null // unlimited
    ];
}

/**
 * Increment photo count for user's subscription
 * @param PDO $pdo
 * @param int $user_id
 * @return bool
 */
function incrementPhotoCount($pdo, $user_id) {
    $subscription = getUserSubscription($pdo, $user_id);
    
    if (!$subscription) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        UPDATE user_subscriptions 
        SET photo_count = photo_count + 1, updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$subscription['id']]);
}

/**
 * Get accessible frames for user based on subscription
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function getAccessibleFrames($pdo, $user_id) {
    $subscription = getUserSubscription($pdo, $user_id);
    
    // Default frames available in the system
    $defaultFrames = [
        ['id' => 1, 'filename' => 'default-frame.png', 'path' => 'assets/images/frames/default-frame.png'],
        ['id' => 2, 'filename' => 'frame-1.png', 'path' => 'assets/images/frames/frame-1.png'],
        ['id' => 3, 'filename' => 'frame-2.png', 'path' => 'assets/images/frames/frame-2.png'],
        ['id' => 4, 'filename' => 'frame-3.png', 'path' => 'assets/images/frames/frame-3.png'],
        ['id' => 5, 'filename' => 'frame-4.png', 'path' => 'assets/images/frames/frame-4.png'],
        ['id' => 6, 'filename' => 'frame-5.png', 'path' => 'assets/images/frames/frame-5.png'],
    ];
    
    if (!$subscription) {
        return ['frames' => $defaultFrames, 'can_upload' => false, 'max_frames' => 6];
    }
    
    $maxFrames = $subscription['max_frames'] ?? 6;
    $canUpload = $subscription['can_upload_frames'] == 1;
    
    // Limit default frames based on subscription
    $accessibleFrames = array_slice($defaultFrames, 0, min($maxFrames, count($defaultFrames)));
    
    // For Premium users, add their custom frames
    if ($canUpload) {
        $stmt = $pdo->prepare("SELECT * FROM custom_frames WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $customFrames = $stmt->fetchAll();
        
        foreach ($customFrames as $frame) {
            if (count($accessibleFrames) < $maxFrames) {
                $accessibleFrames[] = [
                    'id' => 'custom_' . $frame['id'],
                    'filename' => $frame['filename'],
                    'path' => $frame['file_path'],
                    'is_custom' => true
                ];
            }
        }
    }
    
    return [
        'frames' => $accessibleFrames,
        'can_upload' => $canUpload,
        'max_frames' => $maxFrames,
        'current_count' => count($accessibleFrames)
    ];
}

/**
 * Activate subscription after successful payment
 * @param PDO $pdo
 * @param int $user_id
 * @param int $package_id
 * @return bool
 */
function activateSubscription($pdo, $user_id, $package_id) {
    // Get package details
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ? AND is_active = 1");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    
    if (!$package) {
        return false;
    }
    
    // Deactivate old subscriptions (except Trial)
    $stmt = $pdo->prepare("
        UPDATE user_subscriptions us
        JOIN packages p ON us.package_id = p.id
        SET us.is_active = 0
        WHERE us.user_id = ? AND p.is_trial = 0
    ");
    $stmt->execute([$user_id]);
    
    // Calculate expiry date
    $expiresAt = null;
    if ($package['duration_days']) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $package['duration_days'] . ' days'));
    }
    
    // Create new subscription
    $stmt = $pdo->prepare("
        INSERT INTO user_subscriptions (user_id, package_id, expires_at, is_active, photo_count)
        VALUES (?, ?, ?, 1, 0)
    ");
    return $stmt->execute([$user_id, $package_id, $expiresAt]);
}

/**
 * Get subscription status display info
 * @param PDO $pdo
 * @param int $user_id
 * @return array
 */
function getSubscriptionStatus($pdo, $user_id) {
    $subscription = getUserSubscription($pdo, $user_id);
    
    if (!$subscription) {
        return [
            'package_name' => 'No Subscription',
            'status' => 'inactive',
            'expires_in' => null,
            'photo_remaining' => 0
        ];
    }
    
    $status = [
        'package_name' => $subscription['package_name'],
        'status' => 'active',
        'is_trial' => $subscription['is_trial'] == 1,
        'can_upload_frames' => $subscription['can_upload_frames'] == 1
    ];
    
    if ($subscription['is_trial']) {
        $status['photo_remaining'] = $subscription['max_photos'] - $subscription['photo_count'];
        $status['photo_used'] = $subscription['photo_count'];
        $status['photo_max'] = $subscription['max_photos'];
    } else {
        $status['photo_remaining'] = null; // unlimited
        if ($subscription['expires_at']) {
            $expiresAt = strtotime($subscription['expires_at']);
            $now = time();
            $daysLeft = ceil(($expiresAt - $now) / 86400);
            $status['expires_in'] = $daysLeft > 0 ? $daysLeft . ' hari' : 'Expired';
            $status['expires_at'] = $subscription['expires_at'];
        }
    }
    
    return $status;
}
