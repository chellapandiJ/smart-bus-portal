 <?php
session_start();
include '../includes/session_check.php';
include '../includes/db_connect.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$current_page = 'admin-dashboard';

// Fetch User Count
$total_users = 0;
$res = $conn->query("SELECT count(*) as count FROM users WHERE role='USER'");
if ($res) $total_users = $res->fetch_assoc()['count'];

// Fetch Active Passes
$active_passes = 0;
$res = $conn->query("SELECT count(*) as count FROM wallet WHERE expiry_date > NOW()");
if ($res) $active_passes = $res->fetch_assoc()['count'];

// Fetch Total Revenue
$total_revenue = 0.00;
$res = $conn->query("SELECT SUM(amount) as revenue FROM transactions WHERE status='SUCCESS'");
if ($res && $row = $res->fetch_assoc()) $total_revenue = $row['revenue'] ?? 0;

// Fetch Recent Signups for Graph (Last 7 Days)
$graph_labels = [];
$graph_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $graph_labels[] = date('d M', strtotime($date));
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ? AND role='USER'");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $graph_data[] = $stmt->get_result()->fetch_assoc()['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Bus Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="animate-fade-in">

<?php include 'sidebar-admin.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 3rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Admin Dashboard</h1>
        <p style="color: var(--text-secondary);">Overview of system performance and user activity</p>
    </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <!-- Card 1 -->
        <div class="glass card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Total Users</p>
                    <h3 style="font-size: 2rem;"><?php echo $total_users; ?></h3>
                </div>
                <div style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="glass card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Active Passes</p>
                    <h3 style="font-size: 2rem;"><?php echo $active_passes; ?></h3>
                </div>
                <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                    <i class="fas fa-ticket-alt" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="glass card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Total Revenue</p>
                    <h3 style="font-size: 2rem;">₹<?php echo number_format($total_revenue); ?></h3>
                </div>
                <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                    <i class="fas fa-chart-line" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Graph -->
    <div class="glass card" style="padding: 2rem;">
        <h3 style="margin-bottom: 1.5rem;">User Registration Analytics (Last 7 Days)</h3>
        <canvas id="userChart" style="width: 100%; height: 400px;"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('userChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($graph_labels); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode($graph_data); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: { color: '#94a3b8' }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: { stepSize: 1, color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8' }
            }
        }
    }
});
</script>

</body>
</html>
