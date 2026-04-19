<?php
require_once '../config.php';
$pageTitle = 'Manage Courses';
requireAdmin();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = sanitize($conn, $_POST['course_name'] ?? '');
    $course_code = sanitize($conn, $_POST['course_code'] ?? '');
    if (empty($course_name) || empty($course_code)) {
        $error = 'All fields are required.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM courses WHERE course_code='$course_code'");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'Course with this code already exists.';
        } else {
            mysqli_query($conn, "INSERT INTO courses (course_name, course_code) VALUES ('$course_name','$course_code')");
            $success = 'Course added successfully!';
        }
    }
}

$courses = mysqli_query($conn, "SELECT * FROM courses ORDER BY id DESC");
include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Manage Courses
</div>
<div class="page-title">📚 Manage Courses</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">
<div class="card">
    <div class="card-title">Add New Course</div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Course Name *</label>
            <input type="text" name="course_name" placeholder="e.g. Master of Computer Applications">
        </div>
        <div class="form-group">
            <label>Course Code *</label>
            <input type="text" name="course_code" placeholder="e.g. MCA">
        </div>
        <button type="submit" class="btn btn-primary">Add Course</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Existing Courses</div>
    <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
    <table>
        <tr><th>#</th><th>Course Name</th><th>Code</th></tr>
        <?php $i=1; while ($c = mysqli_fetch_assoc($courses)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($c['course_name']); ?></td>
            <td><?php echo htmlspecialchars($c['course_code']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <div class="empty-state">No courses yet.</div>
    <?php endif; ?>
</div>
</div>

<?php include '../includes/footer.php'; ?>
