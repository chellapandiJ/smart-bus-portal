 <?php 
session_start();
include 'db_connect.php';

// Access Control
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = 'buy-pass.php'; 

// Fetch current balance and plan dates from database
$stmt = $conn->prepare("SELECT balance, plan_start_date, expiry_date FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_res = $stmt->get_result();

if ($wallet_res->num_rows > 0) {
    $wallet_data = $wallet_res->fetch_assoc();
    $current_balance = $wallet_data['balance'];
    $plan_start_date = $wallet_data['plan_start_date'];
    $expiry_date = $wallet_data['expiry_date'];
} else {
    $conn->query("INSERT INTO wallet (user_id, balance) VALUES ($user_id, 0.00)");
    $current_balance = 0.00;
    $plan_start_date = null;
    $expiry_date = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Pass</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="animate-fade-in">

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem; gap: 1.5rem;">
        <div>
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Digital Wallet</h1>
            <p style="color: var(--text-secondary);">Manage your balance and purchase smart passes</p>
        </div>
        
        <div class="glass" style="padding: 1rem 2rem; display: flex; flex-direction: column; gap: 0.5rem; min-width: 250px;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                    <i class="fas fa-wallet" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0rem;">Available Balance</p>
                    <h3 style="font-size: 1.5rem; margin: 0;">₹<span id="walletBalance"><?php echo number_format($current_balance, 2); ?></span></h3>
                </div>
            </div>
            <?php if ($plan_start_date && $expiry_date): ?>
                <div style="border-top: 1px solid var(--border); padding-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary);">
                    <div style="display: flex; justify-content: space-between;">
                        <span><i class="fas fa-calendar-check"></i> From:</span>
                        <span style="color: white; font-weight: 600;"><?php echo date('d M Y', strtotime($plan_start_date)); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                        <span><i class="fas fa-clock"></i> Until:</span>
                        <span style="color: var(--accent); font-weight: 600;"><?php echo date('d M Y', strtotime($expiry_date)); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div style="border-top: 1px solid var(--border); padding-top: 0.5rem; font-size: 0.75rem; color: #fca5a5;">
                    <i class="fas fa-times-circle"></i> No active plan. Please recharge.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SELECTION SECTION -->
    <div id="selectSection" class="animate-fade-in">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-ticket-alt" style="color: var(--primary);"></i>
            Select a Travel Plan
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <div class="glass card pass-option" onclick="selectPass('MONTHLY', 1)" id="monthly" style="cursor: pointer; position: relative; overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div style="width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i class="fas fa-calendar-alt" style="font-size: 1.5rem;"></i>
                    </div>
                    <span style="background: rgba(99, 102, 241, 0.15); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Standard</span>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Monthly Pass</h3>
                <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 0.95rem;">Perfect for regular daily commuters. Valid for 30 days.</p>
                <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                    <span style="font-size: 2rem; font-weight: 700;">₹1</span>
                    <span style="color: var(--text-secondary);">/month</span>
                </div>
            </div>

            <div class="glass card pass-option" onclick="selectPass('YEARLY', 2)" id="yearly" style="cursor: pointer; position: relative; overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent);">
                        <i class="fas fa-crown" style="font-size: 1.5rem;"></i>
                    </div>
                    <span style="background: rgba(245, 158, 11, 0.15); color: var(--accent); padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">Premium</span>
                </div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Yearly Pass</h3>
                <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 0.95rem;">Ultimate savings for power travelers. Valid for 365 days.</p>
                <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                    <span style="font-size: 2rem; font-weight: 700;">₹2</span>
                    <span style="color: var(--text-secondary);">/year</span>
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem; text-align: center;">
            <button onclick="payNow()" id="payBtn" class="btn-primary" disabled style="padding: 1rem 3rem; font-size: 1.125rem; border-radius: 1rem;">
                <i class="fas fa-arrow-right"></i>
                <span>Pay & Activate Plan</span>
            </button>
            <p style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-secondary);">
                Secure payments powered by Razorpay
            </p>
        </div>
    </div>

    <!-- SUCCESS SECTION -->
    <div id="successSection" class="hidden animate-fade-in" style="max-width: 600px; margin: 0 auto; text-align: center;">
        <div class="glass-card">
            <div style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary); margin: 0 auto 1.5rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
            </div>
            <h2 style="font-size: 2rem; margin-bottom: 0.5rem;">Payment Successful!</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2.5rem;">Your digital bus pass has been activated and the amount has been added to your wallet.</p>

            <div style="background: linear-gradient(135deg, var(--primary), #818cf8); border-radius: 1.5rem; padding: 2rem; text-align: left; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);">
                <i class="fas fa-bus-alt" style="position: absolute; right: -2rem; bottom: -2rem; font-size: 12rem; color: rgba(255, 255, 255, 0.05);"></i>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px); padding: 0.5rem 1rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 700; color: white;">
                        SMART PASS • ACTIVE
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <p style="font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.25rem;">Pass Type</p>
                    <h3 id="passTypeText" style="font-size: 1.5rem; color: white; margin: 0;"></h3>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 0.25rem;">HOLDER</p>
                        <p style="font-weight: 600; color: white;"><?php echo $_SESSION['username']; ?></p>
                    </div>
                    <div>
                        <p style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 0.25rem;">VALID UNTIL</p>
                        <p id="validDate" style="font-weight: 600; color: white;"></p>
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" class="btn-primary" style="margin-top: 2.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border); color: white;">
                <i class="fas fa-shopping-cart"></i>
                <span>Purchase Another Pass</span>
            </button>
        </div>
    </div>
