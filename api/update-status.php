<?php
// api/update-status.php
require_once '../config/database.php';
require_once '../config/midtrans.php';
require_once '../config/subscription.php';

header('Content-Type: application/json');

// 1. Get all pending transactions
$stmt = $pdo->query("SELECT * FROM transactions WHERE transaction_status = 'pending'");
$transactions = $stmt->fetchAll();

$updated = 0;
$results = [];

foreach ($transactions as $transaction) {
    $order_id = $transaction['order_id'];
    
    // 2. Check status with Midtrans API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MIDTRANS_CORE_API_URL . '/' . $order_id . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $status_data = json_decode($response, true);
        $transaction_status = $status_data['transaction_status'];
        $fraud_status = isset($status_data['fraud_status']) ? $status_data['fraud_status'] : null;
        
        $new_status = 'pending';
        
        if ($transaction_status == 'capture') {
            if ($fraud_status == 'accept') {
                $new_status = 'success';
            }
        } else if ($transaction_status == 'settlement') {
            $new_status = 'success';
        } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
            $new_status = 'failed';
        }
        
        if ($new_status != 'pending') {
            // Update DB
            $updateStmt = $pdo->prepare("UPDATE transactions SET transaction_status = ? WHERE order_id = ?");
            $updateStmt->execute([$new_status, $order_id]);
            
            // Activate subscription if success
            if ($new_status == 'success') {
                activateSubscription($pdo, $transaction['user_id'], $transaction['package_id']);
            }
            
            $updated++;
            $results[] = "Order $order_id updated to $new_status";
        } else {
            $results[] = "Order $order_id is still pending (Midtrans status: $transaction_status)";
        }
    } else {
        $results[] = "Failed to check order $order_id. HTTP Code: $http_code";
    }
}

echo json_encode([
    'success' => true,
    'message' => "Checked " . count($transactions) . " pending transactions. Updated $updated.",
    'details' => $results,
    // 'debug_transactions' => $transactions // Uncomment if needed
]);
