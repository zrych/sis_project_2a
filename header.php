<?php
// ============================================================
//  header.php — Shared Navigation & HTML Head
//  Usage: include 'header.php'; (set $pageTitle before including)
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle  = $pageTitle  ?? 'Student Information System';
$activePage = $activePage ?? '';

// Current logged-in user (set by auth.php / login.php)
$authUser     = $_SESSION['full_name'] ?? 'User';
$authUsername = $_SESSION['username']  ?? '';
$authRole     = $_SESSION['role']      ?? 'staff';
$authInitial  = strtoupper(substr($authUser, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | EduTrack SIS</title>

    <!-- Google Fonts: Plus Jakarta Sans + DM Serif Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ── Design Tokens ───────────────────────────────── */
        :root {
            --green:        #34A853;
            --green-dark:   #1e8e3e;
            --green-light:  #e6f4ea;
            --green-mid:    #ceead6;
            --surface:      #ffffff;
            --surface-2:    #f8fafb;
            --border:       #e8eaed;
            --text-primary: #1a1f2e;
            --text-secondary:#5f6368;
            --text-muted:   #9aa0a6;
            --shadow-sm:    0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md:    0 4px 16px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
            --shadow-lg:    0 8px 32px rgba(0,0,0,.10);
            --radius:       12px;
            --radius-sm:    8px;
            --radius-pill:  999px;
            --transition:   all .2s cubic-bezier(.4,0,.2,1);
        }

        /* ── Base ────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface-2);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'DM Serif Display', serif;
            letter-spacing: -.01em;
        }

        a { text-decoration: none; color: inherit; }

        /* ── Navbar ──────────────────────────────────────── */
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 0 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            height: 64px;
            width: 100%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'DM Serif Display', serif;
            font-size: 1.25rem;
            color: var(--text-primary);
        }

        .brand-icon {
            width: 36px; height: 36px;
            background: var(--green);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 18px;
        }

        .brand span { color: var(--green); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            margin: 0; padding: 0;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            font-size: .875rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .nav-links a:hover {
            background: var(--surface-2);
            color: var(--text-primary);
        }

        .nav-links a.active {
            background: var(--green-light);
            color: var(--green-dark);
        }

        .nav-links a i { font-size: 1rem; }

        /* Hamburger for mobile */
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
        }

        @media (max-width: 768px) {
            .nav-toggle { display: flex; align-items: center; }
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 64px; left: 0; right: 0;
                background: var(--surface);
                border-bottom: 1px solid var(--border);
                padding: 12px 16px;
                gap: 2px;
                box-shadow: var(--shadow-md);
            }
            .nav-links.open { display: flex; }
            .nav-links a { width: 100%; padding: 10px 14px; }
        }

        /* ── Layout Wrapper ──────────────────────────────── */
        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px;
            width: 100%;
            flex: 1;
        }

        /* ── Page Header ─────────────────────────────────── */
        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            font-size: 2rem;
            margin: 0 0 6px;
            color: var(--text-primary);
        }

        .page-header p {
            margin: 0;
            color: var(--text-secondary);
            font-size: .95rem;
        }

        /* ── Cards ───────────────────────────────────────── */
        .card-sis {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-sis-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-sis-body { padding: 24px; }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-green {
            background: var(--green);
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: var(--radius-pill);
            font-size: .875rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(52,168,83,.30);
        }

        .btn-green:hover {
            background: var(--green-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52,168,83,.35);
        }

        .btn-green:active { transform: translateY(0); }

        .btn-outline-sis {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            padding: 9px 18px;
            border-radius: var(--radius-pill);
            font-size: .875rem;
            font-weight: 500;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-outline-sis:hover {
            border-color: var(--text-secondary);
            color: var(--text-primary);
            background: var(--surface-2);
        }

        .btn-icon {
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            border: none;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        .btn-icon.edit { color: #1a73e8; }
        .btn-icon.edit:hover { background: #e8f0fe; }
        .btn-icon.delete { color: #d93025; }
        .btn-icon.delete:hover { background: #fce8e6; }
        .btn-icon.view { color: var(--green); }
        .btn-icon.view:hover { background: var(--green-light); }

        /* ── Badges ──────────────────────────────────────── */
        .badge-year {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: var(--radius-pill);
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-year.y1 { background: #e8f0fe; color: #1a73e8; }
        .badge-year.y2 { background: var(--green-light); color: var(--green-dark); }
        .badge-year.y3 { background: #fce8e6; color: #d93025; }
        .badge-year.y4 { background: #fff3e0; color: #e65100; }

        .badge-gpa {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: var(--radius-pill);
            font-size: .78rem;
            font-weight: 700;
        }

        /* ── Alert / Flash Messages ───────────────────────── */
        .alert-sis {
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            font-size: .875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-sis.success {
            background: var(--green-light);
            border: 1px solid var(--green-mid);
            color: var(--green-dark);
        }

        .alert-sis.error {
            background: #fce8e6;
            border: 1px solid #f5c6c2;
            color: #c5221f;
        }

        .alert-sis.info {
            background: #e8f0fe;
            border: 1px solid #c6d4f8;
            color: #1557b0;
        }

        /* ── Forms ───────────────────────────────────────── */
        .form-label-sis {
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: .02em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-control-sis, .form-select-sis {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: .9rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-primary);
            background: var(--surface);
            transition: var(--transition);
            outline: none;
        }

        .form-control-sis:focus, .form-select-sis:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(52,168,83,.15);
        }

        .form-control-sis.is-invalid, .form-select-sis.is-invalid {
            border-color: #d93025;
            box-shadow: 0 0 0 3px rgba(217,48,37,.12);
        }

        .invalid-msg {
            font-size: .78rem;
            color: #d93025;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        textarea.form-control-sis { resize: vertical; min-height: 80px; }

        /* ── Search input ─────────────────────────────────── */
        .search-wrap {
            position: relative;
            flex: 1;
            max-width: 340px;
        }

        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 9px 14px 9px 36px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-pill);
            font-size: .875rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-primary);
            background: var(--surface);
            transition: var(--transition);
            outline: none;
        }

        .search-input:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(52,168,83,.12);
        }

        /* ── Table ───────────────────────────────────────── */
        .table-sis {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .table-sis thead th {
            padding: 12px 16px;
            background: var(--surface-2);
            color: var(--text-muted);
            font-weight: 600;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table-sis tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .table-sis tbody tr:last-child { border-bottom: none; }
        .table-sis tbody tr:hover { background: var(--surface-2); }

        .table-sis td {
            padding: 14px 16px;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .table-sis td.muted { color: var(--text-secondary); }

        .student-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .student-id-tag {
            font-size: .75rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Stat Cards ──────────────────────────────────── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-icon.green  { background: var(--green-light); color: var(--green); }
        .stat-icon.blue   { background: #e8f0fe; color: #1a73e8; }
        .stat-icon.orange { background: #fff3e0; color: #e65100; }
        .stat-icon.red    { background: #fce8e6; color: #d93025; }

        .stat-value {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: .8125rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* ── Chart containers ─────────────────────────────── */
        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 24px;
        }

        .chart-card h3 {
            font-size: 1rem;
            margin: 0 0 20px;
            color: var(--text-primary);
        }

        /* ── GPA Colour Utility ───────────────────────────── */
        .gpa-excellent { background:#e6f4ea; color:#1e8e3e; }
        .gpa-good      { background:#e8f0fe; color:#1a73e8; }
        .gpa-average   { background:#fff3e0; color:#e65100; }
        .gpa-poor      { background:#fce8e6; color:#d93025; }

        /* ── Modal ───────────────────────────────────────── */
        .modal-sis .modal-content {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .modal-sis .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
        }

        .modal-sis .modal-body  { padding: 24px; }
        .modal-sis .modal-footer {
            border-top: 1px solid var(--border);
            padding: 16px 24px;
        }

        /* ── Empty state ─────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: var(--text-muted);
        }

        .empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; }
        .empty-state p { margin: 0; font-size: .95rem; }

        /* ── Footer ──────────────────────────────────────── */
        .site-footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 20px 24px;
            text-align: center;
            font-size: .8125rem;
            color: var(--text-muted);
            margin-top: auto;
        }

        .site-footer strong { color: var(--green); }

        /* ── Responsive helpers ──────────────────────────── */
        @media (max-width: 640px) {
            .page-wrapper { padding: 20px 16px; }
            .page-header h1 { font-size: 1.6rem; }
            .table-responsive-sis { overflow-x: auto; }
        }

        /* ── User Avatar & Dropdown ──────────────────────── */
        .nav-user {
            position: relative;
            margin-left: 8px;
        }

        .nav-user-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 12px 5px 5px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-pill);
            background: var(--surface);
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .nav-user-btn:hover {
            border-color: var(--green);
            background: var(--green-light);
        }

        .nav-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: var(--green);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .nav-user-name {
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-primary);
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nav-caret {
            font-size: .7rem;
            color: var(--text-muted);
            transition: transform .2s;
        }

        .nav-user.open .nav-caret { transform: rotate(180deg); }

        .nav-dropdown {
            display: none;
            position: absolute;
            right: 0; top: calc(100% + 8px);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            min-width: 220px;
            z-index: 999;
            overflow: hidden;
        }

        .nav-user.open .nav-dropdown { display: block; }

        .nav-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }

        .nav-dropdown-header .dd-name {
            font-weight: 700;
            font-size: .875rem;
            color: var(--text-primary);
        }

        .nav-dropdown-header .dd-role {
            font-size: .72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 2px;
        }

        .nav-dropdown a, .nav-dropdown button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 16px;
            font-size: .875rem;
            color: var(--text-secondary);
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background .15s;
            text-decoration: none;
        }

        .nav-dropdown a:hover, .nav-dropdown button:hover {
            background: var(--surface-2);
            color: var(--text-primary);
        }

        .nav-dropdown .dd-logout {
            border-top: 1px solid var(--border);
            color: #d93025;
        }

        .nav-dropdown .dd-logout:hover { background: #fce8e6; color: #c5221f; }

        @media (max-width: 768px) {
            .nav-user { display: none; }
        }
    </style>
</head>
<body>

<!-- ── Navigation ──────────────────────────────────────────── -->
<nav class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php">
            <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            Edu<span>Track</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>

        <ul class="nav-links" id="navLinks">
            <li>
                <a href="index.php" class="<?= $activePage === 'index' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Students
                </a>
            </li>
            <li>
                <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="add-edit.php" class="<?= $activePage === 'add' ? 'active' : '' ?>">
                    <i class="bi bi-person-plus"></i> Add Student
                </a>
            </li>
            <li>
                <a href="about-project.php" class="<?= $activePage === 'about-project' ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i> About Project
                </a>
            </li>
            <li>
                <a href="about-developers.php" class="<?= $activePage === 'about-developers' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Developers
                </a>
            </li>
            <!-- Mobile-only logout -->
            <li style="border-top:1px solid var(--border); margin-top:4px; padding-top:4px;">
                <a href="change-password.php">
                    <i class="bi bi-key"></i> Change Password
                </a>
            </li>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li>
                <a href="manage-users.php">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="logout.php" style="color:#d93025;">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </li>
        </ul>

        <!-- User Avatar Dropdown (desktop) -->
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="nav-user" id="navUser">
            <button class="nav-user-btn" id="navUserBtn" aria-expanded="false" aria-haspopup="true">
                <div class="nav-avatar"><?= htmlspecialchars($authInitial) ?></div>
                <span class="nav-user-name"><?= htmlspecialchars($authUser) ?></span>
                <i class="bi bi-chevron-down nav-caret"></i>
            </button>

            <div class="nav-dropdown" id="navDropdown" role="menu">
                <div class="nav-dropdown-header">
                    <div class="dd-name"><?= htmlspecialchars($authUser) ?></div>
                    <div class="dd-role">
                        <i class="bi bi-<?= $authRole === 'admin' ? 'shield-fill' : 'person' ?>"></i>
                        <?= ucfirst(htmlspecialchars($authRole)) ?>
                        &bull; @<?= htmlspecialchars($authUsername) ?>
                    </div>
                </div>

                <?php if ($authRole === 'admin'): ?>
                <a href="register.php">
                    <i class="bi bi-person-plus"></i> Create New User
                </a>
                <a href="manage-users.php">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <?php endif; ?>

                <a href="change-password.php">
                    <i class="bi bi-key"></i> Change Password
                </a>

                <a href="logout.php" class="dd-logout">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</nav>

<script>
    // Mobile nav toggle
    document.getElementById('navToggle').addEventListener('click', function () {
        document.getElementById('navLinks').classList.toggle('open');
    });

    // User dropdown toggle
    const navUserBtn = document.getElementById('navUserBtn');
    const navUser    = document.getElementById('navUser');

    if (navUserBtn) {
        navUserBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navUser.classList.toggle('open');
            navUserBtn.setAttribute('aria-expanded', navUser.classList.contains('open'));
        });

        // Close when clicking outside
        document.addEventListener('click', function () {
            navUser.classList.remove('open');
            navUserBtn.setAttribute('aria-expanded', 'false');
        });
    }
</script>
