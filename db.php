<?php
// ============================================================
//  db.php — Database Connection (MySQLi)
//  Edit the constants below to match your environment.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // ← change as needed
define('DB_PASS', '');              // ← change as needed
define('DB_NAME', 'student_db');
define('DB_PORT', 3306);

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

mysqli_set_charset($conn, 'utf8mb4');
