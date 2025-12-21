<?php
// Script to test Midtrans transaction creation manually
session_start();
// Mock session
$_SESSION['user_id'] = 1;

require_once 'config/database.php';
require_once 'config/midtrans.php';

echo "Testing Midtrans Connection...\n";
echo "Server Key: " . substr(MIDTRANS_SERVER_KEY, 0, 5) . "...\n";
echo "API URL: " . MIDTRANS_API_URL . "\n\n";

// Mock Data
$order_id = 'TEST-' . time();
$total = 10000;
$items = [
    [
        'id' => 'PKG-BASIC',
        'price' => 10000,
        'quantity' => 1,
        'name' => 'Basic Package'
    ]
];
$customer = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '081234567890'
];

$transaction_details = [
    'order_id' => $order_id,
    'gross_amount' => $total
];

$item_details = [];
foreach ($items as $item) {
    $item_details[] = [
        'id' => $item['id'],
        'price' => (int)$item['price'],
        'quantity' => (int)$item['quantity'],
        'name' => $item['name']
    ];
}

$customer_details = [
    'first_name' => $customer['name'],
    'email' => $customer['email'],
    'phone' => $customer['phone']
];

$midtrans_params = [
    'transaction_details' => $transaction_details,
    'item_details' => $item_details,
    'customer_details' => $customer_details,
];

echo "Selling Data:\n";
print_r($midtrans_params);

// Call Midtrans
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

echo "\nSending Request...\n";
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response:\n$response\n";
