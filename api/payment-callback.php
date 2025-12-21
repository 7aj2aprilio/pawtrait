<?php
require_once '../config/database.php';
require_once '../config/midtrans.php';
require_once '../config/subscription.php';

// Get JSON input from Midtrans
$input = file_get_contents('php://input');
$notification = json_decode($input, true);

// Log notification for debugging
file_put_contents('../logs/midtrans_notification.log', date('Y-m-d H:i:s') . ' - ' . $input . "\n", FILE_APPEND);

if (!$notification) {
    http_response_code(400);
    exit;
}

// Verify signature key
$order_id = $notification['order_id'];
$status_code = $notification['status_code'];
$gross_amount = $notification['gross_amount'];
$signature_key = $notification['signature_key'];

$server_key = MIDTRANS_SERVER_KEY;
$hashed = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

if ($hashed !== $signature_key) {
    http_response_code(403);
    exit;
}

// Get transaction from database
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ?");
$stmt->execute([$order_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    http_response_code(404);
    exit;
}

// Update transaction status based on Midtrans notification
$transaction_status = $notification['transaction_status'];
$fraud_status = isset($notification['fraud_status']) ? $notification['fraud_status'] : null;
$payment_type = $notification['payment_type'];
$transaction_time = $notification['transaction_time'];

$new_status = 'pending';

if ($transaction_status == 'capture') {
    if ($fraud_status == 'accept') {
        $new_status = 'success';
    } else if ($fraud_status == 'challenge') {
        $new_status = 'pending';
    } else {
        $new_status = 'failed';
    }
} else if ($transaction_status == 'settlement') {
    $new_status = 'success';
} else if ($transaction_status == 'pending') {
    $new_status = 'pending';
} else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
    $new_status = 'failed';
}

// Activate subscription if payment successful
if ($new_status == 'success') {
    activateSubscription($pdo, $transaction['user_id'], $transaction['package_id']);
    file_put_contents('../logs/subscription_activation.log', 
        date('Y-m-d H:i:s') . ' - Activated subscription for user ' . $transaction['user_id'] . 
        ' package ' . $transaction['package_id'] . "\n", FILE_APPEND);
}

// Update database
$stmt = $pdo->prepare("
    UPDATE transactions 
    SET transaction_status = ?, 
        payment_type = ?, 
        transaction_time = ?,
        transaction_id = ?,
        settlement_time = ?
    WHERE order_id = ?
");

$settlement_time = isset($notification['settlement_time']) ? $notification['settlement_time'] : null;
$transaction_id = isset($notification['transaction_id']) ? $notification['transaction_id'] : null;

$stmt->execute([
    $new_status,
    $payment_type,
    $transaction_time,
    $transaction_id,
    $settlement_time,
    $order_id
]);

http_response_code(200);
echo json_encode(['status' => 'success']);
