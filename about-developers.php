<?php
// ============================================================
//  about-developers.php — Meet the team
//  ← Edit $developers array to match your actual team.
// ============================================================
require_once 'auth.php';
$pageTitle  = 'About the Developers';
$activePage = 'about-developers';

// ── Team members — edit as needed ─────────────────────────
$developers = [
    [
        'name'    => 'Your Name Here',
        'role'    => 'Lead Developer',
        'contrib' => 'System architecture, PHP backend, database design, and CRUD implementation.',
        'initials'=> 'YN',
        'color'   => '#34A853',   // green
        'links'   => [
            'github'   => '#',
            'linkedin' => '#',
            'email'    => 'dev@school.edu',
        ],
    ],
    [
        'name'    => 'Team Member 2',
        'role'    => 'Frontend Developer',
        'contrib' => 'UI/UX design, Bootstrap layout, responsive design, and dashboard charts.',
        'initials'=> 'TM',
        'color'   => '#1a73e8',   // blue
        'links'   => [
            'github'   => '#',
            'linkedin' => '#',
            'email'    => 'dev2@school.edu',
        ],
    ],
    [
        'name'    => 'Team Member 3',
        'role'    => 'Database Administrator',
        'contrib' => 'MySQL schema design, query optimisation, data seeding, and validation logic.',
        'initials'=> 'TM',
        'color'   => '#f4a532',   // orange
        'links'   => [
            'github'   => '#',
            'linkedin' => '#',
            'email'    => 'dev3@school.edu',
        ],
    ],
    [
        'name'    => 'Team Member 4',
        'role'    => 'QA & Documentation',
        'contrib' => 'Testing, bug reporting, writing project documentation and user guides.',
        'initials'=> 'TM',
        'color'   => '#d93025',   // red
        'links'   => [
            'github'   => '#',
            'linkedin' => '#',
            'email'    => 'dev4@school.edu',
        ],
    ],
];

include 'header.php';
?>

<div class="page-wrapper" style="max-width:1000px;">

    <!-- Page Header -->
    <div class="page-header" style="text-align:center; max-width:560px; margin:0 auto 40px;">
        <h1>About the Developers</h1>
        <p>Meet the team behind EduTrack — a group of students passionate about building practical web applications.</p>
    </div>

    <!-- Team Cards -->
    <div class="row g-4 mb-5">
        <?php foreach ($developers as $dev): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card-sis h-100" style="text-align:center; overflow:visible; position:relative;">

                    <!-- Color accent bar -->
                    <div style="height:6px; background:<?= $dev['color'] ?>; border-radius:var(--radius) var(--radius) 0 0;"></div>

                    <div style="padding:32px 20px 24px;">
                        <!-- Avatar -->
                        <div style="width:72px; height:72px; border-radius:50%;
                                    background:<?= $dev['color'] ?>22;
                                    border: 3px solid <?= $dev['color'] ?>;
                                    display:flex; align-items:center; justify-content:center;
                                    margin:0 auto 14px;
                                    font-family:'DM Serif Display',serif;
                                    font-size:1.5rem; color:<?= $dev['color'] ?>;">
                            <?= htmlspecialchars($dev['initials']) ?>
                        </div>

                        <!-- Name & role -->
                        <h3 style="font-size:1.05rem; margin:0 0 4px;">
                            <?= htmlspecialchars($dev['name']) ?>
                        </h3>
                        <span style="display:inline-block; padding:3px 12px; border-radius:999px;
                                     background:<?= $dev['color'] ?>18; color:<?= $dev['color'] ?>;
                                     font-size:.72rem; font-weight:700; text-transform:uppercase;
                                     letter-spacing:.05em; margin-bottom:14px;">
                            <?= htmlspecialchars($dev['role']) ?>
                        </span>

                        <!-- Contribution -->
                        <p style="font-size:.8125rem; color:var(--text-secondary); line-height:1.6; margin:0 0 20px;">
                            <?= htmlspecialchars($dev['contrib']) ?>
                        </p>

                        <!-- Social links -->
                        <div style="display:flex; justify-content:center; gap:8px;">
                            <?php if ($dev['links']['github'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($dev['links']['github']) ?>"
                               target="_blank"
                               title="GitHub"
                               style="width:34px; height:34px; border-radius:8px; background:var(--surface-2);
                                      border:1px solid var(--border); display:flex; align-items:center;
                                      justify-content:center; color:var(--text-secondary); transition:var(--transition);"
                               onmouseover="this.style.background='#24292e'; this.style.color='#fff';"
                               onmouseout="this.style.background='var(--surface-2)'; this.style.color='var(--text-secondary)';">
                                <i class="bi bi-github"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($dev['links']['linkedin'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($dev['links']['linkedin']) ?>"
                               target="_blank"
                               title="LinkedIn"
                               style="width:34px; height:34px; border-radius:8px; background:var(--surface-2);
                                      border:1px solid var(--border); display:flex; align-items:center;
                                      justify-content:center; color:var(--text-secondary); transition:var(--transition);"
                               onmouseover="this.style.background='#0A66C2'; this.style.color='#fff';"
                               onmouseout="this.style.background='var(--surface-2)'; this.style.color='var(--text-secondary)';">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <?php endif; ?>

                            <a href="mailto:<?= htmlspecialchars($dev['links']['email']) ?>"
                               title="Send email"
                               style="width:34px; height:34px; border-radius:8px; background:var(--surface-2);
                                      border:1px solid var(--border); display:flex; align-items:center;
                                      justify-content:center; color:var(--text-secondary); transition:var(--transition);"
                               onmouseover="this.style.background='<?= $dev['color'] ?>'; this.style.color='#fff';"
                               onmouseout="this.style.background='var(--surface-2)'; this.style.color='var(--text-secondary)';">
                                <i class="bi bi-envelope"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Project Info Block -->
    <div class="card-sis mb-4" style="background: linear-gradient(135deg, var(--green-light) 0%, #fff 60%);">
        <div class="card-sis-body" style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
            <div style="font-size:3rem; color:var(--green); line-height:1; flex-shrink:0;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div style="flex:1; min-width:200px;">
                <h3 style="font-size:1.1rem; margin:0 0 6px;">
                    EduTrack — Student Information System
                </h3>
                <p style="font-size:.875rem; color:var(--text-secondary); margin:0;">
                    Developed as a final project requirement demonstrating PHP &amp; MySQL integration,
                    full CRUD operations, server-side validation, and a responsive Bootstrap UI.
                </p>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:.75rem; text-transform:uppercase; letter-spacing:.06em;
                            color:var(--text-muted); font-weight:600; margin-bottom:4px;">
                    Academic Year
                </div>
                <div style="font-size:1.2rem; font-family:'DM Serif Display',serif; color:var(--text-primary);">
                    <?= date('Y') ?>–<?= date('Y') + 1 ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acknowledgements -->
    <div class="card-sis">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-heart-fill" style="color:var(--green); margin-right:6px;"></i>
                Acknowledgements
            </strong>
        </div>
        <div class="card-sis-body" style="font-size:.9rem; color:var(--text-secondary); line-height:1.8;">
            <p>
                We extend our sincere gratitude to our instructor for the guidance provided throughout
                the course, and to the open-source community whose tools made this project possible —
                particularly the teams behind PHP, MySQL, Bootstrap, Chart.js, and Bootstrap Icons.
            </p>
            <p style="margin-bottom:0;">
                This project was made with ☕ and a lot of debugging sessions.
            </p>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
