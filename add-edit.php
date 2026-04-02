<?php
// ============================================================
//  add-edit.php — Create & Update student records
//  ?id=N  → edit mode   |  no id → add mode
// ============================================================
require_once 'auth.php';
require_once 'db.php';

$isEdit   = false;
$student  = [];
$errors   = [];
$id       = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

// ── Load existing record if editing ───────────────────────
if ($id > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM students WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($res);
    if (!$student) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Student record not found.'];
        header('Location: index.php');
        exit;
    }
    $isEdit = true;
}

// ── Process form submission ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitise inputs
    $f = [
        'student_id'  => trim($_POST['student_id']  ?? ''),
        'full_name'   => trim($_POST['full_name']    ?? ''),
        'email'       => trim($_POST['email']        ?? ''),
        'course'      => trim($_POST['course']       ?? ''),
        'year_level'  => (int)($_POST['year_level']  ?? 0),
        'contact_no'  => trim($_POST['contact_no']   ?? ''),
        'address'     => trim($_POST['address']      ?? ''),
        'gpa'         => trim($_POST['gpa']          ?? ''),
    ];

    // ── Validation ────────────────────────────────────────
    if ($f['student_id'] === '')
        $errors['student_id'] = 'Student ID is required.';
    elseif (!preg_match('/^\d{4}-\d{4}$/', $f['student_id']))
        $errors['student_id'] = 'Format must be YYYY-NNNN (e.g. 2024-0001).';

    if ($f['full_name'] === '')
        $errors['full_name'] = 'Full name is required.';
    elseif (strlen($f['full_name']) < 2)
        $errors['full_name'] = 'Name must be at least 2 characters.';

    if ($f['email'] === '')
        $errors['email'] = 'Email is required.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Please enter a valid email address.';

    if ($f['course'] === '')
        $errors['course'] = 'Course / Program is required.';

    if (!in_array($f['year_level'], [1, 2, 3, 4]))
        $errors['year_level'] = 'Please select a valid year level.';

    if ($f['contact_no'] === '')
        $errors['contact_no'] = 'Contact number is required.';
    elseif (!preg_match('/^(\+63|0)[0-9]{9,10}$/', preg_replace('/\s+/', '', $f['contact_no'])))
        $errors['contact_no'] = 'Enter a valid PH contact number (e.g. 09171234567).';

    if ($f['address'] === '')
        $errors['address'] = 'Address is required.';

    if ($f['gpa'] === '')
        $errors['gpa'] = 'GPA is required.';
    else {
        $gpaVal = (float)$f['gpa'];
        if ($gpaVal < 1.00 || $gpaVal > 5.00)
            $errors['gpa'] = 'GPA must be between 1.00 and 5.00.';
    }

    // Unique checks (excluding self when editing)
    if (empty($errors['student_id'])) {
        $chk  = mysqli_prepare($conn, 'SELECT id FROM students WHERE student_id = ? AND id != ?');
        $excl = $isEdit ? $id : 0;
        mysqli_stmt_bind_param($chk, 'si', $f['student_id'], $excl);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0)
            $errors['student_id'] = 'This Student ID is already taken.';
    }

    if (empty($errors['email'])) {
        $chk  = mysqli_prepare($conn, 'SELECT id FROM students WHERE email = ? AND id != ?');
        $excl = $isEdit ? $id : 0;
        mysqli_stmt_bind_param($chk, 'si', $f['email'], $excl);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0)
            $errors['email'] = 'This email is already registered to another student.';
    }

    // ── Save if no errors ─────────────────────────────────
    if (empty($errors)) {
        $gpaVal = number_format((float)$f['gpa'], 2, '.', '');

        if ($isEdit) {
            $upd = mysqli_prepare($conn,
                'UPDATE students
                 SET student_id=?, full_name=?, email=?, course=?,
                     year_level=?, contact_no=?, address=?, gpa=?
                 WHERE id=?'
            );
            mysqli_stmt_bind_param($upd, 'ssssissdsi',
                $f['student_id'], $f['full_name'], $f['email'], $f['course'],
                $f['year_level'], $f['contact_no'], $f['address'], $gpaVal, $id
            );
            // Fix: correct types string
            $upd = mysqli_prepare($conn,
                'UPDATE students
                 SET student_id=?, full_name=?, email=?, course=?,
                     year_level=?, contact_no=?, address=?, gpa=?
                 WHERE id=?'
            );
            mysqli_stmt_bind_param($upd, 'ssssissdi',
                $f['student_id'], $f['full_name'], $f['email'], $f['course'],
                $f['year_level'], $f['contact_no'], $f['address'], $gpaVal, $id
            );
            mysqli_stmt_execute($upd);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "✓ {$f['full_name']}'s record has been updated successfully."
            ];
        } else {
            $ins = mysqli_prepare($conn,
                'INSERT INTO students (student_id, full_name, email, course, year_level, contact_no, address, gpa)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param($ins, 'ssssissd',
                $f['student_id'], $f['full_name'], $f['email'], $f['course'],
                $f['year_level'], $f['contact_no'], $f['address'], $gpaVal
            );
            mysqli_stmt_execute($ins);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "✓ {$f['full_name']} has been added successfully."
            ];
        }

        header('Location: index.php');
        exit;
    }

    // Re-populate fields on validation failure
    $student = $f;
}

