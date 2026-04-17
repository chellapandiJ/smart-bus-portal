<?php
session_start();
include '../includes/session_check.php';
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$current_page = 'admin-bus-codes';

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt_del = $conn->prepare("DELETE FROM bus_codes WHERE id = ?");
    $stmt_del->bind_param("i", $delete_id);
    if ($stmt_del->execute()) {
         header("Location: admin-bus-codes.php?msg=deleted");
         exit();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $city = $_POST['city'];
    $start_point = $_POST['start_point'];
    $end_point = $_POST['end_point'];
    $stops = isset($_POST['stops']) ? implode(', ', $_POST['stops']) : '';
    
    // Updated query to include start_point, end_point, and stops
    $stmt = $conn->prepare("INSERT INTO bus_codes (code, city, start_point, end_point, stops) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $code, $city, $start_point, $end_point, $stops);
    
    if ($stmt->execute()) {
        $msg = "Bus Route Added Successfully!";
        $type = "success";
    } else {
        $msg = "Error: " . $conn->error;
        $type = "error";
    }
}

// Fetch Bus Codes
$result = $conn->query("SELECT * FROM bus_codes ORDER BY created_at DESC");

// Fetch All Bus Stops for selection
$stops_query = $conn->query("SELECT * FROM bus_stops ORDER BY city ASC, name ASC");
$stops_by_city = [];
while($stop = $stops_query->fetch_assoc()) {
    $stops_by_city[$stop['city']][] = $stop['name'];
}
$stops_json = json_encode($stops_by_city);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bus Routes - Smart Bus Portal</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        select option {
            background-color: #1e293b;
            color: white;
        }
        .stop-badge {
            display: inline-block;
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.7rem;
            margin: 0.1rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .stops-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            padding: 0.5rem;
            border-radius: 8px;
            background: rgba(255,255,255,0.02);
        }
        .stop-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem;
            cursor: pointer;
            font-size: 0.85rem;
            color: #cbd5e1;
        }
        .stop-checkbox:hover {
            background: rgba(255,255,255,0.05);
        }
    </style>
</head>
<body class="animate-fade-in">

<?php include 'sidebar-admin.php'; ?>

