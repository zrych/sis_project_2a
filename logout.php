<?php
// ============================================================
//  logout.php — Destroy session and redirect to login
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// Preserve the flash message if any exists, clear everything else
$flash = $_SESSION['flash'] ?? null;

session_unset();
session_destroy();

// Start a fresh session to carry the flash message to login
session_start();
session_regenerate_id(true);

if ($flash) $_SESSION['flash'] = $flash;

$_SESSION['flash'] = [
    'type'    => 'info',
    'message' => 'You have been signed out successfully.'
];

header('Location: login.php');
exit;
