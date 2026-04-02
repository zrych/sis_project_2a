<?php
// ============================================================
//  login.php — EduTrack Login Page
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in → go to index
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// ── Flash message from logout / redirect ─────────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once 'db.php';

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic presence check
    if ($username === '') $errors['username'] = 'Username or email is required.';
    if ($password === '') $errors['password'] = 'Password is required.';

    if (empty($errors)) {
        // Look up by username OR email
        $stmt = mysqli_prepare($conn,
            'SELECT id, username, email, password, full_name, role, is_active
             FROM users
             WHERE (username = ? OR email = ?)
             LIMIT 1'
        );
        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if (!$user) {
            $errors['general'] = 'Invalid credentials. Please try again.';
        } elseif (!(int)$user['is_active']) {
            $errors['general'] = 'Your account has been deactivated. Contact an administrator.';
        } elseif (!password_verify($password, $user['password'])) {
            $errors['general'] = 'Invalid credentials. Please try again.';
        } else {
            // ── Successful login ──────────────────────────
            session_regenerate_id(true);   // prevent session fixation

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // Update last_login timestamp
            $upd = mysqli_prepare($conn, 'UPDATE users SET last_login = NOW() WHERE id = ?');
            mysqli_stmt_bind_param($upd, 'i', $user['id']);
            mysqli_stmt_execute($upd);

            // Redirect to originally requested page or index
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduTrack SIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --green:        #34A853;
            --green-dark:   #1e8e3e;
            --green-light:  #e6f4ea;
            --surface:      #ffffff;
            --surface-2:    #f8fafb;
            --border:       #e8eaed;
            --text-primary: #1a1f2e;
            --text-secondary:#5f6368;
            --text-muted:   #9aa0a6;
            --shadow-md:    0 4px 16px rgba(0,0,0,.08);
            --shadow-lg:    0 8px 40px rgba(0,0,0,.12);
            --radius:       16px;
            --radius-sm:    10px;
            --radius-pill:  999px;
            --transition:   all .2s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            background: var(--surface-2);
            color: var(--text-primary);
        }

        /* ── Left Panel (Branding) ────────────────────────── */
        .auth-left {
            width: 45%;
            background: linear-gradient(145deg, #1e8e3e 0%, #34A853 40%, #43c464 100%);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            padding: 64px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .auth-left::before {
            content: '';
            position: absolute;
            width: 360px; height: 360px;
            border-radius: 50%;
            border: 60px solid rgba(255,255,255,.07);
            top: -100px; right: -120px;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            width: 240px; height: 240px;
            border-radius: 50%;
            border: 40px solid rgba(255,255,255,.07);
            bottom: -60px; left: -80px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 56px;
            position: relative; z-index: 1;
        }

        .auth-brand-icon {
            width: 48px; height: 48px;
            background: rgba(255,255,255,.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff;
            backdrop-filter: blur(6px);
        }

        .auth-brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.6rem;
            color: #fff;
        }

        .auth-headline {
            font-family: 'DM Serif Display', serif;
            font-size: 2.4rem;
            color: #fff;
            line-height: 1.2;
            margin: 0 0 16px;
            position: relative; z-index: 1;
        }

        .auth-sub {
            color: rgba(255,255,255,.82);
            font-size: .95rem;
            line-height: 1.7;
            margin: 0 0 48px;
            max-width: 340px;
            position: relative; z-index: 1;
        }

        .auth-features {
            list-style: none;
            margin: 0; padding: 0;
            position: relative; z-index: 1;
        }

        .auth-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.9);
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 14px;
        }

        .auth-features li i {
            width: 28px; height: 28px;
            background: rgba(255,255,255,.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        /* ── Right Panel (Form) ───────────────────────────── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 32px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
        }

        .auth-card-header {
            margin-bottom: 36px;
        }

        .auth-card-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            margin: 0 0 8px;
            color: var(--text-primary);
        }

        .auth-card-header p {
            margin: 0;
            color: var(--text-secondary);
            font-size: .9rem;
        }

        /* Form elements */
        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            pointer-events: none;
            transition: var(--transition);
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: .925rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-primary);
            background: var(--surface);
            outline: none;
            transition: var(--transition);
        }

        .form-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(52,168,83,.15);
        }

        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon {
            color: var(--green);
        }

        .form-input.is-invalid {
            border-color: #d93025;
            box-shadow: 0 0 0 3px rgba(217,48,37,.1);
        }

        /* Password toggle */
        .input-wrap .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1rem;
            padding: 4px;
            border-radius: 4px;
            transition: var(--transition);
        }

        .input-wrap .pw-toggle:hover { color: var(--text-primary); }

        .form-input.pw { padding-right: 42px; }

        .invalid-msg {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .78rem;
            color: #d93025;
            margin-top: 6px;
        }

        /* General error banner */
        .error-banner {
            background: #fce8e6;
            border: 1px solid #f5c6c2;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c5221f;
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 24px;
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .875rem;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--green);
            cursor: pointer;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: var(--radius-pill);
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(52,168,83,.35);
            letter-spacing: .01em;
        }

        .btn-login:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(52,168,83,.40);
        }

        .btn-login:active { transform: translateY(0); }

        /* Register link */
        .auth-footer-link {
            text-align: center;
            margin-top: 28px;
            font-size: .875rem;
            color: var(--text-secondary);
        }

        .auth-footer-link a {
            color: var(--green);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer-link a:hover { text-decoration: underline; }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0;
            color: var(--text-muted);
            font-size: .78rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 768px) {
            .auth-left { display: none; }
            .auth-right { padding: 32px 20px; }
            body { background: var(--surface); }
            .auth-card-header h2 { font-size: 1.7rem; }

            /* Show brand on mobile at top */
            .mobile-brand {
                display: flex !important;
                align-items: center;
                gap: 10px;
                margin-bottom: 32px;
            }
        }

        .mobile-brand { display: none; }
        .mobile-brand-icon {
            width: 40px; height: 40px;
            background: var(--green);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 18px;
        }
        .mobile-brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.3rem;
            color: var(--text-primary);
        }
        .mobile-brand-name span { color: var(--green); }

        /* Loading state */
        .btn-login .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Left Branding Panel -->
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <span class="auth-brand-name">EduTrack</span>
        </div>

        <h1 class="auth-headline">Manage students with confidence.</h1>
        <p class="auth-sub">
            EduTrack is a complete Student Information System designed for academic institutions
            to streamline enrollment, track performance, and maintain records.
        </p>

        <ul class="auth-features">
            <li><i class="bi bi-people-fill"></i> Full CRUD Student Records</li>
            <li><i class="bi bi-bar-chart-line-fill"></i> Analytics &amp; Dashboard</li>
            <li><i class="bi bi-shield-lock-fill"></i> Role-based Access Control</li>
            <li><i class="bi bi-search"></i> Search &amp; Filter Records</li>
            <li><i class="bi bi-phone-fill"></i> Fully Responsive UI</li>
        </ul>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-right">
        <div class="auth-card">

            <!-- Mobile brand (hidden on desktop) -->
            <div class="mobile-brand">
                <div class="mobile-brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <span class="mobile-brand-name">Edu<span>Track</span></span>
            </div>

            <div class="auth-card-header">
                <h2>Welcome back</h2>
                <p>Sign in to your EduTrack account to continue.</p>
            </div>

            <!-- Flash message (e.g. signed out successfully) -->
            <?php if ($flash): ?>
                <div class="error-banner" style="<?= $flash['type'] === 'info' ? 'background:#e8f0fe;border-color:#c6d4f8;color:#1557b0;' : '' ?>">
                    <i class="bi <?= $flash['type'] === 'info' ? 'bi-info-circle-fill' : 'bi-check-circle-fill' ?>"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- General error -->
            <?php if (isset($errors['general'])): ?>
                <div class="error-banner">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login.php" id="loginForm" novalidate>

                <!-- Username / Email -->
                <div class="form-group">
                    <label class="form-label" for="username">Username or Email</label>
                    <div class="input-wrap">
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                            placeholder="Enter your username or email"
                            value="<?= htmlspecialchars($username) ?>"
                            autocomplete="username"
                            autofocus>
                        <i class="bi bi-person input-icon"></i>
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <p class="invalid-msg">
                            <i class="bi bi-x-circle-fill"></i>
                            <?= htmlspecialchars($errors['username']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input pw <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="Enter your password"
                            autocomplete="current-password">
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="pw-toggle" id="pwToggle" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="pwToggleIcon"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="invalid-msg">
                            <i class="bi bi-x-circle-fill"></i>
                            <?= htmlspecialchars($errors['password']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Remember me -->
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <div class="spinner" id="loginSpinner"></div>
                    <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
                    Sign In
                </button>

            </form>

            <!-- Register link -->
            <div class="auth-footer-link">
                Don't have an account?
                <a href="register.php">Create one →</a>
            </div>

        </div>
    </div>

<script>
    // Password visibility toggle
    const pwToggle     = document.getElementById('pwToggle');
    const pwInput      = document.getElementById('password');
    const pwToggleIcon = document.getElementById('pwToggleIcon');

    pwToggle.addEventListener('click', function () {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        pwToggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn     = document.getElementById('loginBtn');
        const spinner = document.getElementById('loginSpinner');
        const icon    = document.getElementById('loginIcon');
        btn.disabled  = true;
        spinner.style.display = 'block';
        icon.style.display    = 'none';
        btn.querySelector('span') && (btn.querySelector('span').textContent = 'Signing in…');
    });
</script>

</body>
</html>
