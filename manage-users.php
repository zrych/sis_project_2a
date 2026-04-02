<?php
// ============================================================
//  manage-users.php — Admin: User Management
//  Only accessible to users with role = 'admin'
// ============================================================
require_once 'auth.php';
require_once 'db.php';

// ── Admin-only guard ──────────────────────────────────────
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['flash'] = [
        'type'    => 'error',
        'message' => 'Access denied. Administrator privileges are required.'
    ];
    header('Location: index.php');
    exit;
}

// ── Handle actions ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetId = (int)($_POST['user_id'] ?? 0);

    // Prevent acting on yourself
    if ($targetId === (int)$_SESSION['user_id']) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You cannot modify your own account from this panel.'];
        header('Location: manage-users.php');
        exit;
    }

    // Fetch target user
    $chk  = mysqli_prepare($conn, 'SELECT full_name, is_active FROM users WHERE id = ?');
    mysqli_stmt_bind_param($chk, 'i', $targetId);
    mysqli_stmt_execute($chk);
    $tUser = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

    if (!$tUser) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'User not found.'];
        header('Location: manage-users.php');
        exit;
    }

    switch ($_POST['action']) {
        case 'toggle_active':
            $newStatus = $tUser['is_active'] ? 0 : 1;
            $upd = mysqli_prepare($conn, 'UPDATE users SET is_active = ? WHERE id = ?');
            mysqli_stmt_bind_param($upd, 'ii', $newStatus, $targetId);
            mysqli_stmt_execute($upd);
            $label = $newStatus ? 'activated' : 'deactivated';
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "✓ {$tUser['full_name']}'s account has been {$label}."
            ];
            break;

        case 'delete':
            $del = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
            mysqli_stmt_bind_param($del, 'i', $targetId);
            mysqli_stmt_execute($del);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "✓ {$tUser['full_name']}'s account has been permanently deleted."
            ];
            break;

        default:
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unknown action.'];
    }

    header('Location: manage-users.php');
    exit;
}

// ── Flash ─────────────────────────────────────────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Fetch all users ───────────────────────────────────────
$res   = mysqli_query($conn,
    'SELECT id, username, email, full_name, role, is_active, last_login, created_at
     FROM users ORDER BY role ASC, created_at ASC'
);
$users = mysqli_fetch_all($res, MYSQLI_ASSOC);

$pageTitle  = 'Manage Users';
$activePage = 'manage-users';
include 'header.php';
?>

