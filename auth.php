<?php
// ============================================================
//  auth.php — Session Guard
//  Include at the TOP of every protected page BEFORE any output.
//  Starts the session and redirects to login if not authenticated.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    // Store the originally requested URL so we can redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// ── Convenience globals set by login ─────────────────────────
// $_SESSION['user_id']    — int
// $_SESSION['username']   — string
// $_SESSION['full_name']  — string
// $_SESSION['role']       — 'admin' | 'staff'