<div class="main-content">
    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Bus Routes</h1>
        <p style="color: var(--text-secondary);">Manage routes and intermediate stops</p>
    </header>

    <!-- Route Analysis Tool -->
    <div class="glass card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-search" style="color: var(--primary);"></i> Route Analysis Tool
        </h3>
        <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="input-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label style="font-size: 0.75rem; color: var(--text-secondary);">Enter Bus Code</label>
                <input type="text" id="admin_bus_code" class="input-field" placeholder="e.g. KGI101" style="text-transform: uppercase;">
            </div>
            <button type="button" onclick="analyzeRoute()" class="btn-primary" style="padding: 0.75rem 1.5rem;">Verify Route</button>
            
            <div id="admin-analysis-result" style="flex: 2; min-width: 300px; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 0.75rem; border: 1px solid var(--border); display: none; align-items: center; gap: 1rem;">
                <div style="width: 35px; height: 35px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                    <i class="fas fa-bus"></i>
                </div>
                <div>
                    <p style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; margin: 0;">Standard Route</p>
                    <p id="analysis-text" style="font-weight: 600; font-size: 1rem; margin: 0; color: white;"></p>
                </div>
            </div>
        </div>
        <p id="analysis-error" style="color: #fca5a5; font-size: 0.8rem; margin-top: 0.75rem; display: none;"></p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Add Form -->
        <div class="glass card" style="height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">Add New Route</h3>
            
            <?php if(isset($msg)): ?>
                <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: <?php echo $type=='success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $type=='success' ? 'var(--secondary)' : '#fecaca'; ?>;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="routeForm">
                <div class="input-group">
                    <label style="color:blue; font-size: 0.9rem;">Bus Code (Unique)</label>
                    <input type="text" name="code" class="input-field" placeholder="e.g. MDU505" required>
                </div>
                
                <div class="input-group">
                    <label style="color: blue; font-size: 0.9rem;">City / District</label>
                    <select name="city" id="citySelect" class="input-field" required onchange="updateStops()">
                        <option value="">Select City / District</option>
                        <?php 
                        $cities = array_keys($stops_by_city);
                        sort($cities);
                        foreach($cities as $c) {
                            echo '<option value="' . htmlspecialchars($c) . '">' . htmlspecialchars($c) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="input-group">
                    <label style="color: blue; font-size: 0.9rem;">Starting Point</label>
                    <select name="start_point" id="startSelect" class="input-field" required disabled>
                        <option value="">Select City First</option>
                    </select>
                </div>

                <div class="input-group">
                    <label style="color: blue; font-size: 0.9rem;">Ending Point</label>
                    <select name="end_point" id="endSelect" class="input-field" required disabled>
                        <option value="">Select City First</option>
                    </select>
                </div>

                <div class="input-group">
                    <label style="color: blue; font-size: 0.9rem;">Intermediate Stops</label>
                    <div id="stopsContainer" class="stops-container">
                        <p style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">Select city to see available stops</p>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Add Route</button>
            </form>
        </div>

        <!-- List -->
        <div class="glass card premium-table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>City</th>
                        <th>Journey</th>
                        <th>Stops</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600; color: white;"><?php echo htmlspecialchars($row['code']); ?></td>
                            <td style="color: #e2e8f0;"><?php echo htmlspecialchars($row['city']); ?></td>
                            <td style="color: #e2e8f0; font-size: 0.85rem;">
                                <span style="color: var(--secondary); font-weight: 500; font-size: 0.95rem;"><?php echo htmlspecialchars($row['start_point']); ?></span>
                                <i class="fas fa-long-arrow-alt-right" style="margin: 0 0.4rem; opacity: 0.5;"></i>
                                <span style="color: #94a3b8; font-size: 0.95rem;"><?php echo htmlspecialchars($row['end_point']); ?></span>
                            </td>
                            <td style="color: #94a3b8; font-size: 0.8rem; max-width: 200px;">
                                <?php 
                                if ($row['stops']) {
                                    $stops_arr = explode(',', $row['stops']);
                                    foreach($stops_arr as $s) {
                                        echo '<span class="stop-badge">' . trim(htmlspecialchars($s)) . '</span>';
                                    }
                                } else {
                                    echo '<span style="opacity: 0.5;">No intermediate stops</span>';
                                }
                                ?>
                            </td>
                            <td>
                                 <a href="admin-bus-codes.php?delete_id=<?php echo $row['id']; ?>" class="btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: rgba(239, 68, 68, 0.2); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4);" onclick="return confirm('Remove this route?');">Remove</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 2rem;">No routes added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const stopsData = <?php echo $stops_json; ?>;

    function updateStops() {
        const city = document.getElementById('citySelect').value;
        const startSelect = document.getElementById('startSelect');
        const endSelect = document.getElementById('endSelect');
        const stopsContainer = document.getElementById('stopsContainer');

        // Clear existing
        startSelect.innerHTML = '<option value="">Select Start Point</option>';
        endSelect.innerHTML = '<option value="">Select End Point</option>';
        stopsContainer.innerHTML = '';

        if (city && stopsData[city]) {
            startSelect.disabled = false;
            endSelect.disabled = false;
            
            stopsData[city].forEach(stop => {
                // Add to start/end selects
                const opt1 = document.createElement('option');
                opt1.value = stop;
                opt1.textContent = stop;
                startSelect.appendChild(opt1);

                const opt2 = document.createElement('option');
                opt2.value = stop;
                opt2.textContent = stop;
                endSelect.appendChild(opt2);

                // Add to multi-select container
                const div = document.createElement('label');
                div.className = 'stop-checkbox';
                div.innerHTML = `<input type="checkbox" name="stops[]" value="${stop}"> <span>${stop}</span>`;
                stopsContainer.appendChild(div);
            });

            if (stopsData[city].length === 0) {
                 stopsContainer.innerHTML = '<p style="padding: 1rem; text-align: center; color: #fca5a5; font-size: 0.8rem;">No stops found for this city. <a href="admin-bus-stops.php" style="color: var(--primary);">Add some first.</a></p>';
            }
        } else {
            startSelect.disabled = true;
            endSelect.disabled = true;
            stopsContainer.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.8rem;">Select city to see available stops</p>';
        }
    }

    async function analyzeRoute() {
        const code = document.getElementById('admin_bus_code').value.trim();
        const resultDiv = document.getElementById('admin-analysis-result');
        const analysisText = document.getElementById('analysis-text');
        const analysisError = document.getElementById('analysis-error');
        
        if (!code) return;

        analysisError.style.display = 'none';
        resultDiv.style.display = 'none';

        try {
            const response = await fetch('../handlers/admin_get_route_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code })
            });
            const data = await response.json();

            if (data.success) {
                analysisText.innerText = `${data.start_point} ➔ ${data.end_point}`;
                resultDiv.style.display = 'flex';
            } else {
                analysisError.innerText = data.message;
                analysisError.style.display = 'block';
            }
        } catch (err) {
            analysisError.innerText = 'Error connecting to server.';
            analysisError.style.display = 'block';
        }
    }
</script>
</body>
</html>