</div>

<style>
.pass-option {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.pass-option:hover {
    border-color: var(--primary);
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.07);
}
.pass-option.active {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
    box-shadow: 0 0 0 2px var(--primary);
}
.hidden { display: none !important; }
</style>

<script>
let selectedType = "";
let selectedAmount = 0;

function selectPass(type, amount){
    selectedType = type;
    selectedAmount = amount;

    document.getElementById("monthly").classList.remove("active");
    document.getElementById("yearly").classList.remove("active");

    document.getElementById(type === "MONTHLY" ? "monthly" : "yearly").classList.add("active");

    document.getElementById("payBtn").disabled = false;
    document.getElementById("payBtn").innerText = `Pay ₹${amount} Now`;
}

async function updateWalletInDB(amount, payment_id, order_id, signature, type) {
    try {
        const response = await fetch('handlers/update_wallet_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                amount: amount,
                payment_id: payment_id,
                order_id: order_id,
                signature: signature,
                type: type,
                payment_method: 'ONLINE'
            })
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById("walletBalance").innerText = parseFloat(data.new_balance).toFixed(2);
            
            // Show Success
            document.getElementById("selectSection").classList.add("hidden");
            document.getElementById("successSection").classList.remove("hidden");
            document.getElementById("passTypeText").innerText = selectedType + " ACCESS";
            
            let d = new Date();
            d.setDate(d.getDate() + (selectedType === 'MONTHLY' ? 30 : 365));
            document.getElementById("validDate").innerText = d.toLocaleDateString();
            
            return true;
        } else {
            console.error("Server error:", data.message);
        }
    } catch (e) { console.error("Wallet update failed", e); }
    return false;
}

/* ========== RAZORPAY FUNCTION ========== */
async function payNow(){
    if (selectedAmount === 0) {
        alert("Please select a plan first.");
        return;
    }
    
    // 1. Create Order on Server (Best Practice)
    let orderId = null;
    try {
        const response = await fetch('handlers/create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                amount: selectedAmount,
                type: selectedType
            })
        });
        
        const data = await response.json();
        
        if(data.order_id && data.order_id !== 'TEST_MODE'){
            orderId = data.order_id;
        } else {
            console.warn("Using Client-side Razorpay handling (Test Mode/Fallback):", data);
        }
    } catch (e) {
        console.error("Order creation failed, falling back to client-side", e);
    }

    var options = {
        "key": "rzp_test_SKjHUKHBA1dJB7", // key_id from dashboard
        "amount": selectedAmount * 100, // Amount in paise
        "currency": "INR",
        "name": "Smart Bus Portal",
        "description": selectedType + " Pass Purchase",
        "image": "https://cdn-icons-png.flaticon.com/512/61/61088.png",
        // Only include order_id if we have a real one
        ...(orderId ? { "order_id": orderId } : {}),
        "handler": async function (response){
            const synced = await updateWalletInDB(
                selectedAmount, 
                response.razorpay_payment_id, 
                response.razorpay_order_id, 
                response.razorpay_signature,
                selectedType
            );
            
            if (!synced) {
                alert("Payment successful but wallet sync failed. Please contact support.");
            }
        },
        "prefill": {
            "name": "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>",
            "email": "user@busportal.com",
            "contact": "9999999999"
        },
        "theme": {
            "color": "#6C5CE7"
        },
        "modal": {
            "ondismiss": function(){
                console.log('Checkout form closed');
            }
        }
    };

    var rzp = new Razorpay(options);
    
    rzp.on('payment.failed', function (response){
        alert("Payment Failed: " + response.error.description);
    });

    rzp.open();
}
</script>

</body>
</html>
