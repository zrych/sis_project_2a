<?php
// ============================================================
//  setup.php — One-time Admin Account Setup
//  1. Visit this page ONCE in your browser.
//  2. It will create the default admin user securely.
//  3. DELETE this file immediately after running it.
// ============================================================
require_once 'db.php';

$message = '';
$success = false;

// Check if users table already has entries
$check = mysqli_query($conn, 'SELECT COUNT(*) AS n FROM users');
$count = (int)(mysqli_fetch_assoc($check)['n'] ?? 0);

if ($count > 0) {
    $message = 'Setup has already been completed. An admin account already exists. Please delete this file.';
} else {
    // Create default admin
    $username  = 'admin';
    $email     = 'admin@school.edu';
    $password  = 'Admin@1234';          // ← Change this after first login
    $full_name = 'System Administrator';
    $role      = 'admin';
    $hash      = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn,
        'INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'sssss', $username, $email, $hash, $full_name, $role);

    if (mysqli_stmt_execute($stmt)) {
        $success = true;
        $message = 'Admin account created successfully. Delete this file now!';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack — Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.10); padding: 40px 48px; max-width: 520px; width: 100%; text-align: center; }
        h2 { margin: 0 0 12px; color: #1a1f2e; }
        p { color: #5f6368; line-height: 1.7; }
        .creds { background: #e6f4ea; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: left; }
        .creds p { margin: 4px 0; font-size: .9rem; }
        .creds strong { color: #1e8e3e; }
        .warning { background: #fce8e6; border-radius: 10px; padding: 16px; color: #c5221f; font-weight: 600; font-size: .875rem; }
        a { display: inline-block; margin-top: 20px; background: #34A853; color: #fff; padding: 11px 28px; border-radius: 999px; font-weight: 600; text-decoration: none; }
        a:hover { background: #1e8e3e; }
        .icon { font-size: 3rem; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="box">
    <?php if ($success): ?>
        <div class="icon">✅</div>
        <h2>Setup Complete!</h2>
        <p>Your default admin account has been created.</p>
        <div class="creds">
            <p>🔑 <strong>Username:</strong> admin</p>
            <p>🔒 <strong>Password:</strong> Admin@1234</p>
            <p>📧 <strong>Email:</strong> admin@school.edu</p>
        </div>
        <div class="warning">⚠️ DELETE this file (setup.php) immediately from your server!</div>
        <a href="login.php">Go to Login →</a>
    <?php else: ?>
        <div class="icon"><?= $count > 0 ? '🔒' : '❌' ?></div>
        <h2><?= $count > 0 ? 'Already Set Up' : 'Setup Failed' ?></h2>
        <p><?= htmlspecialchars($message) ?></p>
        <?php if ($count > 0): ?>
            <div class="warning">⚠️ DELETE this file (setup.php) from your server!</div>
            <a href="login.php">Go to Login →</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
