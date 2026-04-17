<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_page = 'terms.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Bus Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="animate-fade-in">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.5rem; color: white; margin-bottom: 0.5rem;">Terms & Conditions</h1>
        </div>

        <div class="card glass" style="max-width: 800px; padding: 2.5rem;">
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <section>
                    <h3 style="color: white; margin-bottom: 0.75rem;"><i class="fas fa-id-card-alt" style="color: var(--primary); margin-right: 0.5rem;"></i> 1. Smart Pass Subscription</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Access to the Smart Bus Portal is restricted to registered users with an active subscription plan. Users may opt for a **Monthly Subscription (₹1)** or an **Annual Subscription (₹2)**. All payments are final and non-refundable. Your subscription allows you to generate digital boarding passes within your registered District only.</p>
                </section>

                <section>
                    <h3 style="color: white; margin-bottom: 0.75rem;"><i class="fas fa-bus-alt" style="color: var(--primary); margin-right: 0.5rem;"></i> 2. Daily Travel Fair Usage</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">To ensure equitable access for all passengers, a **Fair Usage Policy (FUP)** is implemented. Each account is limited to a maximum of **5 successful boarding attempts per 24-hour period**. The daily counter resets automatically at midnight (00:00 IST). Remaining travels do not carry over to the next day.</p>
                </section>

                <section>
                    <h3 style="color: white; margin-bottom: 0.75rem;"><i class="fas fa-map-marked-alt" style="color: var(--primary); margin-right: 0.5rem;"></i> 3. Territorial Restrictions</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Users are specifically registered to their permanent **District** of residence. Digital boarding passes can only be authorized for buses operating within the same District. Attempting to board a bus in a different District via this portal will result in an "Access Denied" error.</p>
                </section>

                <section>
                    <h3 style="color: white; margin-bottom: 0.75rem;"><i class="fas fa-qrcode" style="color: var(--primary); margin-right: 0.5rem;"></i> 4. Boarding Protocols</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">Passes must be generated **before** entering the bus. A generated digital pass is valid for a single trip and includes a 2-hour inspection window. Passengers must present the digital confirmation or the downloaded PNG receipt to the conductor or authorized transport officials upon request.</p>
                </section>

                <section>
                    <h3 style="color: white; margin-bottom: 0.75rem;"><i class="fas fa-user-lock" style="color: var(--primary); margin-right: 0.5rem;"></i> 5. Administrative Rights</h3>
                    <p style="color: var(--text-secondary); line-height: 1.6;">The Transport Authority reserves the right to suspend or terminate accounts found engaging in fraudulent activities, such as sharing credentials or attempting to bypass the daily limit. Users are responsible for safeguarding their **Recovery Code** for account access.</p>
                </section>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <a href="dashboard.php" class="btn-primary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>
</div>

</body>
</html>
