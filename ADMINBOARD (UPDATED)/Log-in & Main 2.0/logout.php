<?php
// Start session to be able to destroy it and clear cookie
session_start();

// Unset all session variables
$_SESSION = [];

// If using session cookies, clear the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Delete cookie by setting expiration in the past
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>