<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_USER, SENDER_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}

if (!function_exists('get_email_template')) {
    function get_email_template($title, $content) {
        $primary = '#6366f1';
        $bg = '#0f172a';
        $surface = '#1e293b';
        $text = '#f8fafc';
        $text_secondary = '#94a3b8';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: $bg; color: $text; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: $surface; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background-color: $primary; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; color: white; font-size: 24px; font-weight: 600; }
                .content { padding: 40px 30px; line-height: 1.6; color: $text; }
                .footer { background-color: $bg; padding: 20px; text-align: center; font-size: 12px; color: $text_secondary; border-top: 1px solid rgba(255,255,255,0.1); }
                .btn { display: inline-block; padding: 12px 24px; background-color: $primary; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div style='padding: 20px;'>
                <div class='email-container'>
                    <div class='header'>
                        <h1>$title</h1>
                    </div>
                    <div class='content'>
                        $content
                    </div>
                    <div class='footer'>
                        &copy; " . date('Y') . " Smart Bus Portal. All rights reserved.<br>
                        Safe • Reliable • Convenient
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
