<?php
session_start();
include 'db_connect.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = 'pass-history.php';

// Fetch History
$stmt = $conn->prepare("SELECT p.*, u.city as registration_city FROM passes p JOIN users u ON p.user_id = u.id WHERE p.user_id = ? ORDER BY p.valid_from DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history_result = $stmt->get_result();

// Fetch daily summary
$stmt_daily = $conn->prepare("SELECT DATE(valid_from) as travel_date, COUNT(*) as daily_count 
                             FROM passes WHERE user_id = ? 
                             GROUP BY DATE(valid_from) 
                             ORDER BY travel_date DESC LIMIT 7");
$stmt_daily->bind_param("i", $user_id);
$stmt_daily->execute();
$daily_summary = $stmt_daily->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Journey History - Smart Bus Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Review Journeys</h1>
            <p style="color: var(--text-secondary);">Track your recent travels and pass validity</p>
        </div>
        <a href="check-pass.php" class="btn-primary">
            <i class="fas fa-plus"></i>
            <span>New Journey</span>
        </a>
    </div>

    <!-- Daily Summary -->
    <div style="margin-bottom: 3rem;">
        <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; color: var(--text-primary);">Recent Activity</h3>
        <div style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem;">
            <?php 
            if ($daily_summary->num_rows > 0):
                while($day = $daily_summary->fetch_assoc()): 
                    $date_label = (date('Y-m-d') == $day['travel_date']) ? 'Today' : date('d M', strtotime($day['travel_date']));
                    $percentage = min(100, ($day['daily_count'] / 5) * 100);
            ?>
                <div class="glass" style="min-width: 120px; padding: 1.25rem; text-align: center; border-radius: 1rem;">
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.5rem;"><?php echo $date_label; ?></p>
                    <div style="font-size: 1.5rem; font-weight: 700; color: <?php echo ($day['daily_count'] >= 5) ? '#ef4444' : 'var(--secondary)'; ?>;">
                        <?php echo $day['daily_count']; ?>
                    </div>
                    <p style="font-size: 0.65rem; color: var(--text-secondary); margin-top: 0.25rem;">Travels</p>
                </div>
            <?php 
                endwhile; 
            endif; ?>
        </div>
    </div>

    <div class="glass card" style="padding: 0; overflow: hidden; border: none;">
        <?php if ($history_result->num_rows === 0): ?>
            <div style="text-align: center; padding: 5rem 2rem; color: var(--text-secondary);">
                <i class="fas fa-history" style="font-size: 3rem; opacity: 0.2; margin-bottom: 1.5rem;"></i>
                <h3 style="color: white; margin-bottom: 0.5rem;">No Travel History</h3>
                <p>Your journey logs will appear here once you start traveling.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase;">
                            <th style="padding: 1.25rem 1.5rem;">Pass Code</th>
                            <th style="padding: 1.25rem 1.5rem;">District</th>
                            <th style="padding: 1.25rem 1.5rem;">Journey</th>
                            <th style="padding: 1.25rem 1.5rem;">Time</th>
                            <th style="padding: 1.25rem 1.5rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history_result->fetch_assoc()): 
                            $isExpired = time() > strtotime($row['valid_until']);
                        ?>
                            <tr style="border-bottom: 1px solid var(--border); transition: background 0.3s; cursor: default;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--primary);"><?php echo $row['pass_code']; ?></td>
                                <td style="padding: 1.25rem 1.5rem;"><?php echo $row['registration_city']; ?></td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div style="font-size: 0.85rem; color: #e2e8f0;">
                                        <span style="color: var(--secondary);"><?php echo htmlspecialchars($row['start_point'] ?: 'N/A'); ?></span>
                                        <i class="fas fa-arrow-right" style="margin: 0 0.5rem; opacity: 0.5; font-size: 0.75rem;"></i>
                                        <span style="color: #94a3b8;"><?php echo htmlspecialchars($row['end_point'] ?: 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <div><?php echo date('d M Y', strtotime($row['valid_from'])); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo date('h:i A', strtotime($row['valid_from'])); ?></div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem;">
                                    <span style="padding: 0.35rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; <?php echo $isExpired ? 'background: rgba(239, 68, 68, 0.1); color: #fca5a5;' : 'background: rgba(16, 185, 129, 0.1); color: var(--secondary);'; ?>">
                                        <?php echo $isExpired ? 'Expired' : 'Active'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
