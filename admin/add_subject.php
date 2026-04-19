<?php
require_once '../config.php';
$pageTitle = 'Manage Subjects';
requireAdmin();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester_id  = (int)($_POST['semester_id'] ?? 0);
    $subject_name = sanitize($conn, $_POST['subject_name'] ?? '');
    $subject_code = sanitize($conn, $_POST['subject_code'] ?? '');
    $is_elective  = isset($_POST['is_elective']) ? 1 : 0;

    if (!$semester_id || empty($subject_name) || empty($subject_code)) {
        $error = 'All fields are required.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM subjects WHERE subject_code='$subject_code' AND semester_id=$semester_id");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'Subject with this code already exists in selected semester.';
        } else {
            mysqli_query($conn, "INSERT INTO subjects (semester_id, subject_name, subject_code, is_elective)
                                 VALUES ($semester_id,'$subject_name','$subject_code',$is_elective)");
            $success = 'Subject added successfully!';
        }
    }
}

$semesters = mysqli_query($conn, "SELECT s.*, c.course_code FROM semesters s JOIN courses c ON s.course_id=c.id ORDER BY c.course_code, s.semester_number");
$subjects  = mysqli_query($conn, "SELECT sub.*, sem.semester_name, c.course_code
                                   FROM subjects sub
                                   JOIN semesters sem ON sub.semester_id=sem.id
                                   JOIN courses c ON sem.course_id=c.id
                                   ORDER BY sem.semester_number, sub.subject_code");
include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Manage Subjects
</div>
<div class="page-title">📖 Manage Subjects</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">
<div class="card">
    <div class="card-title">Add New Subject</div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Semester *</label>
            <select name="semester_id">
                <option value="">-- Select Semester --</option>
                <?php while ($s = mysqli_fetch_assoc($semesters)): ?>
                <option value="<?php echo $s['id']; ?>">
                    <?php echo htmlspecialchars($s['course_code'] . ' - ' . $s['semester_name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Subject Name *</label>
            <input type="text" name="subject_name" placeholder="e.g. Python Programming">
        </div>
        <div class="form-group">
            <label>Subject Code *</label>
            <input type="text" name="subject_code" placeholder="e.g. PS01CMCA51">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_elective" value="1">
                &nbsp;This is an Elective Subject
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Add Subject</button>
    </form>
</div>

<div class="card">
    <div class="card-title">All Subjects</div>
    <?php if ($subjects && mysqli_num_rows($subjects) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr><th>#</th><th>Code</th><th>Subject Name</th><th>Semester</th><th>Type</th></tr>
        <?php $i=1; while ($sub = mysqli_fetch_assoc($subjects)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($sub['subject_code']); ?></td>
            <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
            <td><?php echo htmlspecialchars($sub['semester_name']); ?></td>
            <td>
                <?php if ($sub['is_elective']): ?>
                <span class="badge badge-orange">Elective</span>
                <?php else: ?>
                <span class="badge badge-blue">Core</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">No subjects yet.</div>
    <?php endif; ?>
</div>
</div>

<?php include '../includes/footer.php'; ?>
