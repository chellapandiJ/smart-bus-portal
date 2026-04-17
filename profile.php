<?php
session_start();
include 'db_connect.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$type = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $city = $_POST['city'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email']; 

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format.";
        $type = "error";
    } else {
        // Update User
        $stmt = $conn->prepare("UPDATE users SET city = ?, age = ?, gender = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sissi", $city, $age, $gender, $email, $user_id);
        
        if ($stmt->execute()) {
            $msg = "Profile updated successfully!";
            $type = "success";
        } else {
            $msg = "Error updating profile: " . $conn->error;
            $type = "error";
        }
    }
}

// Fetch Current User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Smart Bus Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in">

<?php 
$current_page = 'profile.php';
include 'sidebar.php'; 
?>

<div class="main-content" style="margin-left: 280px; padding: 2rem;">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">My Profile</h1>
        <p style="color: var(--text-secondary);">Manage your personal information</p>
    </header>

    <div class="glass card" style="max-width: 600px;">
        <?php if($msg): ?>
            <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: <?php echo $type=='success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $type=='success' ? 'var(--secondary)' : '#fecaca'; ?>; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas <?php echo $type=='success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Full Name</label>
                    <input type="text" class="input-field" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">Full Name cannot be changed</p>
                </div>
                
                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Email</label>
                    <input type="email" name="email" class="input-field" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>



                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">District</label>
                    <select name="city" class="input-field" required>
                        <option value="<?php echo htmlspecialchars($user['city']); ?>" selected hidden><?php echo htmlspecialchars($user['city']); ?></option>
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

                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Age</label>
                    <input type="number" name="age" class="input-field" value="<?php echo htmlspecialchars($user['age']); ?>" min="5" max="100">
                </div>

                <div class="input-group">
                    <label style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Gender</label>
                    <select name="gender" class="input-field">
                        <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if($user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem;">
                    <i class="fas fa-save" style="margin-right: 0.5rem;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Fix for select option visibility in dark mode */
    select option {
        background-color: #1e293b;
        color: white;
    }
</style>

</body>
</html>
