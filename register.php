<?php
// ============================================================
//  register.php — Create new user accounts
//  Access rules:
//    • If no users exist yet → open (first-run setup mode)
//    • If users exist → must be logged in as admin
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'db.php';

// ── Check access ──────────────────────────────────────────
$userCount = (int)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS n FROM users'))['n'] ?? 0);
$isFirstRun  = $userCount === 0;
$isAdmin     = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';

if (!$isFirstRun && !$isAdmin) {
    // Not first run and not an admin → deny
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$errors  = [];
$success = false;
$f       = ['username' => '', 'email' => '', 'full_name' => '', 'role' => 'staff'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = [
        'username'  => trim($_POST['username']  ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role'      => in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : 'staff',
    ];
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    // ── Validation ────────────────────────────────────────
    if ($f['username'] === '')
        $errors['username'] = 'Username is required.';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $f['username']))
        $errors['username'] = 'Username must be 3–30 characters (letters, numbers, underscore only).';

    if ($f['full_name'] === '')
        $errors['full_name'] = 'Full name is required.';

    if ($f['email'] === '')
        $errors['email'] = 'Email is required.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Please enter a valid email address.';

    if ($password === '')
        $errors['password'] = 'Password is required.';
    elseif (strlen($password) < 8)
        $errors['password'] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Z]/', $password))
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    elseif (!preg_match('/[0-9]/', $password))
        $errors['password'] = 'Password must contain at least one number.';

    if ($password2 === '')
        $errors['password2'] = 'Please confirm your password.';
    elseif ($password !== $password2)
        $errors['password2'] = 'Passwords do not match.';

    // Unique checks
    if (empty($errors['username'])) {
        $chk = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
        mysqli_stmt_bind_param($chk, 's', $f['username']);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0)
            $errors['username'] = 'This username is already taken.';
    }

    if (empty($errors['email'])) {
        $chk = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($chk, 's', $f['email']);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0)
            $errors['email'] = 'This email is already registered.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // First-run user is always admin
        $role = $isFirstRun ? 'admin' : $f['role'];

        $ins = mysqli_prepare($conn,
            'INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($ins, 'sssss', $f['username'], $f['email'], $hash, $f['full_name'], $role);

        if (mysqli_stmt_execute($ins)) {
            if ($isFirstRun) {
                // Auto-login and redirect
                session_regenerate_id(true);
                $_SESSION['user_id']   = (int)mysqli_insert_id($conn);
                $_SESSION['username']  = $f['username'];
                $_SESSION['full_name'] = $f['full_name'];
                $_SESSION['role']      = 'admin';
                $_SESSION['flash']     = [
                    'type'    => 'success',
                    'message' => "✓ Account created! Welcome, {$f['full_name']}."
                ];
                header('Location: index.php');
                exit;
            } else {
                $success = true;
                $f = ['username' => '', 'email' => '', 'full_name' => '', 'role' => 'staff'];
            }
        } else {
            $errors['general'] = 'Database error: ' . mysqli_error($conn);
        }
    }
}

