<?php
// ============================================================
//  delete.php — Delete a student record
//  Access: delete.php?id=N  (GET, protected by token optional)
// ============================================================
require_once 'auth.php';
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid request.'];
    header('Location: index.php');
    exit;
}

// Fetch the student name for the flash message
$stmt = mysqli_prepare($conn, 'SELECT full_name FROM students WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Student record not found.'];
    header('Location: index.php');
    exit;
}

$del = mysqli_prepare($conn, 'DELETE FROM students WHERE id = ?');
mysqli_stmt_bind_param($del, 'i', $id);
mysqli_stmt_execute($del);

if (mysqli_stmt_affected_rows($del) > 0) {
    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => "✓ {$row['full_name']}'s record has been deleted."
    ];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to delete the record.'];
}

header('Location: index.php');
exit;