<div class="page-wrapper">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>User Management</h1>
            <p>Manage administrator and staff accounts that have access to EduTrack.</p>
        </div>
        <a href="register.php" class="btn-green">
            <i class="bi bi-person-plus-fill"></i> Add New User
        </a>
    </div>

    <!-- Flash -->
    <?php if ($flash): ?>
        <div class="alert-sis <?= htmlspecialchars($flash['type']) ?>">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <?php
        $totalUsers  = count($users);
        $admins      = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
        $staff       = count(array_filter($users, fn($u) => $u['role'] === 'staff'));
        $activeCount = count(array_filter($users, fn($u) => (int)$u['is_active'] === 1));
        ?>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="padding:18px 20px;">
                <div class="stat-icon green" style="width:40px;height:40px;font-size:18px;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.6rem;"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="padding:18px 20px;">
                <div class="stat-icon blue" style="width:40px;height:40px;font-size:18px;">
                    <i class="bi bi-shield-fill"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.6rem;"><?= $admins ?></div>
                    <div class="stat-label">Admins</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="padding:18px 20px;">
                <div class="stat-icon orange" style="width:40px;height:40px;font-size:18px;">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.6rem;"><?= $staff ?></div>
                    <div class="stat-label">Staff</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="padding:18px 20px;">
                <div class="stat-icon green" style="width:40px;height:40px;font-size:18px;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.6rem;"><?= $activeCount ?></div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card-sis">
        <div class="card-sis-header">
            <strong><?= $totalUsers ?> user<?= $totalUsers !== 1 ? 's' : '' ?> registered</strong>
        </div>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="bi bi-people" style="color:var(--green-mid);"></i>
                <p>No users found.</p>
                <a href="register.php" class="btn-green mt-3 d-inline-flex">
                    <i class="bi bi-person-plus-fill"></i> Add First User
                </a>
            </div>
        <?php else: ?>
        <div class="table-responsive-sis">
            <table class="table-sis">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Registered</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u):
                        $isSelf   = ((int)$u['id'] === (int)$_SESSION['user_id']);
                        $isActive = (int)$u['is_active'] === 1;
                    ?>
                    <tr style="<?= !$isActive ? 'opacity:.55;' : '' ?>">
                        <td class="muted"><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:34px; height:34px; border-radius:50%;
                                            background:<?= $u['role'] === 'admin' ? '#e8f0fe' : 'var(--green-light)' ?>;
                                            color:<?= $u['role'] === 'admin' ? '#1a73e8' : 'var(--green)' ?>;
                                            display:flex; align-items:center; justify-content:center;
                                            font-weight:700; font-size:.85rem; flex-shrink:0;">
                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="student-name">
                                        <?= htmlspecialchars($u['full_name']) ?>
                                        <?php if ($isSelf): ?>
                                            <span style="font-size:.7rem; background:var(--green-light);
                                                         color:var(--green); padding:1px 7px;
                                                         border-radius:999px; font-weight:600; margin-left:4px;">
                                                You
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="student-id-tag">@<?= htmlspecialchars($u['username']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="muted"><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span style="display:inline-flex; align-items:center; gap:5px;
                                             padding:3px 10px; border-radius:999px;
                                             background:#e8f0fe; color:#1a73e8;
                                             font-size:.75rem; font-weight:700;">
                                    <i class="bi bi-shield-fill"></i> Admin
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex; align-items:center; gap:5px;
                                             padding:3px 10px; border-radius:999px;
                                             background:var(--green-light); color:var(--green);
                                             font-size:.75rem; font-weight:700;">
                                    <i class="bi bi-person-fill"></i> Staff
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isActive): ?>
                                <span style="display:inline-flex; align-items:center; gap:5px;
                                             font-size:.78rem; font-weight:600; color:#1e8e3e;">
                                    <i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Active
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex; align-items:center; gap:5px;
                                             font-size:.78rem; font-weight:600; color:#9aa0a6;">
                                    <i class="bi bi-circle" style="font-size:.45rem;"></i> Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.8125rem; color:var(--text-muted);">
                            <?= $u['last_login'] ? date('M j, Y g:i A', strtotime($u['last_login'])) : '—' ?>
                        </td>
                        <td style="font-size:.8125rem; color:var(--text-muted);">
                            <?= date('M j, Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <?php if (!$isSelf): ?>
                                    <!-- Toggle active -->
                                    <form method="POST" action="manage-users.php" style="display:inline;">
                                        <input type="hidden" name="action"  value="toggle_active">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit"
                                                class="btn-icon <?= $isActive ? 'delete' : 'view' ?>"
                                                title="<?= $isActive ? 'Deactivate' : 'Activate' ?> account"
                                                style="<?= $isActive ? '' : 'color:var(--green);' ?>">
                                            <i class="bi bi-<?= $isActive ? 'toggle-on' : 'toggle-off' ?>"
                                               style="font-size:1.2rem;"></i>
                                        </button>
                                    </form>
                                    <!-- Delete -->
                                    <button class="btn-icon delete" title="Delete account"
                                            onclick="confirmUserDelete(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['full_name'])) ?>')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                <?php else: ?>
                                    <span style="font-size:.75rem; color:var(--text-muted); padding:0 8px;">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info note -->
    <p style="font-size:.8rem; color:var(--text-muted); margin-top:14px; text-align:center;">
        <i class="bi bi-info-circle"></i>
        Deactivated accounts cannot sign in but their data is preserved.
        Deleted accounts are permanently removed.
    </p>

</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade modal-sis" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;color:var(--text-primary);">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#d93025;"></i>
                    Delete User Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.9rem; color:var(--text-secondary);">
                Are you sure you want to permanently delete
                <strong id="deleteUserName" style="color:var(--text-primary);"></strong>'s account?
                <br><br>This action <strong>cannot be undone.</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-sis" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="manage-users.php" id="deleteUserForm" style="display:inline;">
                    <input type="hidden" name="action"  value="delete">
                    <input type="hidden" name="user_id" id="deleteUserIdInput" value="">
                    <button type="submit"
                            style="background:#d93025;color:#fff;border:none;padding:9px 20px;
                                   border-radius:999px;font-size:.875rem;font-weight:600;
                                   display:inline-flex;align-items:center;gap:7px;cursor:pointer;
                                   font-family:'Plus Jakarta Sans',sans-serif;">
                        <i class="bi bi-trash3-fill"></i> Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmUserDelete(id, name) {
    document.getElementById('deleteUserName').textContent    = name;
    document.getElementById('deleteUserIdInput').value       = id;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include 'footer.php'; ?>