function eVal(string $key, array $f): string {
    return htmlspecialchars($f[$key] ?? '');
}
function eClass(string $key, array $errors): string {
    return isset($errors[$key]) ? ' is-invalid' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isFirstRun ? 'Initial Setup' : 'Create Account' ?> | EduTrack SIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --green:#34A853; --green-dark:#1e8e3e; --green-light:#e6f4ea;
            --surface:#fff; --surface-2:#f8fafb; --border:#e8eaed;
            --text-primary:#1a1f2e; --text-secondary:#5f6368; --text-muted:#9aa0a6;
            --shadow-md:0 4px 16px rgba(0,0,0,.08);
            --radius:16px; --radius-sm:10px; --radius-pill:999px;
            --transition:all .2s cubic-bezier(.4,0,.2,1);
        }
        *, *::before, *::after { box-sizing:border-box; }
        body {
            font-family:'Plus Jakarta Sans',sans-serif;
            min-height:100vh; margin:0;
            background:var(--surface-2);
            display:flex; align-items:center; justify-content:center;
            padding:40px 20px;
        }
        .register-card {
            background:var(--surface);
            border-radius:var(--radius);
            box-shadow:0 8px 40px rgba(0,0,0,.10);
            width:100%; max-width:560px;
            overflow:hidden;
        }
        .register-header {
            background:linear-gradient(135deg,#1e8e3e,#34A853);
            padding:32px 40px;
            color:#fff;
        }
        .register-header .brand {
            display:flex; align-items:center; gap:10px;
            font-family:'DM Serif Display',serif;
            font-size:1.2rem; color:#fff; margin-bottom:20px;
        }
        .register-header .brand-icon {
            width:36px;height:36px;background:rgba(255,255,255,.2);
            border-radius:10px;display:flex;align-items:center;justify-content:center;
            font-size:16px;
        }
        .register-header h2 {
            font-family:'DM Serif Display',serif;
            font-size:1.8rem; margin:0 0 6px; color:#fff;
        }
        .register-header p { margin:0;opacity:.85;font-size:.875rem; }

        .register-body { padding:36px 40px; }

        .first-run-banner {
            background:var(--green-light);
            border:1px solid #ceead6;
            border-radius:var(--radius-sm);
            padding:12px 16px;
            display:flex; align-items:center; gap:10px;
            color:var(--green-dark);
            font-size:.875rem; font-weight:500;
            margin-bottom:24px;
        }

        .success-banner {
            background:var(--green-light); border:1px solid #ceead6;
            border-radius:var(--radius-sm); padding:16px;
            display:flex; align-items:center; gap:10px;
            color:var(--green-dark); font-weight:600;
            margin-bottom:24px; font-size:.9rem;
        }

        .error-banner {
            background:#fce8e6; border:1px solid #f5c6c2;
            border-radius:var(--radius-sm); padding:12px 16px;
            display:flex; align-items:center; gap:10px;
            color:#c5221f; font-size:.875rem; font-weight:500;
            margin-bottom:20px;
        }

        .row { display:flex; gap:16px; }
        .row > div { flex:1; }

        .form-group { margin-bottom:20px; }
        .form-label {
            display:block; font-size:.78rem; font-weight:700;
            letter-spacing:.05em; text-transform:uppercase;
            color:var(--text-secondary); margin-bottom:7px;
        }
        .input-wrap { position:relative; }
        .input-icon {
            position:absolute; left:13px; top:50%; transform:translateY(-50%);
            color:var(--text-muted); pointer-events:none; font-size:.95rem;
            transition:var(--transition);
        }
        .form-input {
            width:100%; padding:11px 14px 11px 38px;
            border:1.5px solid var(--border); border-radius:var(--radius-sm);
            font-size:.9rem; font-family:'Plus Jakarta Sans',sans-serif;
            color:var(--text-primary); background:var(--surface);
            outline:none; transition:var(--transition);
        }
        .form-input:focus {
            border-color:var(--green);
            box-shadow:0 0 0 3px rgba(52,168,83,.14);
        }
        .form-input:focus ~ .input-icon,
        .input-wrap:focus-within .input-icon { color:var(--green); }
        .form-input.is-invalid {
            border-color:#d93025;
            box-shadow:0 0 0 3px rgba(217,48,37,.10);
        }
        .form-select { padding:11px 14px; }
        .pw-toggle {
            position:absolute; right:12px; top:50%; transform:translateY(-50%);
            background:none; border:none; cursor:pointer;
            color:var(--text-muted); font-size:.95rem; padding:4px;
            border-radius:4px; transition:var(--transition);
        }
        .pw-toggle:hover { color:var(--text-primary); }
        .form-input.pw { padding-right:40px; }
        .invalid-msg {
            display:flex; align-items:center; gap:5px;
            font-size:.78rem; color:#d93025; margin-top:5px;
        }
        .hint { font-size:.75rem; color:var(--text-muted); margin-top:5px; }

        /* Password strength */
        .pw-strength { margin-top:8px; }
        .pw-strength-bar {
            height:4px; border-radius:2px; background:var(--border);
            overflow:hidden; margin-bottom:4px;
        }
        .pw-strength-fill {
            height:100%; border-radius:2px;
            width:0%; transition:width .3s, background .3s;
        }
        .pw-strength-label { font-size:.72rem; color:var(--text-muted); }

        .btn-submit {
            width:100%; padding:13px;
            background:var(--green); color:#fff;
            border:none; border-radius:var(--radius-pill);
            font-size:1rem; font-weight:700;
            font-family:'Plus Jakarta Sans',sans-serif;
            cursor:pointer; transition:var(--transition);
            box-shadow:0 2px 8px rgba(52,168,83,.3);
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-submit:hover { background:var(--green-dark); transform:translateY(-1px); box-shadow:0 4px 16px rgba(52,168,83,.38); }
        .auth-footer-link { text-align:center; margin-top:20px; font-size:.875rem; color:var(--text-secondary); }
        .auth-footer-link a { color:var(--green); font-weight:600; text-decoration:none; }
        .auth-footer-link a:hover { text-decoration:underline; }

        @media (max-width:520px) {
            .register-body, .register-header { padding:24px 24px; }
            .row { flex-direction:column; gap:0; }
        }
    </style>
</head>
<body>

<div class="register-card">
    <!-- Header -->
    <div class="register-header">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            EduTrack SIS
        </div>
        <h2><?= $isFirstRun ? 'Initial Setup' : 'Create Account' ?></h2>
        <p><?= $isFirstRun
            ? 'No accounts found. Set up your administrator account to get started.'
            : 'Add a new staff or admin user to the system.'
        ?></p>
    </div>

    <!-- Body -->
    <div class="register-body">

        <?php if ($isFirstRun): ?>
            <div class="first-run-banner">
                <i class="bi bi-info-circle-fill"></i>
                First-run mode: this account will be assigned the <strong>Admin</strong> role automatically.
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-banner">
                <i class="bi bi-check-circle-fill"></i>
                Account created successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="error-banner">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>

            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <div class="input-wrap">
                    <input type="text" id="full_name" name="full_name"
                           class="form-input<?= eClass('full_name', $errors) ?>"
                           placeholder="e.g. Maria Santos"
                           value="<?= eVal('full_name', $f) ?>">
                    <i class="bi bi-person input-icon"></i>
                </div>
                <?php if (isset($errors['full_name'])): ?>
                    <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['full_name']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Username + Email -->
            <div class="row">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username"
                               class="form-input<?= eClass('username', $errors) ?>"
                               placeholder="e.g. jdelacruz"
                               value="<?= eVal('username', $f) ?>">
                        <i class="bi bi-at input-icon"></i>
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['username']) ?></p>
                    <?php else: ?>
                        <p class="hint">3–30 chars. Letters, numbers, underscore.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email"
                               class="form-input<?= eClass('email', $errors) ?>"
                               placeholder="user@school.edu"
                               value="<?= eVal('email', $f) ?>">
                        <i class="bi bi-envelope input-icon"></i>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Password + Confirm -->
            <div class="row">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               class="form-input pw<?= eClass('password', $errors) ?>"
                               placeholder="Min. 8 characters">
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="pw-toggle" onclick="togglePw('password', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="pw-strength">
                        <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwBar"></div></div>
                        <span class="pw-strength-label" id="pwLabel"></span>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password2">Confirm Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password2" name="password2"
                               class="form-input pw<?= eClass('password2', $errors) ?>"
                               placeholder="Re-enter password">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <button type="button" class="pw-toggle" onclick="togglePw('password2', this)" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password2'])): ?>
                        <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['password2']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Role (hidden for first-run, shown for admins) -->
            <?php if (!$isFirstRun && $isAdmin): ?>
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <div class="input-wrap">
                    <select id="role" name="role" class="form-input form-select">
                        <option value="staff"  <?= $f['role'] === 'staff'  ? 'selected' : '' ?>>Staff</option>
                        <option value="admin"  <?= $f['role'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <i class="bi bi-shield input-icon"></i>
                </div>
                <p class="hint">Admins can create and manage other users. Staff can only manage students.</p>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                <i class="bi bi-person-plus-fill"></i>
                <?= $isFirstRun ? 'Create Admin Account' : 'Create Account' ?>
            </button>
        </form>

        <div class="auth-footer-link">
            <?php if ($isAdmin): ?>
                <a href="index.php"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
            <?php else: ?>
                Already have an account? <a href="login.php">Sign in →</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function () {
    const v = this.value;
    const bar = document.getElementById('pwBar');
    const lbl = document.getElementById('pwLabel');
    let score = 0;
    if (v.length >= 8)         score++;
    if (/[A-Z]/.test(v))       score++;
    if (/[0-9]/.test(v))       score++;
    if (/[^a-zA-Z0-9]/.test(v)) score++;

    const levels = [
        { w: '0%',   c: 'transparent', t: '' },
        { w: '25%',  c: '#d93025',     t: 'Weak' },
        { w: '50%',  c: '#f4a532',     t: 'Fair' },
        { w: '75%',  c: '#1a73e8',     t: 'Good' },
        { w: '100%', c: '#34A853',     t: 'Strong' },
    ];

    const l = levels[score] || levels[0];
    bar.style.width      = l.w;
    bar.style.background = l.c;
    lbl.textContent      = l.t;
    lbl.style.color      = l.c;
});
</script>
</body>
</html>
