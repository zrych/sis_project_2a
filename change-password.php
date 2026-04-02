<?php
// ============================================================
//  change-password.php — Authenticated user changes own password
// ============================================================
require_once 'auth.php';
require_once 'db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password']  ?? '';
    $newPw    = $_POST['new_password']       ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';

    // ── Fetch current hash ────────────────────────────────
    $stmt = mysqli_prepare($conn, 'SELECT password FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $row  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Validate current password
    if ($current === '') {
        $errors['current'] = 'Current password is required.';
    } elseif (!$row || !password_verify($current, $row['password'])) {
        $errors['current'] = 'Your current password is incorrect.';
    }

    // Validate new password
    if ($newPw === '') {
        $errors['new'] = 'New password is required.';
    } elseif (strlen($newPw) < 8) {
        $errors['new'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $newPw)) {
        $errors['new'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPw)) {
        $errors['new'] = 'Password must contain at least one number.';
    } elseif ($newPw === $current) {
        $errors['new'] = 'New password must be different from your current password.';
    }

    if (empty($errors['new'])) {
        if ($confirm === '') {
            $errors['confirm'] = 'Please confirm your new password.';
        } elseif ($confirm !== $newPw) {
            $errors['confirm'] = 'Passwords do not match.';
        }
    }

    // ── Save ──────────────────────────────────────────────
    if (empty($errors)) {
        $hash = password_hash($newPw, PASSWORD_DEFAULT);
        $upd  = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id = ?');
        mysqli_stmt_bind_param($upd, 'si', $hash, $_SESSION['user_id']);
        mysqli_stmt_execute($upd);
        $success = true;
    }
}

$pageTitle  = 'Change Password';
$activePage = '';
include 'header.php';
?>

