<?php
session_start();
require_once '../vendor/autoload.php';
require_once '../db_connect.php'; // For database connection if needed
if (file_exists('../includes/mail.php')) {
    require_once '../includes/mail.php';
}

use Twilio\Rest\Client;

header('Content-Type: application/json');

/* 
 * OTP SETTINGS 
 */
define('SMS_GATEWAY', 'TWILIO'); 
define('TWILIO_SID', 'YOUR_TWILIO_SID_HERE'); 
define('TWILIO_TOKEN', 'YOUR_TWILIO_TOKEN_HERE');
define('TWILIO_FROM', 'YOUR_TWILIO_NUMBER_HERE');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? ''; // 'email' or 'mobile'

    if ($action === 'send_otp') {
        $email = $_POST['email'] ?? '';
        $mobile = $_POST['mobile'] ?? '';
        $target = ($type === 'email') ? $email : $mobile;

        if (empty($target)) {
            echo json_encode(['success' => false, 'message' => ucfirst($type) . ' is required']);
            exit;
        }

        // Check if already exists in DB
        if (isset($conn)) {
            if ($type === 'email') {
                $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
            }
            $check->bind_param("s", $target);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => ucfirst($type) . ' is already registered!']);
                exit;
            }
        }

        // 1️⃣ Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // 2️⃣ Store in session with timestamp
        $_SESSION["register_{$type}_otp"] = $otp;
        $_SESSION["register_{$type}_time"] = time();

        $sent = false;

        if ($type === 'email') {
            if (function_exists('sendEmail')) {
                $subject = "Email Verification - Smart Bus Portal";
                $title = "Verify Your Email";
                $content = "
                    <h2 style='color: #ffffff; margin-top: 0;'>Verification Code</h2>
                    <p>Use the following OTP to verify your <strong>email address</strong>:</p>
                    <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 12px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #10b981;'>$otp</span>
                    </div>
                    <p style='color: #94a3b8; font-size: 14px;'>Valid for 5 minutes.</p>
                ";
                $body = get_email_template($title, $content);
                $sent = sendEmail($email, $subject, $body);
            }
        } else {
            // 3️⃣ MOBILE OTP LOGIC (Twilio)
            if (SMS_GATEWAY === 'TWILIO') {
                try {
                    $client = new Client(TWILIO_SID, TWILIO_TOKEN);
                    
                    // Simple logic to add +91 if it's 10 digits and doesn't start with +
                    $formatted_mobile = $mobile;
                    if (strlen($mobile) == 10 && strpos($mobile, '+') !== 0) {
                        $formatted_mobile = "+91" . $mobile;
                    } elseif (strpos($mobile, '+') !== 0) {
                        // Default to adding + if missing, but maybe it's already a full number
                        $formatted_mobile = "+" . $mobile;
                    }

                    $client->messages->create(
                        $formatted_mobile,
                        [
                            "from" => TWILIO_FROM,
                            "body" => "Your Smart Bus Portal OTP is $otp. Valid for 5 minutes."
                        ]
                    );
                    $sent = true;
                } catch (Exception $e) {
                    $sent = false;
                    $error_message = $e->getMessage();
                }
            } else {
                // SIMULATION MODE
                $sent = true; 
            }
        }

        if ($sent) {
            $msg = ($type === 'email') ? "OTP has been sent to your email" : "OTP sent to ".substr($mobile, 0, 2)."XXXXXX".substr($mobile, -2);
            $response = ['success' => true, 'message' => $msg];
            echo json_encode($response);
        } else {
            $error_info = isset($error_message) ? " (" . $error_message . ")" : "";
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP.' . $error_info]);
        }
    } 
    elseif ($action === 'verify_otp') {
        $user_otp = $_POST['otp'] ?? '';
        
        if (!isset($_SESSION["register_{$type}_otp"])) {
            echo json_encode(['success' => false, 'message' => 'No OTP found. Please click Resend.']);
            exit;
        }

        // 4️⃣ Check expiry (5 minutes)
        if (time() - $_SESSION["register_{$type}_time"] > 300) {
            unset($_SESSION["register_{$type}_otp"]);
            echo json_encode(['success' => false, 'message' => 'OTP has expired!']);
            exit;
        }

        if ($user_otp === $_SESSION["register_{$type}_otp"]) {
            $_SESSION["{$type}_verified"] = true;
            echo json_encode(['success' => true, 'message' => 'Verified']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
        }
    }
}
?>
