<?php
// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        // 'cookie_secure' => true, // enable if using HTTPS
        'use_strict_mode' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// --- CONFIG ---
// Path to your login page (relative to current script)
$loginPage = '/Log-in%20%26%20Main%202.0/index.php';

// Session key that marks a logged-in user
$sessionKey = 'user_id'; // <-- change to whatever your app sets when logging in (e.g. 'logged_in' or 'user')

// Optional: allow AJAX requests to receive 401 instead of redirect
$ajaxSend401 = true;

// --- PROTECTION CHECK ---
$loggedIn = !empty($_SESSION[$sessionKey]);

if (!$loggedIn) {
    // If request looks like an AJAX call, return 401 JSON/plain
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

    // Optionally support fetch() style check
    if (!$isAjax && !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $isAjax = true;
    }

    if ($isAjax && $ajaxSend401) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'unauthorized']);
        exit;
    }

    // Save intended URL so you can redirect back after login (optional)
    // Only save for GET requests to avoid exposing POST data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // You may want to restrict which hosts are allowed — here we just save path+query
        $current = $_SERVER['REQUEST_URI'];
        $_SESSION['after_login_redirect'] = $current;
    }

    // Perform redirect (use absolute or relative path as needed)
    // If headers already sent, use JS fallback.
    if (!headers_sent()) {
        header('Location: ' . $loginPage);
        exit;
    } else {
        // fallback if some output already printed (still better to include this file before any HTML)
        echo "<script>location.href = " . json_encode($loginPage) . ";</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url={$loginPage}'></noscript>";
        exit;
    }
}