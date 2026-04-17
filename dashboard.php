 <?php
session_start();
include 'db_connect.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = 'dashboard.php';

// Fetch username for personalized greeting
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_res = $stmt->get_result();
$user_name = ($user_res->num_rows > 0) ? $user_res->fetch_assoc()['username'] : 'User';

// Daily Travel Stats
$limit = 5;
$stmt_count = $conn->prepare("SELECT count(*) as today_count FROM passes WHERE user_id = ? AND DATE(valid_from) = CURDATE()");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$today_count = $stmt_count->get_result()->fetch_assoc()['today_count'];
$remaining_travels = max(0, $limit - $today_count);

// Calculate reset time (midnight)
$timezone = new DateTimeZone('Asia/Kolkata');
$now = new DateTime('now', $timezone);
$midnight = new DateTime('tomorrow midnight', $timezone);
$diff = $now->diff($midnight);
$remaining_time_string = $diff->format('%H:%I:%S');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Bus Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 4rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem;">Manage your transit account and plan your next journey.</p>
    </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
        <!-- Quick Stats -->
        <div class="glass card" style="display: flex; align-items: center; gap: 2rem;">
            <div style="width: 70px; height: 70px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                <i class="fas fa-wallet" style="font-size: 2rem;"></i>
            </div>
            <div>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem;">Wallet Balance</p>
                <?php
                $stmt_bal = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
                $stmt_bal->bind_param("i", $user_id);
                $stmt_bal->execute();
                $bal_data = $stmt_bal->get_result()->fetch_assoc();
                $balance = $bal_data ? $bal_data['balance'] : 0.00;
                ?>
                <h3 style="font-size: 2rem; margin: 0;">₹<?php echo number_format($balance, 2); ?></h3>
            </div>
            <a href="buy-pass.php" class="btn-primary" style="margin-left: auto;">Recharge</a>
        </div>

        <div class="glass card" style="display: flex; align-items: center; gap: 2rem;">
            <div style="width: 70px; height: 70px; background: rgba(99, 102, 241, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                <i class="fas fa-bus" style="font-size: 2rem;"></i>
            </div>
            <div>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem;">Recent Journeys</p>
                <?php
                $stmt_journeys = $conn->prepare("SELECT COUNT(*) as total FROM passes WHERE user_id = ?");
                $stmt_journeys->bind_param("i", $user_id);
                $stmt_journeys->execute();
                $journey_data = $stmt_journeys->get_result()->fetch_assoc();
                $total_journeys = $journey_data ? $journey_data['total'] : 0;
                ?>
                <h3 style="font-size: 2rem; margin: 0;"><?php echo $total_journeys; ?></h3>
            </div>
            <a href="pass-history.php" class="btn-primary" style="margin-left: auto; background: var(--glass-bg); border: 1px solid var(--glass-border);">History</a>
        </div>

        <!-- Daily Travel Limit Card -->
        <div class="glass card" style="display: flex; align-items: center; gap: 2rem;">
            <div style="width: 70px; height: 70px; background: rgba(245, 158, 11, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                <i class="fas fa-clock" style="font-size: 2rem;"></i>
            </div>
            <div style="flex-grow: 1;">
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem;">Daily Travel Limit</p>
                <h3 style="font-size: 1.5rem; margin: 0;"><?php echo $today_count; ?> / <?php echo $limit; ?> Used</h3>
                <div style="margin-top: 0.5rem; background: rgba(255,255,255,0.1); border-radius: 10px; height: 8px; overflow: hidden; width: 100%;">
                    <div style="height: 100%; background: var(--accent); width: <?php echo ($today_count / $limit) * 100; ?>%;"></div>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.5rem;">
                    Resets in: <span id="reset-countdown"><?php echo $remaining_time_string; ?></span>
                </p>
            </div>
            <a href="check-pass.php" class="btn-primary" style="margin-left: auto;">Board</a>
        </div>
    </div>

    <script>
        function updateCountdown() {
            const el = document.getElementById('reset-countdown');
            if (!el) return;
            let parts = el.innerText.split(':');
            let h = parseInt(parts[0]), m = parseInt(parts[1]), s = parseInt(parts[2]);
            s--;
            if (s < 0) { s = 59; m--; if (m < 0) { m = 59; h--; if (h < 0) { window.location.reload(); return; } } }
            const pad = (n) => String(n).padStart(2, '0');
            el.innerText = `${pad(h)}:${pad(m)}:${pad(s)}`;
        }
        setInterval(updateCountdown, 1000);
    </script>

    <div class="glass card" style="padding: 2.5rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-info-circle" style="color: var(--primary);"></i>
            Smart Transit Features
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <div>
                <h4 style="margin-bottom: 0.75rem;"><i class="fas fa-bolt" style="color: var(--accent);"></i> Instant Boarding</h4>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">No more waiting in lines. Generate your pass code and board any smart bus instantly.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 0.75rem;"><i class="fas fa-shield-alt" style="color: var(--secondary);"></i> Secure Payments</h4>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Your transactions are protected with industry-standard encryption through Razorpay.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 0.75rem;"><i class="fas fa-leaf" style="color: #10b981;"></i> Eco-Friendly</h4>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Help us reduce paper waste by using digital passes instead of physical tickets.</p>
            </div>
        </div>
    </div>

    <footer style="margin-top: 5rem; padding-top: 2rem; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-secondary); font-size: 0.9rem;">
        <p>&copy; <?php echo date('Y'); ?> Smart Bus Portal. Empowering Modern Commuters.</p>
    </footer>
</div>

</body>
</html>
