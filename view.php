<?php
// ============================================================
//  view.php — Student Profile (Read-only detail page)
// ============================================================
require_once 'auth.php';
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM students WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res     = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($res);

if (!$student) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Student record not found.'];
    header('Location: index.php');
    exit;
}

$yearLabels = ['', '1st Year', '2nd Year', '3rd Year', '4th Year'];

function gpaClass(float $gpa): string {
    if ($gpa <= 1.50) return 'gpa-excellent';
    if ($gpa <= 2.00) return 'gpa-good';
    if ($gpa <= 3.00) return 'gpa-average';
    return 'gpa-poor';
}

function gpaLabel(float $gpa): string {
    if ($gpa <= 1.50) return 'Excellent';
    if ($gpa <= 2.00) return 'Good';
    if ($gpa <= 3.00) return 'Average';
    return 'Needs Improvement';
}

$pageTitle  = htmlspecialchars($student['full_name']);
$activePage = 'index';
include 'header.php';
?>

<div class="page-wrapper" style="max-width:820px;">

    <!-- Breadcrumb -->
    <nav style="font-size:.8125rem; color:var(--text-muted); margin-bottom:16px;">
        <a href="index.php" style="color:var(--green);">Students</a>
        <span style="margin:0 6px;">/</span>
        <?= htmlspecialchars($student['full_name']) ?>
    </nav>

    <!-- Profile Header -->
    <div class="card-sis mb-4" style="overflow:visible;">
        <div style="background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
                    height: 100px; border-radius: var(--radius) var(--radius) 0 0;"></div>
        <div style="padding: 0 28px 24px; position:relative;">
            <!-- Avatar -->
            <div style="width:80px; height:80px; background:var(--surface); border:4px solid var(--surface);
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        position:absolute; top:-40px; box-shadow: var(--shadow-md);">
                <div style="width:72px; height:72px; background:var(--green-light); border-radius:50%;
                            display:flex; align-items:center; justify-content:center;
                            font-size:28px; color:var(--green);">
                    <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                </div>
            </div>

            <div style="padding-top:52px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div>
                    <h2 style="font-size:1.6rem; margin:0 0 4px;">
                        <?= htmlspecialchars($student['full_name']) ?>
                    </h2>
                    <p style="margin:0; color:var(--text-secondary); font-size:.9rem;">
                        <span style="font-weight:600; color:var(--green);">
                            <?= htmlspecialchars($student['student_id']) ?>
                        </span>
                        &bull; <?= htmlspecialchars($student['course']) ?>
                        &bull; <?= $yearLabels[(int)$student['year_level']] ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="add-edit.php?id=<?= $student['id'] ?>" class="btn-green">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button class="btn-outline-sis"
                            onclick="confirmDelete(<?= $student['id'] ?>, '<?= htmlspecialchars(addslashes($student['full_name'])) ?>')"
                            style="color:#d93025; border-color:#d93025;">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-sis h-100">
                <div class="card-sis-header">
                    <strong style="font-size:.9rem;">
                        <i class="bi bi-person" style="color:var(--green); margin-right:6px;"></i>
                        Contact Details
                    </strong>
                </div>
                <div class="card-sis-body">
                    <?php
                    $items = [
                        ['bi bi-envelope', 'Email',   $student['email']],
                        ['bi bi-telephone','Phone',   $student['contact_no']],
                        ['bi bi-geo-alt',  'Address', $student['address']],
                    ];
                    foreach ($items as [$icon, $label, $value]): ?>
                        <div style="display:flex; gap:12px; margin-bottom:18px;">
                            <div style="width:36px; height:36px; background:var(--green-light);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; flex-shrink:0; color:var(--green);">
                                <i class="<?= $icon ?>"></i>
                            </div>
                            <div>
                                <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em;
                                            font-weight:600; color:var(--text-muted); margin-bottom:2px;">
                                    <?= $label ?>
                                </div>
                                <div style="font-size:.9rem; color:var(--text-primary);">
                                    <?= htmlspecialchars($value) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-sis h-100">
                <div class="card-sis-header">
                    <strong style="font-size:.9rem;">
                        <i class="bi bi-mortarboard" style="color:var(--green); margin-right:6px;"></i>
                        Academic Details
                    </strong>
                </div>
                <div class="card-sis-body">
                    <!-- GPA Highlight -->
                    <div style="text-align:center; padding:16px 0 24px; border-bottom:1px solid var(--border); margin-bottom:20px;">
                        <div style="font-size:3rem; font-family:'DM Serif Display',serif;
                                    color:var(--green); line-height:1;">
                            <?= number_format((float)$student['gpa'], 2) ?>
                        </div>
                        <div style="font-size:.8rem; color:var(--text-muted); margin-top:4px;">
                            Grade Point Average
                        </div>
                        <span class="badge-gpa <?= gpaClass((float)$student['gpa']) ?> mt-2">
                            <?= gpaLabel((float)$student['gpa']) ?>
                        </span>
                    </div>

                    <?php
                    $yearCls = ['','y1','y2','y3','y4'];
                    $acadItems = [
                        ['bi bi-book', 'Course',     $student['course']],
                        ['bi bi-calendar3','Year Level', $yearLabels[(int)$student['year_level']]],
                    ];
                    foreach ($acadItems as [$icon, $label, $value]): ?>
                        <div style="display:flex; gap:12px; margin-bottom:16px;">
                            <div style="width:36px; height:36px; background:var(--green-light);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; flex-shrink:0; color:var(--green);">
                                <i class="<?= $icon ?>"></i>
                            </div>
                            <div>
                                <div style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em;
                                            font-weight:600; color:var(--text-muted); margin-bottom:2px;">
                                    <?= $label ?>
                                </div>
                                <div style="font-size:.9rem; color:var(--text-primary);">
                                    <?= htmlspecialchars($value) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Meta -->
    <div style="text-align:right; margin-top:16px; font-size:.78rem; color:var(--text-muted);">
        Created: <?= date('M j, Y g:i A', strtotime($student['created_at'])) ?>
        &bull; Last updated: <?= date('M j, Y g:i A', strtotime($student['updated_at'])) ?>
    </div>
</div>

<!-- Delete Modal -->
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
                          display:inline-flex; align-items:center; gap:7px;">
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
