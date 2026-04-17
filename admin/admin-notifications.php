<?php
session_start();
include '../includes/session_check.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$current_page = 'admin-notifications';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $conn->real_escape_string($_POST['message']);
    
    // First, deactivate all previous true notifications (if we only want one active at a time)
    // Or just insert a new one. Let's deactivate old ones to keep the banner fresh.
    $conn->query("UPDATE notifications SET is_active = FALSE");
    
    $stmt = $conn->prepare("INSERT INTO notifications (message, is_active) VALUES (?, TRUE)");
    $stmt->bind_param("s", $message);
    
    if ($stmt->execute()) {
        $msg = "Notification Updated Successfully!";
        $type = "success";
    } else {
        $msg = "Error: " . $conn->error;
        $type = "error";
    }
}

// Fetch History
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Smart Bus Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php include 'sidebar-admin.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Site Notifications</h1>
        <p style="color: var(--text-secondary);">Update the marquee banner on the home page</p>
    </header>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Add Form -->
        <div class="glass card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Broadcast Message</h3>
            
            <?php if(isset($msg)): ?>
                <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: <?php echo $type=='success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $type=='success' ? 'var(--secondary)' : '#fecaca'; ?>;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem;">Message</label>
                    <textarea name="message" class="input-field" rows="4" placeholder="Enter important announcement..." required style="resize: none;"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-bullhorn"></i> Publish Notification
                </button>
            </form>
        </div>

        <!-- List -->
        <div class="glass card premium-table-container">
            <h3 style="padding: 1rem;">Recent Broadcasts</h3>
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="color: var(--text-secondary); font-size: 0.85rem;">
                            <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td>
                            <?php if($row['is_active']): ?>
                                <span style="background: rgba(16, 185, 129, 0.1); color: var(--secondary); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">Live</span>
                            <?php else: ?>
                                <span style="background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">Archived</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
