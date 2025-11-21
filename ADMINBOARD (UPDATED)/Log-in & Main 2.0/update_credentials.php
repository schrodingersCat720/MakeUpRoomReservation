<?php
session_start();
require 'connect.php';

// Assume user_id is stored in session after login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $newPassword = $_POST['newPassword'];
    $station = $_POST['station'];
    $position = $_POST['position'];
    $task = $_POST['task'];

    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password_hash=?, station=?, position=?, task=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $passwordHash, $station, $position, $task, $userId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Credentials updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update credentials."]);
    }
}
?>
