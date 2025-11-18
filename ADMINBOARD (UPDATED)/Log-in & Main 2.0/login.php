<?php
session_start();

// Database Connection Configuration
$host = 'localhost';
$db   = 'admindb';  
$user = 'root';    
$pass = '';         
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Handle connection error gracefully
    error_log("DB Connection Error: " . $e->getMessage());
    echo "<script>alert('A server error occurred. Please try again later.'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get Input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation to prevent unnecessary query
    if (empty($email) || empty($password)) {
        echo "<script>alert('Please enter both email and password.'); window.location.href='index.php';</script>";
        exit();
    }

    // Prepare and Execute Query to fetch user data and HASH
    $stmt = $pdo->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $login_success = false;
    
    // Check if user was found AND verify the password
    if ($user) {
        // Use password_verify() to compare the plain text password against the stored hash
        if (password_verify($password, $user['password_hash'])) {
            $login_success = true;
            
            // Success: Set session variables and redirect
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $email;
            
            header('Location: main.php');
            exit();
        }
    }

    // Failure: If $user is false OR password_verify failed
    if (!$login_success) {
        // Use a generic error message for security (prevents leaking if email exists)
        echo "<script>alert('Invalid email or password!'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    // If accessed via GET, redirect to login page
    header('Location: index.php');
    exit();
}
?>