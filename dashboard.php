<?php
// ============================================================
//  dashboard.php — Stats overview & charts
// ============================================================
require_once 'auth.php';
require_once 'db.php';

// ── Aggregate Stats ────────────────────────────────────────
$totalStudents = (int)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS n FROM students'))['n']);
$avgGpa        = (float)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT AVG(gpa) AS a FROM students'))['a'] ?? 0);
$bestGpa       = (float)(mysqli_fetch_assoc(mysqli_query($conn, 'SELECT MIN(gpa) AS m FROM students'))['m'] ?? 0); // lower = better PH system

// ── By Year Level ──────────────────────────────────────────
$yearRes = mysqli_query($conn,
    'SELECT year_level, COUNT(*) AS cnt FROM students GROUP BY year_level ORDER BY year_level');
$yearData = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
while ($r = mysqli_fetch_assoc($yearRes)) $yearData[(int)$r['year_level']] = (int)$r['cnt'];

// ── By Course ─────────────────────────────────────────────
$courseRes = mysqli_query($conn,
    'SELECT course, COUNT(*) AS cnt FROM students GROUP BY course ORDER BY cnt DESC');
$courseLabels = [];
$courseCounts = [];
while ($r = mysqli_fetch_assoc($courseRes)) {
    $courseLabels[] = $r['course'];
    $courseCounts[] = (int)$r['cnt'];
}

// ── GPA Distribution ──────────────────────────────────────
$gpaDist = ['Excellent (≤1.50)' => 0, 'Good (≤2.00)' => 0, 'Average (≤3.00)' => 0, 'Poor (>3.00)' => 0];
$gpaDistRes = mysqli_query($conn, 'SELECT gpa FROM students');
while ($r = mysqli_fetch_assoc($gpaDistRes)) {
    $g = (float)$r['gpa'];
    if ($g <= 1.50)      $gpaDist['Excellent (≤1.50)']++;
    elseif ($g <= 2.00)  $gpaDist['Good (≤2.00)']++;
    elseif ($g <= 3.00)  $gpaDist['Average (≤3.00)']++;
    else                 $gpaDist['Poor (>3.00)']++;
}

// ── Recent Students ───────────────────────────────────────
$recentRes  = mysqli_query($conn, 'SELECT * FROM students ORDER BY created_at DESC LIMIT 5');
$recent     = mysqli_fetch_all($recentRes, MYSQLI_ASSOC);

// ── JSON for charts ───────────────────────────────────────
$yearLabels   = json_encode(['1st Year','2nd Year','3rd Year','4th Year']);
$yearValues   = json_encode(array_values($yearData));
$cLabelsJson  = json_encode($courseLabels);
$cCountsJson  = json_encode($courseCounts);
$gpaKeys      = json_encode(array_keys($gpaDist));
$gpaVals      = json_encode(array_values($gpaDist));

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include 'header.php';
?>

