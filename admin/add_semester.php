<?php
require_once '../config.php';
$pageTitle = 'Manage Semesters';
requireAdmin();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id      = (int)($_POST['course_id'] ?? 0);
    $semester_name  = sanitize($conn, $_POST['semester_name'] ?? '');
    $semester_number= (int)($_POST['semester_number'] ?? 0);

    if (!$course_id || empty($semester_name) || !$semester_number) {
        $error = 'All fields are required.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM semesters WHERE course_id=$course_id AND semester_number=$semester_number");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'This semester already exists for selected course.';
        } else {
            mysqli_query($conn, "INSERT INTO semesters (course_id, semester_name, semester_number)
                                 VALUES ($course_id,'$semester_name',$semester_number)");
            $success = 'Semester added successfully!';
        }
    }
}

$courses   = mysqli_query($conn, "SELECT * FROM courses");
$semesters = mysqli_query($conn, "SELECT s.*, c.course_code FROM semesters s
                                  JOIN courses c ON s.course_id=c.id
                                  ORDER BY c.course_code, s.semester_number");
include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Manage Semesters
</div>
<div class="page-title">📅 Manage Semesters</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div class="card">
    <div class="card-title">Add New Semester</div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Course *</label>
            <select name="course_id">
                <option value="">-- Select Course --</option>
                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Semester Name *</label>
            <input type="text" name="semester_name" placeholder="e.g. MCA Semester 1">
        </div>
        <div class="form-group">
            <label>Semester Number *</label>
            <select name="semester_number">
                <option value="">-- Select --</option>
                <?php for ($i=1; $i<=4; $i++): ?>
                <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Semester</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Existing Semesters</div>
    <?php if ($semesters && mysqli_num_rows($semesters) > 0): ?>
    <table>
        <tr><th>#</th><th>Course</th><th>Semester</th><th>No.</th></tr>
        <?php $i=1; while ($s = mysqli_fetch_assoc($semesters)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($s['course_code']); ?></td>
            <td><?php echo htmlspecialchars($s['semester_name']); ?></td>
            <td><?php echo $s['semester_number']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <div class="empty-state">No semesters yet.</div>
    <?php endif; ?>
</div>
</div>

<?php include '../includes/footer.php'; ?>