<div class="page-wrapper" style="max-width:560px;">

    <!-- Breadcrumb -->
    <nav style="font-size:.8125rem; color:var(--text-muted); margin-bottom:16px;">
        <a href="index.php" style="color:var(--green);">Home</a>
        <span style="margin:0 6px;">/</span>
        Change Password
    </nav>

    <div class="page-header">
        <h1>Change Password</h1>
        <p>Update your account password. Choose something strong and unique.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert-sis success" style="margin-bottom:24px;">
            <i class="bi bi-check-circle-fill"></i>
            Your password has been updated successfully.
        </div>
    <?php endif; ?>

    <div class="card-sis">
        <!-- Account info banner -->
        <div style="background:var(--surface-2); padding:16px 24px; border-bottom:1px solid var(--border);
                    display:flex; align-items:center; gap:12px;">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--green);
                        color:#fff;display:flex;align-items:center;justify-content:center;
                        font-weight:700;font-size:1rem;flex-shrink:0;">
                <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);">
                    <?= htmlspecialchars($_SESSION['full_name']) ?>
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);">
                    @<?= htmlspecialchars($_SESSION['username']) ?>
                    &bull; <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?>
                </div>
            </div>
        </div>

        <div class="card-sis-body">
            <form method="POST" action="change-password.php" novalidate>

                <!-- Current Password -->
                <div style="margin-bottom:20px;">
                    <label class="form-label-sis" for="current_password">Current Password</label>
                    <div style="position:relative;">
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-control-sis<?= isset($errors['current']) ? ' is-invalid' : '' ?>"
                               placeholder="Enter your current password"
                               style="padding-right:44px;">
                        <button type="button"
                                class="btn-icon"
                                onclick="togglePw('current_password', this)"
                                tabindex="-1"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);
                                       color:var(--text-muted);">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['current'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['current']) ?></p>
                    <?php endif; ?>
                </div>

                <hr style="border-color:var(--border); margin:24px 0;">

                <!-- New Password -->
                <div style="margin-bottom:20px;">
                    <label class="form-label-sis" for="new_password">New Password</label>
                    <div style="position:relative;">
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-control-sis<?= isset($errors['new']) ? ' is-invalid' : '' ?>"
                               placeholder="Min. 8 chars, one uppercase, one number"
                               style="padding-right:44px;">
                        <button type="button"
                                class="btn-icon"
                                onclick="togglePw('new_password', this)"
                                tabindex="-1"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);
                                       color:var(--text-muted);">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <!-- Password strength -->
                    <div style="margin-top:8px;">
                        <div style="height:4px;border-radius:2px;background:var(--border);overflow:hidden;margin-bottom:4px;">
                            <div id="pwBar" style="height:100%;border-radius:2px;width:0%;transition:width .3s,background .3s;"></div>
                        </div>
                        <span id="pwLabel" style="font-size:.72rem;"></span>
                    </div>
                    <?php if (isset($errors['new'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['new']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Confirm New Password -->
                <div style="margin-bottom:28px;">
                    <label class="form-label-sis" for="confirm_password">Confirm New Password</label>
                    <div style="position:relative;">
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-control-sis<?= isset($errors['confirm']) ? ' is-invalid' : '' ?>"
                               placeholder="Re-enter your new password"
                               style="padding-right:44px;">
                        <button type="button"
                                class="btn-icon"
                                onclick="togglePw('confirm_password', this)"
                                tabindex="-1"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);
                                       color:var(--text-muted);">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="matchIndicator" style="font-size:.78rem;margin-top:5px;"></div>
                    <?php if (isset($errors['confirm'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['confirm']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Password rules reminder -->
                <div style="background:var(--surface-2);border:1px solid var(--border);
                            border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:24px;">
                    <p style="font-size:.78rem;font-weight:700;color:var(--text-secondary);
                               text-transform:uppercase;letter-spacing:.04em;margin:0 0 8px;">
                        Password Requirements
                    </p>
                    <ul style="margin:0;padding-left:18px;font-size:.8125rem;color:var(--text-secondary);line-height:1.8;">
                        <li>At least <strong>8 characters</strong> long</li>
                        <li>At least one <strong>uppercase letter</strong> (A–Z)</li>
                        <li>At least one <strong>number</strong> (0–9)</li>
                        <li>Must be different from your current password</li>
                    </ul>
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <button type="submit" class="btn-green">
                        <i class="bi bi-floppy"></i> Update Password
                    </button>
                    <a href="index.php" class="btn-outline-sis">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
function togglePw(id, btn) {
    const inp  = document.getElementById(id);
    const icon = btn.querySelector('i');
    const isText = inp.type === 'text';
    inp.type   = isText ? 'password' : 'text';
    icon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Strength meter
document.getElementById('new_password').addEventListener('input', function () {
    const v   = this.value;
    const bar = document.getElementById('pwBar');
    const lbl = document.getElementById('pwLabel');
    let score = 0;
    if (v.length >= 8)            score++;
    if (/[A-Z]/.test(v))          score++;
    if (/[0-9]/.test(v))          score++;
    if (/[^a-zA-Z0-9]/.test(v))   score++;

    const levels = [
        {w:'0%',  c:'transparent', t:''},
        {w:'25%', c:'#d93025',     t:'Weak'},
        {w:'50%', c:'#f4a532',     t:'Fair'},
        {w:'75%', c:'#1a73e8',     t:'Good'},
        {w:'100%',c:'#34A853',     t:'Strong'},
    ];
    const l = levels[score] || levels[0];
    bar.style.width      = l.w;
    bar.style.background = l.c;
    lbl.textContent      = l.t;
    lbl.style.color      = l.c;

    checkMatch();
});

// Match indicator
document.getElementById('confirm_password').addEventListener('input', checkMatch);

function checkMatch() {
    const nw  = document.getElementById('new_password').value;
    const cf  = document.getElementById('confirm_password').value;
    const ind = document.getElementById('matchIndicator');
    if (cf === '') { ind.textContent = ''; return; }
    if (nw === cf) {
        ind.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#34A853;"></i> <span style="color:#1e8e3e;font-weight:600;">Passwords match</span>';
    } else {
        ind.innerHTML = '<i class="bi bi-x-circle-fill" style="color:#d93025;"></i> <span style="color:#d93025;">Passwords do not match</span>';
    }
}
</script>

<?php include 'footer.php'; ?>
