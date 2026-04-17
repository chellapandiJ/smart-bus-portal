<?php
session_start();
include '../includes/session_check.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$current_page = 'admin-bus-stops';

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt_del = $conn->prepare("DELETE FROM bus_stops WHERE id = ?");
    $stmt_del->bind_param("i", $delete_id);
    if ($stmt_del->execute()) {
         header("Location: admin-bus-stops.php?msg=deleted");
         exit();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $city = $_POST['city'];
    
    $stmt = $conn->prepare("INSERT INTO bus_stops (name, city) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $city);
    
    if ($stmt->execute()) {
        $msg = "Bus Stop Added Successfully!";
        $type = "success";
    } else {
        $msg = "Error: " . $conn->error;
        $type = "error";
    }
}

// Fetch Bus Stops
$result = $conn->query("SELECT * FROM bus_stops ORDER BY city ASC, name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bus Stops - Smart Bus Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php include 'sidebar-admin.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Bus Stops</h1>
        <p style="color: var(--text-secondary);">Manage locations for journey points</p>
    </header>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Add Form -->
        <div class="glass card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Add New Stop</h3>
            
            <?php if(isset($msg)): ?>
                <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: <?php echo $type=='success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $type=='success' ? 'var(--secondary)' : '#fecaca'; ?>;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label style="color:blue; font-size: 0.9rem;">Stop Name</label>
                    <input type="text" name="name" class="input-field" placeholder="e.g. Periyar Bus Stand" required>
                </div>
                <div class="input-group">
                    <label style="color: blue; font-size: 0.9rem;">City / District</label>
                    <select name="city" class="input-field" required>
                        <option value="Ariyalur">Ariyalur</option>
                        <option value="Chengalpattu">Chengalpattu</option>
                        <option value="Chennai">Chennai</option>
                        <option value="Coimbatore">Coimbatore</option>
                        <option value="Cuddalore">Cuddalore</option>
                        <option value="Dharmapuri">Dharmapuri</option>
                        <option value="Dindigul">Dindigul</option>
                        <option value="Erode">Erode</option>
                        <option value="Kallakurichi">Kallakurichi</option>
                        <option value="Kancheepuram">Kancheepuram</option>
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
                        <option value="Sivagangai">Sivagangai</option>
                        <option value="Tenkasi">Tenkasi</option>
                        <option value="Thanjavur">Thanjavur</option>
                        <option value="Theni">Theni</option>
                        <option value="Thiruvallur">Thiruvallur</option>
                        <option value="Thiruvarur">Thiruvarur</option>
                        <option value="Thoothukudi">Thoothukudi</option>
                        <option value="Tiruchirappalli">Tiruchirappalli</option>
                        <option value="Tirunelveli">Tirunelveli</option>
                        <option value="Tirupathur">Tirupathur</option>
                        <option value="Tiruppur">Tiruppur</option>
                        <option value="Tiruvannamalai">Tiruvannamalai</option>
                        <option value="Vellore">Vellore</option>
                        <option value="Viluppuram">Viluppuram</option>
                        <option value="Virudhunagar">Virudhunagar</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Add Bus Stop</button>
            </form>
        </div>

        <!-- List -->
        <div class="glass card premium-table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Stop Name</th>
                        <th>City / District</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600; color: white;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="color: #e2e8f0;"><?php echo htmlspecialchars($row['city']); ?></td>
                            <td>
                                 <a href="admin-bus-stops.php?delete_id=<?php echo $row['id']; ?>" class="btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: rgba(239, 68, 68, 0.2); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);" onclick="return confirm('Remove this stop?');">Remove</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No bus stops added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    select option {
        background-color: #1e293b;
        color: white;
    }
</style>
</body>
</html>
