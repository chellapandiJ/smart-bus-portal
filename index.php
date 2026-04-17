<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Bus Portal - Premium Transit Experience</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <style>
        .hero {
            min-height: 100vh;
            background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.8)), url('assets/images/hero_bg.png') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 6rem 2rem 2rem 2rem;
        }

        .hero-content {
            max-width: 900px;
            z-index: 10;
            margin-top: 2rem;
        }

        .hero h1 {
            font-size: clamp(3rem, 8vw, 5rem);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: clamp(1.1rem, 3vw, 1.5rem);
            color: #cbd5e1;
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-large {
            padding: 1.25rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 1rem;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }

        .floating-bus {
            position: absolute;
            bottom: 5%;
            right: 5%;
            font-size: 10rem;
            color: rgba(255, 255, 255, 0.03);
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 5rem;
            width: 100%;
            max-width: 1200px;
        }

        .feature-card {
            padding: 2rem;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .navbar {
            padding: 1rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 1.5rem;
            left: 0;
            width: 100%;
            z-index: 100;
            transition: top 0.3s ease;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .nav-logo img {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-logo:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="animate-fade-in">

<?php
// Fetch Active Notification
include 'db_connect.php';
$notif_msg = "";
$res = $conn->query("SELECT message FROM notifications WHERE is_active=TRUE ORDER BY id DESC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $notif_msg = $row['message'];
}
?>
    
    <?php if($notif_msg): ?>
    <div style="background: linear-gradient(90deg, var(--primary), var(--secondary)); color: white; padding: 0.5rem 0; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
        <marquee behavior="scroll" direction="left" scrollamount="6" style="font-weight: 500; font-size: 0.95rem;">
            <i class="fas fa-bell" style="margin-right: 10px;"></i> <?php echo htmlspecialchars($notif_msg); ?>
        </marquee>
    </div>
    <?php endif; ?>

    <nav class="navbar" <?php if($notif_msg) echo 'style="top: 55px;"'; ?>>
        <a href="index.php" class="nav-logo">
            <img src="assets/images/logo.svg" alt="Logo">
            <span style="font-size: 1.6rem; font-weight: 700; letter-spacing: -0.01em;">Smart Bus Portal</span>
        </a>
    </nav>

    <section class="hero">
        <i class="fas fa-bus floating-bus"></i>
        
        <div class="hero-content">
            <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
                <div style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                    <img src="assets/images/logo.svg" alt="Bus Icon" style="width: 60px; height: 60px;">
                </div>
            </div>
            
            <h1 class="animate-fade-in">Your Gateway to Smart Travel</h1>
            <p class="animate-fade-in" style="animation-delay: 0.1s;">Experience a modern, digital-first approach to public transportation. Secure, fast, and completely paperless.</p>
            
            <div class="cta-group animate-fade-in" style="animation-delay: 0.2s;">
                <a href="login.php" class="btn-primary btn-large">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Log In to Portal</span>
                </a>
                <a href="register.php" class="btn-primary btn-large btn-outline">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Account</span>
                </a>
            </div>

            <div class="features-grid animate-fade-in" style="animation-delay: 0.3s;">
                <div class="feature-card">
                    <i class="fas fa-qrcode"></i>
                    <h3>Digital Boarding</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Board instantly with digital validation. No more physical tickets.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-wallet"></i>
                    <h3>Smart Wallet</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Easy recharges with Secure Gateway. Manage your balance on the go.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure & Verified</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Industry-standard security for your personal data and travel logs.</p>
                </div>
            </div>
        </div>
    </section>

    <footer style="padding: 3rem; text-align: center; color: var(--text-secondary); border-top: 1px solid var(--glass-border); background: var(--background);">
        <p>&copy; <?php echo date('Y'); ?> Smart Bus Portal. Modernizing Urban Transit.</p>
    </footer>

</body>
</html>
