<?php
require_once '../config/config.php';
require_once '../config/auth.php';


if (isLoggedIn()) {
    $user = getCurrentUser();
    error_log("User logout: " . $user['username'] . " (ID: " . $user['id'] . ") from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}


logoutUser();

$message = $_GET['message'] ?? '';
$redirect_url = 'index.php';

if ($message) {
    $redirect_url .= '?message=' . urlencode($message);
}


header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login page
header("Location: $redirect_url");
exit;
?>