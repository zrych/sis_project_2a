<?php
// ============================================================
//  about-project.php — Project overview & documentation
// ============================================================
require_once 'auth.php';
$pageTitle  = 'About the Project';
$activePage = 'about-project';
include 'header.php';
?>

<div class="page-wrapper" style="max-width:860px;">

    <!-- Page Header -->
    <div class="page-header">
        <h1>About the Project</h1>
        <p>An overview of EduTrack's purpose, architecture, and feature set.</p>
    </div>

    <!-- Hero Banner -->
    <div style="background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
                border-radius: var(--radius); padding: 40px 48px; color: #fff; margin-bottom: 32px;
                display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
        <div style="font-size:3.5rem; line-height:1;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <h2 style="font-size:2rem; margin:0 0 8px; color:#fff;">EduTrack SIS</h2>
            <p style="margin:0; opacity:.9; font-size:.95rem; max-width:520px;">
                A web-based Student Information System built with PHP and MySQL,
                enabling institutions to efficiently manage student records through
                a clean, intuitive interface.
            </p>
        </div>
    </div>

    <!-- Project Overview -->
    <div class="card-sis mb-4">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-info-circle" style="color:var(--green); margin-right:6px;"></i>
                Project Overview
            </strong>
        </div>
        <div class="card-sis-body" style="line-height:1.75; color:var(--text-secondary); font-size:.925rem;">
            <p>
                <strong style="color:var(--text-primary);">EduTrack</strong> is a dynamic Student Information System
                designed as a hands-on learning project that demonstrates the fundamentals of full-stack web
                development using the PHP + MySQL stack. The system provides a complete workflow for managing
                student enrollment data, from initial registration all the way through record maintenance and
                performance tracking.
            </p>
            <p style="margin-bottom:0;">
                The system was developed with a focus on clean architecture, input validation, and a modern
                Google-inspired UI — proving that academic projects can be both functionally solid and
                visually polished.
            </p>
        </div>
    </div>

    <!-- Objectives -->
    <div class="card-sis mb-4">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-bullseye" style="color:var(--green); margin-right:6px;"></i>
                Objectives
            </strong>
        </div>
        <div class="card-sis-body">
            <?php
            $objectives = [
                ['bi bi-database-fill',         'Database Connectivity',   'Apply PHP MySQLi to connect, query, and manipulate a structured MySQL database.'],
                ['bi bi-arrow-repeat',           'Full CRUD Operations',    'Implement Create, Read, Update, and Delete operations for student records.'],
                ['bi bi-ui-checks-grid',         'Form Handling',           'Accept and process user input via HTML forms with server-side validation.'],
                ['bi bi-table',                  'Tabular Data Display',    'Retrieve and display records in an organized, searchable, and filterable table.'],
                ['bi bi-shield-check',           'Input Validation',        'Enforce data integrity through both client-side hints and server-side validation logic.'],
                ['bi bi-phone',                  'Responsive UI',           'Design a fully responsive interface using Bootstrap 5 and custom CSS.'],
            ];
            foreach ($objectives as [$icon, $title, $desc]): ?>
                <div style="display:flex; gap:14px; margin-bottom:20px; align-items:flex-start;">
                    <div style="width:40px; height:40px; background:var(--green-light); border-radius:10px;
                                display:flex; align-items:center; justify-content:center;
                                color:var(--green); font-size:1.1rem; flex-shrink:0;">
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:.9rem; color:var(--text-primary); margin-bottom:3px;">
                            <?= $title ?>
                        </div>
                        <div style="font-size:.875rem; color:var(--text-secondary); line-height:1.6;">
                            <?= $desc ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Features -->
    <div class="card-sis mb-4">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-stars" style="color:var(--green); margin-right:6px;"></i>
                Key Features
            </strong>
        </div>
        <div class="card-sis-body">
            <div class="row g-3">
                <?php
                $features = [
                    ['bi bi-person-plus-fill',  'Student Registration',   'Register new students with all essential academic and personal details.'],
                    ['bi bi-pencil-square',     'Record Editing',         'Update any student\'s information with a pre-populated form.'],
                    ['bi bi-trash3-fill',        'Safe Deletion',          'Remove records via a confirmation modal to prevent accidental deletions.'],
                    ['bi bi-search',             'Search & Filter',        'Filter the records table by name, ID, course, or year level instantly.'],
                    ['bi bi-bar-chart-line-fill','Analytics Dashboard',    'Visualise enrollment trends and GPA distribution with interactive charts.'],
                    ['bi bi-eye-fill',           'Student Profile View',   'View a full profile card for each student with all stored information.'],
                    ['bi bi-check2-circle',      'Server-side Validation', 'All inputs are validated before any database write, with inline error feedback.'],
                    ['bi bi-moon-stars-fill',    'Flash Notifications',    'Session-based flash messages confirm every create, update, and delete action.'],
                ];
                foreach ($features as [$icon, $title, $desc]): ?>
                    <div class="col-md-6">
                        <div style="display:flex; gap:10px; padding:14px; border:1px solid var(--border);
                                    border-radius:var(--radius-sm); height:100%; background:var(--surface-2);">
                            <i class="<?= $icon ?>" style="color:var(--green); font-size:1.2rem; margin-top:2px; flex-shrink:0;"></i>
                            <div>
                                <div style="font-weight:600; font-size:.875rem; color:var(--text-primary); margin-bottom:3px;">
                                    <?= $title ?>
                                </div>
                                <div style="font-size:.8125rem; color:var(--text-secondary); line-height:1.5;">
                                    <?= $desc ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tech Stack -->
    <div class="card-sis mb-4">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-layers" style="color:var(--green); margin-right:6px;"></i>
                Technology Stack
            </strong>
        </div>
        <div class="card-sis-body">
            <div class="row g-3">
                <?php
                $stack = [
                    ['#777BB4', 'bi-filetype-php',    'PHP 8+',           'Server-side scripting, CRUD logic, session handling, and form processing.'],
                    ['#4479A1', 'bi-database',        'MySQL 8',           'Relational database management with indexed queries and referential integrity.'],
                    ['#7952B3', 'bi-bootstrap',       'Bootstrap 5.3',     'Responsive grid system, utility classes, and modal components.'],
                    ['#F7DF1E', 'bi-filetype-js',     'Vanilla JS',        'Client-side interactions: nav toggle, modal triggers, and Chart.js rendering.'],
                    ['#FF6384', 'bi-graph-up',        'Chart.js 4',        'Interactive bar, doughnut, and horizontal bar charts on the dashboard.'],
                    ['#4285F4', 'bi-fonts',           'Google Fonts',      'DM Serif Display + Plus Jakarta Sans for distinctive, refined typography.'],
                ];
                foreach ($stack as [$color, $icon, $name, $desc]): ?>
                    <div class="col-md-6">
                        <div style="display:flex; gap:12px; align-items:flex-start; padding:14px;
                                    border:1px solid var(--border); border-radius:var(--radius-sm);
                                    background:var(--surface-2);">
                            <div style="width:38px; height:38px; background:<?= $color ?>22;
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; color:<?= $color ?>; font-size:1.2rem; flex-shrink:0;">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <div>
                                <div style="font-weight:700; font-size:.875rem; color:var(--text-primary); margin-bottom:3px;">
                                    <?= $name ?>
                                </div>
                                <div style="font-size:.8rem; color:var(--text-secondary); line-height:1.5;">
                                    <?= $desc ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pages Overview -->
    <div class="card-sis">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-files" style="color:var(--green); margin-right:6px;"></i>
                Application Pages
            </strong>
        </div>
        <div class="card-sis-body">
            <?php
            $pages = [
                ['index.php',            'Student Records',      'Main listing page with search, filter, and CRUD action buttons for each record.'],
                ['dashboard.php',        'Dashboard',            'Statistics overview with stat cards and three Chart.js visualisations.'],
                ['add-edit.php',         'Add / Edit Student',   'Dual-mode form: add a new student or edit an existing one via ?id=N.'],
                ['view.php',             'Student Profile',      'Full read-only profile card for a single student including GPA highlights.'],
                ['about-project.php',    'About the Project',    'This page — project documentation, objectives, features, and tech stack.'],
                ['about-developers.php', 'About the Developers', 'Meet the team behind EduTrack with their roles and contributions.'],
            ];
            foreach ($pages as [$file, $name, $desc]): ?>
                <div style="display:flex; gap:14px; align-items:flex-start; margin-bottom:16px;
                            padding-bottom:16px; border-bottom:1px solid var(--border);">
                    <code style="background:var(--green-light); color:var(--green-dark); padding:2px 8px;
                                 border-radius:4px; font-size:.78rem; white-space:nowrap; flex-shrink:0;
                                 align-self:center;">
                        <?= $file ?>
                    </code>
                    <div>
                        <div style="font-weight:600; font-size:.875rem; color:var(--text-primary); margin-bottom:3px;">
                            <?= $name ?>
                        </div>
                        <div style="font-size:.8rem; color:var(--text-secondary); line-height:1.5;">
                            <?= $desc ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