// ── Helpers ───────────────────────────────────────────────
function old(string $key, array $student, string $default = ''): string {
    return htmlspecialchars($student[$key] ?? $default);
}

function errClass(string $key, array $errors): string {
    return isset($errors[$key]) ? ' is-invalid' : '';
}

// ── Page vars ─────────────────────────────────────────────
$pageTitle  = $isEdit ? 'Edit Student' : 'Add New Student';
$activePage = 'add';
include 'header.php';
?>

<div class="page-wrapper" style="max-width:820px;">

    <!-- Breadcrumb -->
    <nav style="font-size:.8125rem; color:var(--text-muted); margin-bottom:16px;">
        <a href="index.php" style="color:var(--green);">Students</a>
        <span style="margin:0 6px;">/</span>
        <?= $isEdit ? 'Edit Student' : 'Add New Student' ?>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><?= $isEdit ? 'Edit Student Record' : 'Add New Student' ?></h1>
        <p><?= $isEdit ? 'Update the information below and save your changes.' : 'Fill in the form below to register a new student.' ?></p>
    </div>

    <!-- Validation errors summary -->
    <?php if (!empty($errors)): ?>
        <div class="alert-sis error" style="align-items:flex-start; flex-direction:column; gap:6px;">
            <div style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <i class="bi bi-exclamation-circle-fill"></i>
                Please fix the following errors:
            </div>
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="add-edit.php" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>

        <div class="card-sis">
            <div class="card-sis-header">
                <strong style="font-size:.95rem;">
                    <i class="bi bi-person-vcard" style="color:var(--green); margin-right:6px;"></i>
                    Basic Information
                </strong>
            </div>
            <div class="card-sis-body">
                <div class="row g-4">

                    <!-- Student ID -->
                    <div class="col-md-6">
                        <label class="form-label-sis">Student ID</label>
                        <input type="text"
                               name="student_id"
                               class="form-control-sis<?= errClass('student_id', $errors) ?>"
                               placeholder="e.g. 2024-0001"
                               value="<?= old('student_id', $student) ?>"
                               maxlength="20">
                        <?php if (isset($errors['student_id'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['student_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Full Name -->
                    <div class="col-md-6">
                        <label class="form-label-sis">Full Name</label>
                        <input type="text"
                               name="full_name"
                               class="form-control-sis<?= errClass('full_name', $errors) ?>"
                               placeholder="e.g. Maria Santos"
                               value="<?= old('full_name', $student) ?>"
                               maxlength="100">
                        <?php if (isset($errors['full_name'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['full_name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label-sis">Email Address</label>
                        <input type="email"
                               name="email"
                               class="form-control-sis<?= errClass('email', $errors) ?>"
                               placeholder="e.g. student@school.edu"
                               value="<?= old('email', $student) ?>"
                               maxlength="150">
                        <?php if (isset($errors['email'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Contact -->
                    <div class="col-md-6">
                        <label class="form-label-sis">Contact Number</label>
                        <input type="text"
                               name="contact_no"
                               class="form-control-sis<?= errClass('contact_no', $errors) ?>"
                               placeholder="e.g. 09171234567"
                               value="<?= old('contact_no', $student) ?>"
                               maxlength="20">
                        <?php if (isset($errors['contact_no'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['contact_no']) ?></p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <div class="card-sis mt-4">
            <div class="card-sis-header">
                <strong style="font-size:.95rem;">
                    <i class="bi bi-book" style="color:var(--green); margin-right:6px;"></i>
                    Academic Information
                </strong>
            </div>
            <div class="card-sis-body">
                <div class="row g-4">

                    <!-- Course -->
                    <div class="col-md-6">
                        <label class="form-label-sis">Course / Program</label>
                        <input type="text"
                               name="course"
                               class="form-control-sis<?= errClass('course', $errors) ?>"
                               placeholder="e.g. BS Computer Science"
                               value="<?= old('course', $student) ?>"
                               maxlength="100"
                               list="courseList">
                        <datalist id="courseList">
                            <option value="BS Computer Science">
                            <option value="BS Information Technology">
                            <option value="BS Information Systems">
                            <option value="BS Computer Engineering">
                            <option value="BS Electronics Engineering">
                        </datalist>
                        <?php if (isset($errors['course'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['course']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Year Level -->
                    <div class="col-md-3">
                        <label class="form-label-sis">Year Level</label>
                        <select name="year_level"
                                class="form-select-sis<?= errClass('year_level', $errors) ?>">
                            <option value="">Select year</option>
                            <?php foreach ([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year'] as $val => $lbl): ?>
                                <option value="<?= $val ?>"
                                    <?= (isset($student['year_level']) && $student['year_level'] == $val) ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['year_level'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['year_level']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- GPA -->
                    <div class="col-md-3">
                        <label class="form-label-sis">GPA</label>
                        <input type="number"
                               name="gpa"
                               class="form-control-sis<?= errClass('gpa', $errors) ?>"
                               placeholder="e.g. 1.50"
                               value="<?= old('gpa', $student) ?>"
                               min="1.00" max="5.00" step="0.01">
                        <p style="font-size:.72rem; color:var(--text-muted); margin-top:4px; margin-bottom:0;">
                            1.00 = highest &bull; 5.00 = lowest
                        </p>
                        <?php if (isset($errors['gpa'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['gpa']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label class="form-label-sis">Home Address</label>
                        <textarea name="address"
                                  class="form-control-sis<?= errClass('address', $errors) ?>"
                                  placeholder="Street, Barangay, City / Municipality, Province"
                                  rows="3"><?= old('address', $student) ?></textarea>
                        <?php if (isset($errors['address'])): ?>
                            <p class="invalid-msg"><i class="bi bi-x-circle-fill"></i><?= htmlspecialchars($errors['address']) ?></p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn-green">
                <i class="bi <?= $isEdit ? 'bi-floppy' : 'bi-person-plus-fill' ?>"></i>
                <?= $isEdit ? 'Save Changes' : 'Add Student' ?>
            </button>
            <a href="index.php" class="btn-outline-sis">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
            <?php if ($isEdit): ?>
                <a href="view.php?id=<?= $id ?>"
                   style="margin-left:auto; color:var(--text-muted); font-size:.875rem; display:flex; align-items:center; gap:5px;">
                    <i class="bi bi-eye"></i> View Profile
                </a>
            <?php endif; ?>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>
