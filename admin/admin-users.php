<?php
session_start();
include '../includes/session_check.php';
include '../includes/db_connect.php';

// Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$current_page = 'admin-users'; // Used in sidebar for active state

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Prevent deleting admin
    $stmt_check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    
    if ($res_check->num_rows > 0 && $res_check->fetch_assoc()['role'] !== 'ADMIN') {
        $stmt_del = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt_del->bind_param("i", $delete_id);
        if ($stmt_del->execute()) {
             // Optional: Set a success message in session or query param
             header("Location: admin-users.php?msg=deleted");
             exit();
        }
    }
}

// Handle Search
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE role='USER'";
if ($search) {
    $sql .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR mobile LIKE '%$search%')";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Smart Bus Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php include 'sidebar-admin.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 2rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
        <div>
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">User Management</h1>
            <p style="color: var(--text-secondary);">View and manage registered users</p>
        </div>
        
        <form action="" method="GET" class="glass" style="display: flex; align-items: center; padding: 0.5rem 1rem; border-radius: 99px; width: 100%; max-width: 300px;">
            <i class="fas fa-search" style="color: var(--text-secondary);"></i>
            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" 
                   style="background: transparent; border: none; color: white; padding-left: 0.5rem; outline: none; width: 100%;">
        </form>
    </header>

    <div class="glass card premium-table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <div style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.8rem;">
                                <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                            </div>
                            <span style="font-weight: 500;"><?php echo htmlspecialchars($row['username']); ?></span>
                        </div>
                    </td>
                    <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['mobile']); ?></td>
                    <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><span style="background: rgba(16, 185, 129, 0.1); color: var(--secondary); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">Active</span></td>
                    <td>
                        <a href="admin-users.php?delete_id=<?php echo $row['id']; ?>" class="btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: rgba(239, 68, 68, 0.2); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);" onclick="return confirm('Are you sure you want to remove this user?');">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if($result->num_rows == 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        No users found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
