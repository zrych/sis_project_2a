<?php
// ============================================================
//  index.php — Student Records (Read + Delete entry point)
// ============================================================
require_once 'auth.php';
require_once 'db.php';

// ── Flash message from session ────────────────────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Search / filter ───────────────────────────────────────
$search      = trim($_GET['search'] ?? '');
$filterYear  = $_GET['year'] ?? '';
$filterCourse = $_GET['course'] ?? '';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(full_name LIKE ? OR student_id LIKE ? OR email LIKE ? OR course LIKE ?)';
    $like     = "%{$search}%";
    $params   = array_merge($params, [$like, $like, $like, $like]);
    $types   .= 'ssss';
}

if ($filterYear !== '' && in_array($filterYear, ['1','2','3','4'])) {
    $where[]  = 'year_level = ?';
    $params[] = (int)$filterYear;
    $types   .= 'i';
}

if ($filterCourse !== '') {
    $where[]  = 'course = ?';
    $params[] = $filterCourse;
    $types   .= 's';
}

$sql = 'SELECT * FROM students';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC';

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);
$count    = count($students);

// ── Distinct courses for filter ───────────────────────────
$cRes    = mysqli_query($conn, 'SELECT DISTINCT course FROM students ORDER BY course');
$courses = mysqli_fetch_all($cRes, MYSQLI_ASSOC);

// ── Helpers ───────────────────────────────────────────────
function yearLabel(int $y): string {
    return ['', '1st Year', '2nd Year', '3rd Year', '4th Year'][$y] ?? $y;
}

function yearBadge(int $y): string {
    $cls = ['', 'y1', 'y2', 'y3', 'y4'][$y] ?? 'y1';
    return '<span class="badge-year ' . $cls . '">' . yearLabel($y) . '</span>';
}

function gpaBadge(float $gpa): string {
    if ($gpa <= 1.50)      $cls = 'gpa-excellent';
    elseif ($gpa <= 2.00)  $cls = 'gpa-good';
    elseif ($gpa <= 3.00)  $cls = 'gpa-average';
    else                   $cls = 'gpa-poor';
    return '<span class="badge-gpa ' . $cls . '">' . number_format($gpa, 2) . '</span>';
}

// ── Page vars ─────────────────────────────────────────────
$pageTitle  = 'Student Records';
$activePage = 'index';
include 'header.php';
?>

<div class="page-wrapper">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>Student Records</h1>
            <p>Manage all enrolled students &mdash; view, edit, or remove entries.</p>
        </div>
        <a href="add-edit.php" class="btn-green">
            <i class="bi bi-person-plus-fill"></i> Add New Student
        </a>
    </div>

    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="alert-sis <?= htmlspecialchars($flash['type']) ?>">
            <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Filter / Search Card -->
    <div class="card-sis mb-4">
        <div class="card-sis-body">
            <form method="GET" action="index.php" class="d-flex flex-wrap gap-3 align-items-flex-end">
                <!-- Search -->
                <div class="search-wrap">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Search name, ID, email, course…"
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <!-- Year Level filter -->
                <div>
                    <select name="year" class="form-select-sis" style="min-width:140px;">
                        <option value="">All Year Levels</option>
                        <?php for ($y = 1; $y <= 4; $y++): ?>
                            <option value="<?= $y ?>" <?= $filterYear == $y ? 'selected' : '' ?>>
                                <?= yearLabel($y) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Course filter -->
                <div>
                    <select name="course" class="form-select-sis" style="min-width:200px;">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= htmlspecialchars($c['course']) ?>"
                                <?= $filterCourse === $c['course'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['course']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-green">
                    <i class="bi bi-funnel"></i> Filter
                </button>

                <?php if ($search || $filterYear || $filterCourse): ?>
                    <a href="index.php" class="btn-outline-sis">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card-sis">
        <div class="card-sis-header">
            <div>
                <strong><?= $count ?></strong>
                <span style="color:var(--text-muted); font-size:.875rem;">
                    student<?= $count !== 1 ? 's' : '' ?> found
                </span>
            </div>
        </div>

        <?php if ($count === 0): ?>
            <div class="empty-state">
                <i class="bi bi-people" style="color:var(--green-mid);"></i>
                <p>No student records found.</p>
                <?php if ($search || $filterYear || $filterCourse): ?>
                    <a href="index.php" style="color:var(--green); font-size:.875rem;">Clear filters</a>
                <?php else: ?>
                    <a href="add-edit.php" class="btn-green mt-3 d-inline-flex">
                        <i class="bi bi-person-plus-fill"></i> Add First Student
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive-sis">
                <table class="table-sis">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Contact</th>
                            <th>GPA</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): ?>
                            <tr>
                                <td class="muted"><?= $i + 1 ?></td>
                                <td>
                                    <div class="student-name"><?= htmlspecialchars($s['full_name']) ?></div>
                                    <div class="student-id-tag"><?= htmlspecialchars($s['student_id']) ?></div>
                                </td>
                                <td class="muted"><?= htmlspecialchars($s['email']) ?></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td><?= yearBadge((int)$s['year_level']) ?></td>
                                <td class="muted"><?= htmlspecialchars($s['contact_no']) ?></td>
                                <td><?= gpaBadge((float)$s['gpa']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <!-- View -->
                                        <a href="view.php?id=<?= $s['id'] ?>"
                                           class="btn-icon view" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="add-edit.php?id=<?= $s['id'] ?>"
                                           class="btn-icon edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Delete (triggers modal) -->
                                        <button
                                            class="btn-icon delete"
                                            title="Delete"
                                            onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Delete Confirmation Modal ──────────────────────────── -->
<div class="modal fade modal-sis" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; color:var(--text-primary);">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#d93025;"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.9rem; color:var(--text-secondary);">
                Are you sure you want to delete <strong id="deleteStudentName" style="color:var(--text-primary);"></strong>?
                <br><br>This action <strong>cannot be undone.</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-sis" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteConfirmBtn" href="#"
                   style="background:#d93025; color:#fff; border:none; padding:9px 20px;
                          border-radius:999px; font-size:.875rem; font-weight:600;
                          display:inline-flex; align-items:center; gap:7px; transition:all .2s;"
                   onmouseover="this.style.background='#b31412'"
                   onmouseout="this.style.background='#d93025'">
                    <i class="bi bi-trash3-fill"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteStudentName').textContent = name;
        document.getElementById('deleteConfirmBtn').href = 'delete.php?id=' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php include 'footer.php'; ?>
