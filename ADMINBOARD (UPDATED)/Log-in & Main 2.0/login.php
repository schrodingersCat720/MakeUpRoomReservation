<?php
// Start session early
session_start();

// For demo credentials (you said keep changes minimal). In production, move to DB + hashed passwords.
$valid_email = 'admin@plv.edu.ph';
$valid_password = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic server-side sanitization
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === $valid_email && $password === $valid_password) {
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user'] = $email;
        header('Location: main.php');
        exit();
    } else {
        // Redirect back with a generic error flag
        header('Location: index.php?error=1');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>