<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/db_connect.php';
include '../includes/config.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount']) || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

try {
    // If the secret is still the placeholder, don't even try the API
    if (RAZORPAY_KEY_SECRET === 'test_secret_73849204859302') {
        throw new Exception("Please set your real RAZORPAY_KEY_SECRET in includes/config.php");
    }

    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $orderData = [
        'receipt'         => 'rcpt_' . uniqid(),
        'amount'          => intval($data['amount'] * 100), 
        'currency'        => 'INR',
        'payment_capture' => 1 
    ];

    $razorpayOrder = $api->order->create($orderData);
    echo json_encode(['order_id' => $razorpayOrder['id']]);

} catch (Exception $e) {
    // FALLBACK FOR TESTING: Return a flag so the frontend can use simple checkout
    // This allows the UI to work even without a valid Secret Key
    echo json_encode([
        'order_id' => 'TEST_MODE', 
        'error' => $e->getMessage(),
        'note' => 'Frontend will use simple checkout mode'
    ]);
}

