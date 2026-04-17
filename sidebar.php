<div class="sidebar glass" style="width: 280px; height: 100vh; position: fixed; left: 0; top: 0; padding: 2rem; border-radius: 0; border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; overflow-y: auto;">
    <div style="margin-bottom: 3rem; display: flex; align-items: center; gap: 1rem; padding: 0.5rem;">
        <?php 
        $logo_url = file_exists('assets/images/logo.svg') ? 'assets/images/logo.svg' : '../assets/images/logo.svg';
        ?>
        <div style="width: 45px; height: 45px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--glass-border);">
            <img src="<?php echo $logo_url; ?>" alt="Bus Logo" style="width: 25px; height: 25px;">
        </div>
        <h2 style="font-size: 1.5rem; margin-bottom: 0;">Bus Portal</h2>
    </div>

    <!-- User Profile Section -->
    <?php
    $stmt_prof = $conn->prepare("SELECT username, profile_image FROM users WHERE id = ?");
    $stmt_prof->bind_param("i", $_SESSION['user_id']);
    $stmt_prof->execute();
    $user_data = $stmt_prof->get_result()->fetch_assoc();
    $display_name = $user_data['username'];
    $profile_img = $user_data['profile_image'] ? $user_data['profile_image'] : "https://api.dicebear.com/7.x/avataaars/svg?seed=" . $display_name;
    ?>
    <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border); border-radius: 1.25rem; padding: 1.25rem; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
        <div style="position: relative; cursor: pointer;" onclick="document.getElementById('profileInput').click();" title="Change Profile Picture">
            <img src="<?php echo $profile_img; ?>" alt="Avatar" style="width: 50px; height: 50px; border-radius: 12px; background: #2d3748; padding: 2px; border: 2px solid var(--primary); object-fit: cover;">
            <div style="position: absolute; bottom: -2px; right: -2px; width: 14px; height: 14px; background: var(--secondary); border: 2px solid #1e293b; border-radius: 50%;"></div>
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.4); border-radius: 12px; opacity: 0; transition: 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                <i class="fas fa-camera" style="color: white; font-size: 0.8rem;"></i>
            </div>
        </div>
        <div style="overflow: hidden; flex-grow: 1;">
            <p style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem;">Welcome back,</p>
            <h3 style="font-size: 1.1rem; color: white; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo $display_name; ?></h3>
        </div>
    </div>

    <!-- Hidden Upload Form -->
    <form id="profileForm" action="handlers/upload_profile_handler.php" method="POST" enctype="multipart/form-data" style="display: none;">
        <input type="file" name="profile_image" id="profileInput" accept="image/*" onchange="document.getElementById('profileForm').submit();">
    </form>

    <nav style="display: flex; flex-direction: column; gap: 0.5rem; flex-grow: 1;">
        <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="buy-pass.php" class="nav-link <?php echo ($current_page == 'buy-pass.php') ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i>
            <span>Wallet</span>
        </a>
        <a href="check-pass.php" class="nav-link <?php echo ($current_page == 'check-pass.php') ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i>
            <span>Validate Pass</span>
        </a>
        <a href="pass-history.php" class="nav-link <?php echo ($current_page == 'pass-history.php') ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Review Journey</span>
        </a>
        <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-edit"></i>
            <span>My Profile</span>
        </a>
        <a href="terms.php" class="nav-link <?php echo ($current_page == 'terms.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-contract"></i>
            <span>Terms & Conditions</span>
        </a>
    </nav>
    
    <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
        <a href="logout.php" class="nav-link logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    font-weight: 500;
}

.nav-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    transform: translateX(4px);
}

.nav-link.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
}

.nav-link.active:hover {
    transform: none;
}

.nav-link.logout:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Ensure Font Awesome is loaded */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
</style>
