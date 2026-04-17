<?php
if (!isset($current_page)) {
    $current_page = '';
}
?>
<div class="sidebar glass" style="position: fixed; left: 0; top: 0; height: 100vh; width: 260px; padding: 2rem; border-radius: 0; border-left: none; border-top: none; border-bottom: none; z-index: 100;">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 3rem; padding-left: 0.5rem;">
        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.25rem;">
            A
        </div>
        <div>
            <h3 style="font-size: 1.25rem; margin: 0; color: white;">Admin Panel</h3>
            <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">Smart Bus Portal</p>
        </div>
    </div>

    <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
        <a href="admin-dashboard.php" class="nav-item <?php echo $current_page === 'admin-dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="admin-users.php" class="nav-item <?php echo $current_page === 'admin-users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Manage Users</span>
        </a>
        <a href="admin-bus-codes.php" class="nav-item <?php echo $current_page === 'admin-bus-codes' ? 'active' : ''; ?>">
            <i class="fas fa-route"></i>
            <span>Bus Routes</span>
        </a>
        <a href="admin-bus-stops.php" class="nav-item <?php echo $current_page === 'admin-bus-stops' ? 'active' : ''; ?>">
            <i class="fas fa-map-marker-alt"></i>
            <span>Bus Stops</span>
        </a>
        <a href="admin-notifications.php" class="nav-item <?php echo $current_page === 'admin-notifications' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i>
            <span>Notifications</span>
        </a>
    </nav>

    <div style="margin-top: auto; padding-top: 2rem; border-top: 1px solid var(--border);">
        <a href="../logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 0.75rem;
    transition: all 0.2s;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
}

.nav-item.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.nav-item i {
    width: 20px;
    text-align: center;
}
</style>
