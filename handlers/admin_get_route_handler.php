<?php
session_start();
include '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $code = strtoupper(trim($data['code']));

    // Admins can see any route
    $stmt = $conn->prepare("SELECT start_point, end_point, city FROM bus_codes WHERE code = ? AND is_active = 1");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo json_encode([
            'success' => true,
            'city' => $row['city'],
            'start_point' => $row['start_point'],
            'end_point' => $row['end_point']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Bus code not found.']);
    }
}
?>
