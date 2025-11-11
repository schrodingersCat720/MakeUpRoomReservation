<?php
session_start();

// Simple example — replace this with DB check later
$validEmail = 'admin@plv.edu.ph';
$validPassword = 'admin123';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === $validEmail && $password === $validPassword) {
    $_SESSION['user_id'] = 1; // session flag that user is logged in
    header('Location: main.php');
    exit;
} else {
    echo "<script>alert('Invalid email or password!'); window.location.href='index.php';</script>";
    exit;
}
?>
