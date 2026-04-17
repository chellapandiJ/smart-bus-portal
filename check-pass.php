<?php
session_start();
include 'db_connect.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$boarding_success = null;
$error = "";
$current_page = 'check-pass.php';

// Initialize variables
$today_count = 0;
$limit = 5;
$remaining_travels = 5;
$expiry_date = null;
$plan_active = false;

// Fetch today's travel count
$stmt_count = $conn->prepare("SELECT count(*) as today_count FROM passes WHERE user_id = ? AND DATE(valid_from) = CURDATE()");
if ($stmt_count) {
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $res = $stmt_count->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $today_count = $row['today_count'];
    }
}
$limit = 5;
$remaining_travels = max(0, $limit - $today_count);

// Calculate remaining time until reset (midnight)
$timezone = new DateTimeZone('Asia/Kolkata');
$now = new DateTime('now', $timezone);
$midnight = new DateTime('tomorrow midnight', $timezone);
$diff = $now->diff($midnight);
$remaining_time_string = $diff->format('%H:%I:%S');

// Handle Boarding (Code Redemption)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['board_bus'])) {
    $code = strtoupper(trim($_POST['bus_code']));
    $selected_start = trim($_POST['selected_start']);
    $selected_end = trim($_POST['selected_end']);
    
    // 1. Fetch User City
    $stmt_user = $conn->prepare("SELECT city FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_city = $stmt_user->get_result()->fetch_assoc()['city'];

    // 2. Validate Bus Code and City Match
    $stmt = $conn->prepare("SELECT * FROM bus_codes WHERE code = ? AND city = ? AND is_active = 1");
    $stmt->bind_param("ss", $code, $user_city);
    $stmt->execute();
    $bus_res = $stmt->get_result();
    
    if ($bus_res->num_rows > 0) {
        if ($selected_start === $selected_end) {
            $error = "Starting Point and Ending Point cannot be the same.";
        } else {
            // Check Daily Limit
            if ($today_count >= $limit) {
                $error = "Daily Limit Reached! You have already traveled 5 times today. Please wait for the daily reset.";
            } else {
                // 3. Check Pass Expiry
                $stmt = $conn->prepare("SELECT expiry_date FROM wallet WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $userData = $stmt->get_result()->fetch_assoc();
                $expiryDate = $userData['expiry_date'];

                $is_plan_valid = ($expiryDate && strtotime($expiryDate) > time());

                if ($is_plan_valid) {
                    // Trip logic
                    $price = 0.00; 
                    $valid_until = date('Y-m-d H:i:s', strtotime('+2 hours'));

                    // Store in database with selected points
                    $stmt_insert = $conn->prepare("INSERT INTO passes (user_id, pass_code, start_point, end_point, price, valid_until) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("isssds", $user_id, $code, $selected_start, $selected_end, $price, $valid_until);
                    
                    if ($stmt_insert->execute()) {
                        $boarding_success = [
                            'city' => $user_city,
                            'pass_code' => $code,
                            'start_point' => $selected_start,
                            'end_point' => $selected_end
                        ];
                        
                        $today_count++;
                        $remaining_travels = max(0, $limit - $today_count);
                    } else {
                        $error = "System Error: Could not record journey. " . $conn->error;
                    }

                } else {
                    $error = "No Active Pass! Please purchase a Monthly or Yearly pass to travel.";
                }
            }
        }
    } else {
        $error = "Access Denied! You are registered in **" . $user_city . "**. You can only travel using bus codes from your own District.";
    }
}

// Fetch current expiry
$stmt = $conn->prepare("SELECT expiry_date FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_res = $stmt->get_result();

if ($wallet_res->num_rows > 0) {
    $wallet_data = $wallet_res->fetch_assoc();
    $expiry_date = $wallet_data['expiry_date'];
    $plan_active = ($expiry_date && strtotime($expiry_date) > time());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Board Bus - Smart Bus Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="animate-fade-in">

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Board the Bus</h1>
            <p style="color: var(--text-secondary);">Enter the bus identification code to generate your trip pass</p>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem; margin-top: 2rem;">
                <div class="glass" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; padding: 0.75rem 1.5rem; border-radius: 1.5rem; min-width: 150px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-id-card" style="color: <?php echo $plan_active ? '#10b981' : '#fca5a5'; ?>;"></i>
                        <span style="font-weight: 600;"><?php echo $plan_active ? 'Active Pass' : 'No Pass'; ?></span>
                    </div>
                    <?php if (isset($expiry_date) && $expiry_date && $plan_active): ?>
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">
                            <i class="fas fa-calendar-alt"></i> Expires: <?php echo date('d M Y', strtotime($expiry_date)); ?>
                        </span>
                    <?php elseif (!$plan_active): ?>
                         <span style="font-size: 0.75rem; color: var(--text-secondary);">
                            <a href="buy-pass.php" style="color: var(--primary);">Buy Now</a>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="glass" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; padding: 0.75rem 1.5rem; border-radius: 1.5rem; min-width: 150px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-bus" style="color: var(--primary);"></i>
                        <span style="font-weight: 600;">Travels Today: <?php echo $today_count; ?>/<?php echo $limit; ?></span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-secondary);">
                        <i class="fas fa-ticket-alt"></i> Remaining: <?php echo $remaining_travels; ?>
                    </span>
                </div>

                <div class="glass" style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem; padding: 0.75rem 1.5rem; border-radius: 1.5rem; min-width: 150px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-clock" style="color: var(--accent);"></i>
                        <span style="font-weight: 600;">Limit Resets In</span>
                    </div>
                    <span id="reset-countdown" style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">
                        <?php echo $remaining_time_string; ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="animate-fade-in" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 1.25rem; border-radius: 1rem; margin-bottom: 2rem; display: flex; align-items: flex-start; gap: 1rem;">
                <i class="fas fa-exclamation-triangle" style="margin-top: 0.25rem;"></i>
                <div>
                   <strong style="display: block; margin-bottom: 0.25rem;">Boarding Error</strong>
                   <?php echo $error; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($boarding_success): ?>
            <div id="capture-pass" class="glass-card animate-fade-in" style="padding: 0; overflow: hidden; border: none; background: #fff; color: #1e293b; max-width: 500px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, var(--secondary), #059669); padding: 2rem; text-align: center; color: white;">
                    <div style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i class="fas fa-check" style="font-size: 1.5rem;"></i>
                    </div>
                    <h2 style="font-size: 1.5rem; margin: 0;">Boarding Confirmed</h2>
                    <p style="opacity: 0.9; font-size: 0.875rem; margin-top: 0.25rem;">Smart Bus Portal</p>
                </div>

                <div style="padding: 2rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px dashed #e2e8f0;">
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Ticket Status</p>
                            <p style="font-weight: 700; color: #059669;">VALID FOR TRAVEL</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Pass Code</p>
                            <p style="font-weight: 700;"><?php echo $boarding_success['pass_code']; ?></p>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.5rem; border-radius: 1rem; border: 1px solid #e2e8f0;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Starting Point</p>
                                <p style="font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($boarding_success['start_point']); ?></p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Ending Point</p>
                                <p style="font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($boarding_success['end_point']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">District</p>
                            <div style="font-size: 1rem; font-weight: 700;">
                                <?php echo htmlspecialchars($boarding_success['city']); ?>
                            </div>
                        </div>
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">Boarding Time</p>
                            <div style="font-size: 1rem; font-weight: 700;">
                                <?php echo date('h:i:s A'); ?>
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.5rem; text-align: center;">
                        <p style="font-size: 0.875rem; color: #64748b; font-weight: 600;">Access Granted</p>
                        <p style="font-size: 1.25rem; color: #10b981; font-weight: 700; margin-top: 0.5rem;">You may board the bus</p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 3rem; display: flex; gap: 1rem; justify-content: center;">
                <button id="downloadPass" class="btn-primary" style="padding: 1rem 2rem;">
                    <i class="fas fa-download"></i>
                    <span>Download Receipt</span>
                </button>
                <a href="check-pass.php" class="btn-primary" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border); color: white;">
                    <i class="fas fa-home"></i>
                    <span>Back to Portal</span>
                </a>
            </div>

            <script>
            document.getElementById('downloadPass').addEventListener('click', function() {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerText = 'Creating PNG...';
                
                html2canvas(document.querySelector("#capture-pass"), {
                    backgroundColor: "#ffffff",
                    scale: 3
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'Bus_Boarding_Confirmation.png';
                    link.href = canvas.toDataURL("image/png");
                    link.click();
                    
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
            </script>
        <?php else: ?>
            <div id="boarding-stage-1" class="glass card animate-fade-in" style="max-width: 500px; margin: 0 auto; padding: 3rem;">
                <div style="margin-bottom: 2rem; text-align: center;">
                    <div style="width: 70px; height: 70px; background: rgba(99, 102, 241, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--primary); margin: 0 auto 1.5rem;">
                        <i class="fas fa-qrcode" style="font-size: 2rem;"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Ready to travel?</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Enter the bus code from the scanner</p>
                </div>

                <div class="input-group">
                    <input type="text" id="bus_code_input" class="input-field" placeholder="BUS-CODE-HERE" required maxlength="15" style="text-align: center; font-size: 1.5rem; letter-spacing: 5px; text-transform: uppercase;">
                </div>

                <button type="button" id="validate_code_btn" class="btn-primary" style="width: 100%; justify-content: center; padding: 1.25rem; font-size: 1.1rem;">
                    <i class="fas fa-search"></i>
                    <span>Verify Route</span>
                </button>
                
                <p id="verify-error" style="color: #fca5a5; font-size: 0.85rem; margin-top: 1rem; text-align: center; display: none;"></p>
            </div>

            <!-- Stage 2: Select Stops -->
            <div id="boarding-stage-2" class="glass card animate-fade-in" style="max-width: 500px; margin: 0 auto; padding: 3rem; display: none;">
                <form action="check-pass.php" method="POST">
                    <input type="hidden" name="bus_code" id="hidden_bus_code">
                    
                    <div style="margin-bottom: 2rem; text-align: center;">
                        <div style="width: 70px; height: 70px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--secondary); margin: 0 auto 1.5rem;">
                            <i class="fas fa-map-marked-alt" style="font-size: 2rem;"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;" id="route-title">Select Journey</h3>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">Confirm your boarding and destination</p>
                        <div id="route-info" style="margin-top: 1rem; padding: 0.75rem; background: rgba(99, 102, 241, 0.1); border-radius: 0.75rem; font-size: 0.85rem; color: var(--primary); display: none;">
                            <i class="fas fa-info-circle"></i> Standard Route: <span id="std-route-text"></span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label style="color: var(--primary); font-size: 0.8rem; font-weight: 600;">BOARDING AT</label>
                        <select name="selected_start" id="start_point_select" class="input-field" required style="background: #1e293b; color: white;">
                            <option value="">Starting Point</option>
                        </select>
                    </div>

                    <div class="input-group" style="margin-top: 1.5rem;">
                        <label style="color: var(--secondary); font-size: 0.8rem; font-weight: 600;">DESTINATION</label>
                        <select name="selected_end" id="end_point_select" class="input-field" required style="background: #1e293b; color: white;">
                            <option value="">Select Destination</option>
                        </select>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button type="button" onclick="goToStage1()" class="btn-primary" style="background: transparent; border: 1px solid var(--border); color: var(--text-secondary); flex: 1; justify-content: center;">
                            Back
                        </button>
                        <button type="submit" name="board_bus" class="btn-primary" style="flex: 2; justify-content: center; background: linear-gradient(135deg, var(--secondary), #059669);">
                            Confirm Boarding
                        </button>
                    </div>
                </form>
            </div>

            <script>
            const stage1 = document.getElementById('boarding-stage-1');
            const stage2 = document.getElementById('boarding-stage-2');
            const verifyError = document.getElementById('verify-error');
            const validateBtn = document.getElementById('validate_code_btn');
            const codeInput = document.getElementById('bus_code_input');

            function goToStage1() {
                stage2.style.display = 'none';
                stage1.style.display = 'block';
            }

            validateBtn.addEventListener('click', async () => {
                const code = codeInput.value.trim();
                if (!code) return;

                validateBtn.disabled = true;
                validateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
                verifyError.style.display = 'none';

                try {
                    const response = await fetch('handlers/get_route_info_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ code: code })
                    });
                    const data = await response.json();

                    if (data.success) {
                        document.getElementById('hidden_bus_code').value = code.toUpperCase();
                        document.getElementById('route-title').innerText = `Bus ${code} - ${data.city}`;
                        
                        const startSelect = document.getElementById('start_point_select');
                        const endSelect = document.getElementById('end_point_select');
                        
                        // Clear sections
                        startSelect.innerHTML = '<option value="">Starting Point</option>';
                        endSelect.innerHTML = '<option value="">Select Destination</option>';
                        
                        data.stops.forEach(stop => {
                            const opt = document.createElement('option');
                            opt.value = stop;
                            opt.innerText = stop;
                            startSelect.appendChild(opt.cloneNode(true));
                            endSelect.appendChild(opt.cloneNode(true));
                        });

                        // Set default values if available
                        if (data.start_point) startSelect.value = data.start_point;
                        if (data.end_point) endSelect.value = data.end_point;

                        // Show standard route info
                        if (data.start_point && data.end_point) {
                            document.getElementById('std-route-text').innerText = `${data.start_point} ➔ ${data.end_point}`;
                            document.getElementById('route-info').style.display = 'block';
                        } else {
                            document.getElementById('route-info').style.display = 'none';
                        }

                        stage1.style.display = 'none';
                        stage2.style.display = 'block';
                    } else {
                        verifyError.innerText = data.message;
                        verifyError.style.display = 'block';
                    }
                } catch (err) {
                    verifyError.innerText = 'Network error. Please try again.';
                    verifyError.style.display = 'block';
                } finally {
                    validateBtn.disabled = false;
                    validateBtn.innerHTML = '<i class="fas fa-search"></i> Verify Route';
                }
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<script>
    // Live Countdown Timer
    function updateCountdown() {
        const countdownElement = document.getElementById('reset-countdown');
        if (!countdownElement) return;

        let timeParts = countdownElement.innerText.split(':');
        let h = parseInt(timeParts[0]);
        let m = parseInt(timeParts[1]);
        let s = parseInt(timeParts[2]);

        s--;
        if (s < 0) {
            s = 59;
            m--;
            if (m < 0) {
                m = 59;
                h--;
                if (h < 0) {
                    window.location.reload();
                    return;
                }
            }
        }

        const format = (num) => String(num).padStart(2, '0');
        countdownElement.innerText = `${format(h)}:${format(m)}:${format(s)}`;
    }
    setInterval(updateCountdown, 1000);
</script>

</body>
</html>
