<?php
session_start();
require_once '../config/database.php';
require_once '../config/midtrans.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['total']) || !isset($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$total = $input['total'];
$items = $input['items'];
$customer = $input['customer'];

// Generate unique order ID
$order_id = 'ORDER-' . $user_id . '-' . time();

// Get package ID (assuming single package for now)
$package_id = $items[0]['id'];

// Get user's photos
$stmt = $pdo->prepare("SELECT id FROM photos WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
$photo_ids = json_encode($photos);

// Prepare transaction data for Midtrans
$transaction_details = [
    'order_id' => $order_id,
    'gross_amount' => (int)$total
];

$item_details = [];
foreach ($items as $item) {
    // Generate valid ID for Midtrans (max 50 chars, no spaces)
    $itemId = isset($item['id']) ? substr(preg_replace('/[^a-zA-Z0-9-]/', '', $item['id']), 0, 50) : 'ITEM-001';
    
    $item_details[] = [
        'id' => $itemId, // Midtrans requires ID
        'price' => (int)$item['price'],
        'quantity' => (int)$item['quantity'],
        'name' => substr($item['name'], 0, 50) // Limit name length
    ];
}

$customer_email = filter_var($customer['email'], FILTER_VALIDATE_EMAIL) 
    ? $customer['email'] 
    : 'user' . $user_id . '@pawtrait.com'; // Fallback for invalid emails (e.g. admin accounts)

$customer_details = [
    'first_name' => $customer['name'],
    'email' => $customer_email,
    'phone' => $customer['phone']
];

$midtrans_params = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details,
    'enabled_payments' => ['credit_card', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'other_va', 'qris'],
    'credit_card' => [
        'secure' => MIDTRANS_3DS
    ]
];

// Call Midtrans Snap API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MIDTRANS_API_URL . '/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($midtrans_params));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
]);

// WAJIB BUAT AZURE: Bypass SSL verification biar mau konek
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($http_code == 201 && isset($result['token'])) {
    $snap_token = $result['token'];
    
    // Save transaction to database
    $stmt = $pdo->prepare("
        INSERT INTO transactions 
        (user_id, package_id, order_id, gross_amount, transaction_status, snap_token, photo_ids, customer_name, customer_email, customer_phone) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $package_id,
        $order_id,
        $total,
        'pending',
        $snap_token,
        $photo_ids,
        $customer['name'],
        $customer['email'],
        $customer['phone']
    ]);
    
    // Log success
    error_log("Midtrans Transaction Created: $order_id");
    
    echo json_encode([
        'success' => true,
        'snap_token' => $snap_token,
        'order_id' => $order_id
    ]);
} else {
    // Log detailed error information
    error_log("Midtrans API Error - HTTP Code: $http_code");
    error_log("Midtrans API Response: $response");
    error_log("Midtrans API Request: " . json_encode($midtrans_params));
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create Midtrans transaction',
        'http_code' => $http_code,
        'error' => $result,
        'error' => $result,
        'raw_response' => $response, // Temporarily expose raw response for debugging
        'debug_info' => [
            'api_url' => MIDTRANS_API_URL . '/transactions',
            'server_key_prefix' => substr(MIDTRANS_SERVER_KEY, 0, 5) . '...'
        ]
    ]);
}
