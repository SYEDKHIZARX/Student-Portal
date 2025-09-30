<?php require_once __DIR__ . '/includes/ui_header.php'; ?>
<div class='card p-3 mb-3'>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Clear all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Go back to login
header('Location: index.php');
exit();

</div>
<?php require_once __DIR__ . '/includes/ui_footer.php'; ?>
