 <?php
session_start();
include 'db_connect.php';
$error = "";

// Check for pre-fill credentials from registration
$pre_user = $_SESSION['reg_user'] ?? "";
$pre_pass = $_SESSION['reg_pass'] ?? "";
unset($_SESSION['reg_user'], $_SESSION['reg_pass']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == "ADMIN") {
                header("Location: admin/admin-dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Bus Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="animate-fade-in auth-page" style="background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('bus_bg.png') center/cover fixed;">

<div class="portal-heading" style="text-align: center; margin-bottom: 0.5rem;">
    <a href="index.php" style="text-decoration: none;">
        <h1 style="font-size: 2rem; color: #fff; text-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-bottom: 0.1rem;">Smart Bus Portal</h1>
    </a>
    <p style="font-size: 0.9rem; color: var(--text-secondary);">Modern • Secure • Efficient</p>
</div>

<div class="auth-card glass-card animate-fade-in" style="margin: 0 auto; max-width: 360px; padding: 2rem;">
    <div style="margin-bottom: 1.5rem;">
        <div style="width: 70px; height: 70px; background: var(--primary); border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);">
            <i class="fas fa-bus-alt" style="font-size: 2.2rem; color: white;"></i>
        </div>
        <h2 style="font-size: 1.5rem; color: white; margin-bottom: 0.3rem;">Welcome Back</h2>
        <p style="font-size: 0.9rem; color: var(--text-secondary);">Enter your credentials to continue</p>
    </div>

    <?php if($error): ?>
        <div class="error" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fecaca; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['registered'])): ?>
        <div class="success-msg" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #a7f3d0; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
            <span>Account created successfully! Please login.</span>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="input-group" style="text-align: left;">
            <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem; margin-left: 0.5rem;">Username</label>
            <div style="position: relative;">
                <i class="fas fa-user" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-secondary);"></i>
                <input type="text" name="username" class="input-field" placeholder="username" required style="padding-left: 2.8rem;" value="<?php echo htmlspecialchars($pre_user); ?>">
            </div>
        </div>

        <div class="input-group" style="text-align: left;">
            <label style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem; margin-left: 0.5rem;">Password</label>
            <div style="position: relative;">
                <i class="fas fa-lock" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-secondary);"></i>
                <input type="password" name="password" class="input-field" placeholder="••••••••" required style="padding-left: 2.8rem;" value="<?php echo htmlspecialchars($pre_pass); ?>">
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
            <span>Sign In</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
        <p style="color: var(--text-secondary); font-size: 0.95rem;">
            New traveler? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Create Account</a>
        </p>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (this.checkValidity()) {
        btn.classList.add('loading');
    }
});
</script>

</body>
</html>
```
