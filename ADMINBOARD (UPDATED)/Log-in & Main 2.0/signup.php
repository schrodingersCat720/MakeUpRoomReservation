// Example structure for signup.php
<?php
// 1. Database Connection 
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
    // Better to log error and show generic message to user for security
    die("A server error occurred. Please try again later.");
}

// 2. Data Retrieval and Validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic server-side validation
    if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Handle error: invalid input
        header("Location: index.php?error=invalid_input");
        exit();
    }

    // 3. Password Hashing (Securely store password)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // 4. Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        // Handle error: email already exists
        header("Location: index.php?error=email_exists");
        exit();
    }

    // 5. Insert new user
    $sql = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$email, $hashed_password])) {
        // Success: Redirect back to the login form with a success message
        header("Location: index.php?success=registered");
        exit();
    } else {
        // Handle error: database insertion failed
        header("Location: index.php?error=db_fail");
        exit();
    }
}
?>