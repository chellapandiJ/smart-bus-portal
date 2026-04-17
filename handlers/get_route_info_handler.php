<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $code = strtoupper(trim($data['code']));
    $user_id = $_SESSION['user_id'];

    // Fetch user city
    $stmt_user = $conn->prepare("SELECT city FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_city = $stmt_user->get_result()->fetch_assoc()['city'];

    // Validate code and city
    $stmt = $conn->prepare("SELECT start_point, end_point, stops FROM bus_codes WHERE code = ? AND city = ? AND is_active = 1");
    $stmt->bind_param("ss", $code, $user_city);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $bus_city = $user_city; 
        $start_point = $row['start_point'];
        $end_point = $row['end_point'];

        // Fetch all stops for this city from bus_stops table
        $stops_stmt = $conn->prepare("SELECT name FROM bus_stops WHERE city = ? ORDER BY name ASC");
        $stops_stmt->bind_param("s", $bus_city);
        $stops_stmt->execute();
        $stops_res = $stops_stmt->get_result();
        
        $all_stops = [];
        while($stop_row = $stops_res->fetch_assoc()) {
            $all_stops[] = $stop_row['name'];
        }

        // Add start and end points to the stops list if they are not already there
        if (!empty($start_point) && !in_array($start_point, $all_stops)) $all_stops[] = $start_point;
        if (!empty($end_point) && !in_array($end_point, $all_stops)) $all_stops[] = $end_point;
        sort($all_stops);

        echo json_encode([
            'success' => true,
            'city' => $bus_city,
            'start_point' => $start_point,
            'end_point' => $end_point,
            'stops' => $all_stops
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Access Denied! Either the code is invalid or it belongs to another district (Your District: $user_city)."
        ]);
    }
}
?>