<div class="page-wrapper">

    <!-- Page Header -->
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>An overview of enrollment statistics and academic performance.</p>
    </div>

    <!-- ── Stat Cards ──────────────────────────────────────── -->
    <div class="row g-4 mb-4">

        <!-- Total Students -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>

        <!-- Average GPA -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="stat-value"><?= number_format($avgGpa, 2) ?></div>
                    <div class="stat-label">Average GPA</div>
                </div>
            </div>
        </div>

        <!-- Courses offered -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-book-half"></i></div>
                <div>
                    <div class="stat-value"><?= count($courseLabels) ?></div>
                    <div class="stat-label">Programs Enrolled</div>
                </div>
            </div>
        </div>

        <!-- Top GPA -->
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-award-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $totalStudents > 0 ? number_format($bestGpa, 2) : '—' ?></div>
                    <div class="stat-label">Highest GPA (Lowest #)</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Charts Row ─────────────────────────────────────── -->
    <div class="row g-4 mb-4">

        <!-- Enrollment by Year Level — Bar -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><i class="bi bi-bar-chart-fill" style="color:var(--green); margin-right:8px;"></i>Enrollment by Year Level</h3>
                <canvas id="yearChart" height="220"></canvas>
            </div>
        </div>

        <!-- GPA Distribution — Doughnut -->
        <div class="col-lg-6">
            <div class="chart-card">
                <h3><i class="bi bi-pie-chart-fill" style="color:var(--green); margin-right:8px;"></i>GPA Distribution</h3>
                <canvas id="gpaChart" height="220"></canvas>
            </div>
        </div>

    </div>

    <!-- ── Course Distribution — Horizontal Bar ──────────── -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="chart-card">
                <h3><i class="bi bi-mortarboard-fill" style="color:var(--green); margin-right:8px;"></i>Students per Program</h3>
                <canvas id="courseChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Recent Registrations ───────────────────────────── -->
    <div class="card-sis">
        <div class="card-sis-header">
            <strong style="font-size:.95rem;">
                <i class="bi bi-clock-history" style="color:var(--green); margin-right:6px;"></i>
                Recent Registrations
            </strong>
            <a href="index.php" style="font-size:.8125rem; color:var(--green);">View all →</a>
        </div>
        <?php if (empty($recent)): ?>
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <p>No students registered yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive-sis">
                <table class="table-sis">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>GPA</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $s):
                            $yearCls = ['','y1','y2','y3','y4'];
                            $yLabel  = ['','1st Year','2nd Year','3rd Year','4th Year'];
                            if ((float)$s['gpa'] <= 1.50)     $gClass = 'gpa-excellent';
                            elseif ((float)$s['gpa'] <= 2.00) $gClass = 'gpa-good';
                            elseif ((float)$s['gpa'] <= 3.00) $gClass = 'gpa-average';
                            else                               $gClass = 'gpa-poor';
                        ?>
                            <tr>
                                <td>
                                    <div class="student-name"><?= htmlspecialchars($s['full_name']) ?></div>
                                    <div class="student-id-tag"><?= htmlspecialchars($s['student_id']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td>
                                    <span class="badge-year <?= $yearCls[(int)$s['year_level']] ?>">
                                        <?= $yLabel[(int)$s['year_level']] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-gpa <?= $gClass ?>">
                                        <?= number_format((float)$s['gpa'], 2) ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-muted); font-size:.8125rem;">
                                    <?= date('M j, Y', strtotime($s['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const GREEN      = '#34A853';
const GREEN_DARK = '#1e8e3e';
const BLUE       = '#1a73e8';
const ORANGE     = '#f4a532';
const RED        = '#d93025';
const MUTED      = '#e8eaed';
const TEXT       = '#5f6368';

Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color       = TEXT;

// ── Bar: Year Level ───────────────────────────────────────
new Chart(document.getElementById('yearChart'), {
    type: 'bar',
    data: {
        labels: <?= $yearLabels ?>,
        datasets: [{
            label: 'Students',
            data: <?= $yearValues ?>,
            backgroundColor: [
                'rgba(26,115,232,.85)',
                'rgba(52,168,83,.85)',
                'rgba(244,165,50,.85)',
                'rgba(217,48,37,.85)'
            ],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} student${ctx.parsed.y !== 1 ? 's' : ''}` } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f3f4' } },
            x: { grid: { display: false } }
        }
    }
});

// ── Doughnut: GPA Distribution ────────────────────────────
new Chart(document.getElementById('gpaChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $gpaKeys ?>,
        datasets: [{
            data: <?= $gpaVals ?>,
            backgroundColor: ['#34A853', '#1a73e8', '#f4a532', '#d93025'],
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'right',
                labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' }
            }
        }
    }
});

// ── Horizontal Bar: Course ─────────────────────────────────
new Chart(document.getElementById('courseChart'), {
    type: 'bar',
    data: {
        labels: <?= $cLabelsJson ?>,
        datasets: [{
            label: 'Students',
            data: <?= $cCountsJson ?>,
            backgroundColor: 'rgba(52,168,83,.8)',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} student${ctx.parsed.x !== 1 ? 's' : ''}` } }
        },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f3f4' } },
            y: { grid: { display: false } }
        }
    }
});
</script>

<?php include 'footer.php'; ?>
