<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
include '../includes/db_connect.php';
include '../includes/config.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['amount']) && $data['amount'] > 0) {
    $amount = floatval($data['amount']);
    $payment_id = $data['payment_id'] ?? '';
    $order_id = $data['order_id'] ?? '';
    $signature = $data['signature'] ?? '';
    $type = $data['type'] ?? 'MONTHLY';
    $days = ($type === 'YEARLY') ? 365 : 30;

    // Verify Signature (Skip if using temporary/placeholder key for testing)
    if (!empty($signature) && RAZORPAY_KEY_SECRET !== 'test_secret_73849204859302') {
        try {
            $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
            $attributes = array(
                'razorpay_order_id' => $order_id,
                'razorpay_payment_id' => $payment_id,
                'razorpay_signature' => $signature
            );
            $api->utility->verifyPaymentSignature($attributes);
        } catch (SignatureVerificationError $e) {
            echo json_encode(['success' => false, 'message' => 'Signature verification failed: ' . $e->getMessage()]);
            exit();
        }
    }

    $conn->begin_transaction();

    try {
        // 1. Store the Razorpay transaction
        $stmt_tx = $conn->prepare("INSERT INTO transactions (user_id, razorpay_payment_id, razorpay_order_id, razorpay_signature, amount) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_tx) throw new Exception("Prepare failed for transactions: " . $conn->error);

        $stmt_tx->bind_param("isssd", $user_id, $payment_id, $order_id, $signature, $amount);
        if (!$stmt_tx->execute()) throw new Exception("Execute failed for transactions: " . $stmt_tx->error);

        // 2. Check if wallet row exists
        $stmt_check = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
        if (!$stmt_check) throw new Exception("Prepare failed for wallet select: " . $conn->error);
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Update balance and expiry
            $stmt_wallet = $conn->prepare("UPDATE wallet SET balance = ?, plan_start_date = NOW(), expiry_date = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE user_id = ?");
            if (!$stmt_wallet) throw new Exception("Prepare failed for wallet update: " . $conn->error);

            $stmt_wallet->bind_param("dii", $amount, $days, $user_id);
            if (!$stmt_wallet->execute()) throw new Exception("Execute failed for wallet update: " . $stmt_wallet->error);
        } else {
            // Insert new wallet row
            $stmt_wallet = $conn->prepare("INSERT INTO wallet (user_id, balance, plan_start_date, expiry_date) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))");
            if (!$stmt_wallet) throw new Exception("Prepare failed for wallet insert: " . $conn->error);

            $stmt_wallet->bind_param("idi", $user_id, $amount, $days);
            if (!$stmt_wallet->execute()) throw new Exception("Execute failed for wallet insert: " . $stmt_wallet->error);
        }

        $conn->commit();

        // Fetch new balance
        $stmt_fetch = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
        if (!$stmt_fetch) throw new Exception("Prepare failed for balance fetch: " . $conn->error);
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $new_balance = $stmt_fetch->get_result()->fetch_assoc()['balance'];

        // Send Payment Confirmation Email
        // 1. Fetch user email
        $stmt_user = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_res = $stmt_user->get_result();
        
        if ($user_res->num_rows > 0) {
            $user_data = $user_res->fetch_assoc();
            $email = $user_data['email'];
            $username = $user_data['username'];
            
            if ($email) {
                include_once '../includes/mail.php';
                $expiry_date = date('d M Y', strtotime("+$days days")); // Recalculate expiry for display
                
                $subject = "Payment Successful - Pass Activated";
                $title = "Payment Receipt";
                $content = "
                    <h2 style='color: #ffffff; margin-top: 0;'>Payment Successful!</h2>
                    <p>Dear <strong>$username</strong>,</p>
                    <p>Your payment has been successfully processed and your pass is now active.</p>
                    
                    <div style='background-color: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; margin: 20px 0;'>
                        <p style='margin: 5px 0; color: #cbd5e1;'>Amount Paid:</p>
                        <h3 style='margin: 0; color: #10b981; font-size: 24px;'>₹$amount</h3>
                        <br>
                        <p style='margin: 5px 0; color: #cbd5e1;'>Pass Type:</p>
                        <strong style='color: white; font-size: 18px;'>$type Membership</strong>
                        <br><br>
                        <p style='margin: 5px 0; color: #cbd5e1;'>Valid Until:</p>
                        <strong style='color: #f59e0b; font-size: 18px;'>$expiry_date</strong>
                    </div>

                    <p style='font-size: 13px; color: #94a3b8;'>Transaction ID: $payment_id</p>
                    <center><a href='http://localhost/bus_portal/buy-pass.php' class='btn' style='color: #ffffff !important;'>View Current Pass</a></center>
                ";
                
                if (function_exists('get_email_template')) {
                    $body = get_email_template($title, $content);
                } else {
                     $body = $content; // Fallback
                }
                
                sendEmail($email, $subject, $body);
            }
        }

        echo json_encode(['success' => true, 'new_balance' => $new_balance]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
}
?>
