<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_image'];
    
    // Config
    $upload_rel_path = "assets/uploads/";
    $target_dir = "../" . $upload_rel_path;
    
    // Ensure directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    $db_save_path = $upload_rel_path . $new_filename;
    
    // Validate image
    $check = getimagesize($file["tmp_name"]);
    if ($check !== false) {
        if ($file["size"] < 5000000) { // 5MB limit
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                if (move_uploaded_file($file["tmp_name"], $target_file)) {
                    // Update database
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->bind_param("si", $db_save_path, $user_id);
                    
                    if ($stmt->execute()) {
                        header("Location: " . $_SERVER['HTTP_REFERER']);
                        exit();
                    }
                }
            }
        }
    }
}

header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=upload_failed");
exit();
?>
