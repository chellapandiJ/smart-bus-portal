<?php
session_start();
include 'db_connect.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email was verified
    if (!isset($_SESSION['email_verified']) || $_SESSION['email_verified'] !== true) {
        $error = "Please verify your email with OTP first!";
    } else {
        if (strlen($_POST['password']) < 8) {
            $error = "Password must be at least 8 characters long!";
        } else {
            $user = $_POST['username'];
            $email = $_POST['email']; 
            $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $age = $_POST['age'];
            $age = $_POST['age'];
            $city = $_POST['city'];
            $gender = $_POST['gender'];

            // Clear verification sessions
            unset($_SESSION['email_verified']);
            unset($_SESSION['register_email_otp']);

            // Check if username OR email exists
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $user, $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $error = "Name or Email already exists!";
            } else {
                // Generate unique recovery code
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $recovery_code = "";
                for ($i = 0; $i < 24; $i++) {
                    $recovery_code .= $chars[mt_rand(0, strlen($chars) - 1)];
                }

                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, age, city, gender, recovery_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisss", $user, $email, $pass, $age, $city, $gender, $recovery_code);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    $conn->query("INSERT INTO wallet (user_id, balance) VALUES ($user_id, 0.00)");

                    // Send Welcome Email
                    if (file_exists('includes/mail.php')) {
                        include_once 'includes/mail.php';
                        if (function_exists('sendEmail')) {
                            $subject = "Welcome to Smart Bus Portal!";
                            $title = "Welcome Aboard!";
                            $content = "
                                <h2 style='color: #ffffff; margin-top: 0;'>Hello, $user!</h2>
                                <p>Thank you for joining the <strong>Smart Bus Portal</strong>.</p>
                            ";
                            $body = get_email_template($title, $content);
                            sendEmail($email, $subject, $body);
                        }
                    }
                    
                    $_SESSION['reg_user'] = $user;
                    $_SESSION['reg_pass'] = $_POST['password'];

                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = "Registration failed!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Bus Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in auth-page" style="background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('bus_bg.png') center/cover fixed;">

<div class="portal-heading" style="text-align: center; margin-bottom: 0.5rem;">
    <a href="index.php" style="text-decoration: none;">
        <h1 style="font-size: 2rem; color: #fff; text-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-bottom: 0.1rem;">Smart Bus Portal</h1>
    </a>
    <p style="font-size: 0.9rem; color: var(--text-secondary);">Safe • Reliable • Convenient</p>
</div>

<div class="auth-card glass-card animate-fade-in" style="margin: 0 auto; width: 100%; max-width: 580px; padding: 2rem;">
    <div style="margin-bottom: 1.25rem; text-align: center;">
        <div style="width: 50px; height: 50px; background: var(--secondary); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);">
            <i class="fas fa-user-plus" style="font-size: 1.5rem; color: white;"></i>
        </div>
        <h2 style="font-size: 1.4rem; color: white; margin-bottom: 0.2rem;">Join the Journey</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">Create your account in seconds</p>
    </div>

    <?php if($error): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <!-- Row 1: Username & Password -->
        <!-- Row 1: Username & Password -->
        <div class="responsive-grid grid-2">
            <div class="input-group" style="text-align: left;">
                <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">Full Name</label>
                <input type="text" name="username" class="input-field" placeholder="Enter full name" required>
            </div>
            <div class="input-group" style="text-align: left;">
                <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">Password</label>
                <input type="password" name="password" class="input-field" placeholder="Min 8 characters" required minlength="8">
            </div>
        </div>



        <!-- Row 3: Email Verification -->
        <div class="input-group" style="text-align: left;">
            <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">Email Address</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="email" name="email" id="email" class="input-field" placeholder="your@email.com" required>
                <button type="button" id="email_send_btn" class="btn-primary" style="padding: 0 1rem; flex-shrink: 0; background: var(--secondary); margin-top: 0;">
                    <span>Send OTP</span>
                </button>
                <div id="email_verified_badge" style="display: none; align-items: center; justify-content: center; color: var(--secondary); padding: 0 0.5rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
                </div>
            </div>
            <div id="email_otp_status" style="font-size: 0.75rem; margin-top: 0.4rem; color: var(--secondary); display: none;"></div>

            <div id="email_otp_container" style="display: none; margin-top: 0.75rem;">
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="email_otp_input" class="input-field" placeholder="Email OTP" maxlength="6" style="text-align: center; letter-spacing: 2px; font-weight: 700;">
                    <button type="button" id="email_verify_btn" class="btn-primary" style="background: var(--primary); padding: 0 1rem; margin-top: 0;">Verify</button>
                </div>
                <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span id="email_timer" style="font-size: 0.7rem; color: var(--text-secondary);"></span>
                    <button type="button" id="email_resend_btn" style="display: none; background: none; border: none; color: var(--primary); font-size: 0.75rem; cursor: pointer; text-decoration: underline; padding: 0;">Resend OTP</button>
                </div>
            </div>
        </div>

        <!-- Row 4: Age, City, Gender -->
        <!-- Row 4: Age, City, Gender -->
        <div class="responsive-grid grid-3">
             <div class="input-group" style="text-align: left;">
                <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">Age</label>
                <input type="number" name="age" class="input-field" placeholder="Age" min="1" required>
            </div>
            <div class="input-group" style="text-align: left;">
                <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">District</label>
                <select name="city" class="input-field" required style="cursor: pointer;">
                    <option value="" disabled selected>Select District</option>
                    <option value="Ariyalur">Ariyalur</option>
                    <option value="Chengalpattu">Chengalpattu</option>
                    <option value="Chennai">Chennai</option>
                    <option value="Coimbatore">Coimbatore</option>
                    <option value="Cuddalore">Cuddalore</option>
                    <option value="Dharmapuri">Dharmapuri</option>
                    <option value="Dindigul">Dindigul</option>
                    <option value="Erode">Erode</option>
                    <option value="Kallakurichi">Kallakurichi</option>
                    <option value="Kanchipuram">Kanchipuram</option>
                    <option value="Kanyakumari">Kanyakumari</option>
                    <option value="Karur">Karur</option>
                    <option value="Krishnagiri">Krishnagiri</option>
                    <option value="Madurai">Madurai</option>
                    <option value="Mayiladuthurai">Mayiladuthurai</option>
                    <option value="Nagapattinam">Nagapattinam</option>
                    <option value="Namakkal">Namakkal</option>
                    <option value="Nilgiris">Nilgiris</option>
                    <option value="Perambalur">Perambalur</option>
                    <option value="Pudukkottai">Pudukkottai</option>
                    <option value="Ramanathapuram">Ramanathapuram</option>
                    <option value="Ranipet">Ranipet</option>
                    <option value="Salem">Salem</option>
                    <option value="Sivaganga">Sivaganga</option>
                    <option value="Tenkasi">Tenkasi</option>
                    <option value="Thanjavur">Thanjavur</option>
                    <option value="Theni">Theni</option>
                    <option value="Thoothukudi">Thoothukudi</option>
                    <option value="Tiruchirappalli">Tiruchirappalli</option>
                    <option value="Tirunelveli">Tirunelveli</option>
                    <option value="Tirupathur">Tirupathur</option>
                    <option value="Tiruppur">Tiruppur</option>
                    <option value="Tiruvallur">Tiruvallur</option>
                    <option value="Tiruvannamalai">Tiruvannamalai</option>
                    <option value="Tiruvarur">Tiruvarur</option>
                    <option value="Vellore">Vellore</option>
                    <option value="Viluppuram">Viluppuram</option>
                    <option value="Virudhunagar">Virudhunagar</option>
                </select>
            </div>
            <div class="input-group" style="text-align: left;">
                <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.4rem; margin-left: 0.5rem;">Gender</label>
                <select name="gender" class="input-field" required style="cursor: pointer;">
                    <option value="" disabled selected>Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <button type="submit" id="submit_btn" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; font-size: 1rem; margin-top: 1rem; background: var(--secondary);">
            <span>Create Account</span>
            <i class="fas fa-check-circle"></i>
        </button>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const registerForm = document.querySelector('form');
        const submitBtn = document.getElementById('submit_btn');

        // --- OTP TIMER HELPERS ---
        function startTimer(duration, display, resendBtn) {
            let timer = duration, minutes, seconds;
            resendBtn.style.display = 'none';
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = "Resend available in " + minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "";
                    resendBtn.style.display = 'inline-block';
                }
            }, 1000);
            return interval;
        }

        let emailTimerInterval;



        // --- EMAIL ELEMENTS ---
        const emailInput = document.getElementById('email');
        const emailSendBtn = document.getElementById('email_send_btn');
        const emailVerifiedBadge = document.getElementById('email_verified_badge');
        const emailOtpContainer = document.getElementById('email_otp_container');
        const emailOtpInput = document.getElementById('email_otp_input');
        const emailVerifyBtn = document.getElementById('email_verify_btn');
        const emailOtpStatus = document.getElementById('email_otp_status');
        const emailResendBtn = document.getElementById('email_resend_btn');
        const emailTimerDisplay = document.getElementById('email_timer');

        // Email input show/hide send button
        emailInput.addEventListener('input', () => {
            if (emailInput.value && emailInput.checkValidity() && emailVerifiedBadge.style.display === 'none') {
                emailSendBtn.style.display = 'flex';
            } else {
                emailSendBtn.style.display = 'none';
            }
        });

        async function sendEmailOtp() {
            emailSendBtn.classList.add('loading');
            emailResendBtn.classList.add('loading');
            emailOtpStatus.style.display = 'none';

            const fd = new FormData();
            fd.append('action', 'send_otp');
            fd.append('type', 'email');
            fd.append('email', emailInput.value);

            try {
                const res = await fetch('handlers/otp_handler.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    emailSendBtn.style.display = 'none';
                    emailOtpContainer.style.display = 'block';
                    emailOtpStatus.style.display = 'block';
                    emailOtpStatus.innerHTML = '✅ ' + data.message;
                    
                    if(emailTimerInterval) clearInterval(emailTimerInterval);
                    emailTimerInterval = startTimer(60, emailTimerDisplay, emailResendBtn);
                    
                    if(data.debug_otp) console.log("Email OTP:", data.debug_otp);
                } else { alert(data.message); }
            } catch (e) { alert('Error.'); }
            finally { 
                emailSendBtn.classList.remove('loading');
                emailResendBtn.classList.remove('loading');
            }
        }

        emailSendBtn.addEventListener('click', sendEmailOtp);
        emailResendBtn.addEventListener('click', sendEmailOtp);

        emailVerifyBtn.addEventListener('click', async () => {
            if (emailOtpInput.value.length !== 6) return alert('Enter 6 digit OTP');
            emailVerifyBtn.classList.add('loading');
            const fd = new FormData();
            fd.append('action', 'verify_otp');
            fd.append('type', 'email');
            fd.append('otp', emailOtpInput.value);

            try {
                const res = await fetch('handlers/otp_handler.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    emailOtpContainer.style.display = 'none';
                    emailOtpStatus.style.display = 'none';
                    emailVerifiedBadge.style.display = 'flex';
                    emailInput.readOnly = true;
                    emailInput.style.opacity = '0.7';
                } else { alert(data.message); }
            } catch (e) { alert('Error.'); }
            finally { emailVerifyBtn.classList.remove('loading'); }
        });

        // --- SUBMIT VALIDATION ---
        registerForm.addEventListener('submit', (e) => {
            if (emailVerifiedBadge.style.display === 'none') {
                e.preventDefault();
                alert('Please verify your email first!');
                emailInput.focus();
            } else {
                submitBtn.classList.add('loading');
            }
        });

        // --- SCROLL TO TOP LOGIC ---
        const scrollTopBtn = document.getElementById("scrollTopBtn");
        window.onscroll = function() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollTopBtn.style.display = "flex";
                scrollTopBtn.style.opacity = "1";
            } else {
                scrollTopBtn.style.opacity = "0";
                setTimeout(() => {
                    if(scrollTopBtn.style.opacity === "0") scrollTopBtn.style.display = "none";
                }, 300);
            }
        };
        scrollTopBtn.onclick = function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        };
    });
    </script>

    <style>
    select.input-field { color: var(--text-secondary); }
    select.input-field option { background: #1e293b; color: white; }
    select.input-field:valid { color: var(--secondary); font-weight: 600; }
    </style>

    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Already have an account? <a href="login.php" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Login here</a>
        </p>
    </div>
</div>

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" title="Go to top" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 99; border: none; outline: none; background: var(--primary); color: white; cursor: pointer; padding: 15px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: 0.3s; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
    <i class="fas fa-arrow-up"></i>
</button>
</body>
</html>
