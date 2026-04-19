<?php
require_once '../config.php';
$pageTitle = 'Admin Dashboard';
requireAdmin();

// Counts
$total_students  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='student'"))['c'];
$total_teachers  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='teacher'"))['c'];
$total_subjects  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM subjects"))['c'];
$total_submissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM submissions"))['c'];

include '../includes/header.php';
?>

<div class="page-title">⚙️ Admin Dashboard</div>

<div class="dashboard-grid">
    <div class="dash-card dash-card-blue">
        <div class="dash-number"><?php echo $total_students; ?></div>
        <div class="dash-label">Total Students</div>
    </div>
    <div class="dash-card dash-card-green">
        <div class="dash-number"><?php echo $total_teachers; ?></div>
        <div class="dash-label">Total Teachers</div>
    </div>
    <div class="dash-card dash-card-orange">
        <div class="dash-number"><?php echo $total_subjects; ?></div>
        <div class="dash-label">Total Subjects</div>
    </div>
    <div class="dash-card dash-card-purple">
        <div class="dash-number"><?php echo $total_submissions; ?></div>
        <div class="dash-label">Total Submissions</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:24px;">
    <a href="add_course.php"    class="btn btn-primary" style="padding:14px;text-align:center;display:block;">📚 Manage Courses</a>
    <a href="add_semester.php"  class="btn btn-primary" style="padding:14px;text-align:center;display:block;">📅 Manage Semesters</a>
    <a href="add_subject.php"   class="btn btn-primary" style="padding:14px;text-align:center;display:block;">📖 Manage Subjects</a>
    <a href="add_teacher.php"   class="btn btn-success" style="padding:14px;text-align:center;display:block;">👩‍🏫 Manage Teachers</a>
    <a href="assign_teacher.php" class="btn btn-success" style="padding:14px;text-align:center;display:block;">🔗 Assign Teachers</a>
</div>

<!-- Recent Students -->
<div class="card">
    <div class="card-title">Recent Student Registrations</div>
    <?php
    $recent = mysqli_query($conn, "SELECT u.name, u.email, c.course_code, s.semester_name, u.created_at
                                   FROM users u
                                   LEFT JOIN courses c ON u.course_id = c.id
                                   LEFT JOIN semesters s ON u.semester_id = s.id
                                   WHERE u.role='student'
                                   ORDER BY u.created_at DESC LIMIT 8");
    ?>
    <?php if (mysqli_num_rows($recent) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Registered</th>
        </tr>
        <?php while ($r = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td><?php echo htmlspecialchars($r['course_code'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($r['semester_name'] ?? 'N/A'); ?></td>
            <td><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="empty-icon">👤</div>No students registered yet.</div>
    <?php endif; ?>
</div>

<!-- Teacher List -->
<div class="card">
    <div class="card-title">Teachers</div>
    <?php
    $teachers = mysqli_query($conn, "SELECT u.id, u.name, u.email,
                                            GROUP_CONCAT(sub.subject_name SEPARATOR ', ') AS subjects
                                     FROM users u
                                     LEFT JOIN teacher_assignments ta ON u.id = ta.teacher_id
                                     LEFT JOIN subjects sub ON ta.subject_id = sub.id
                                     WHERE u.role='teacher'
                                     GROUP BY u.id
                                     ORDER BY u.name");
    ?>
    <?php if ($teachers && mysqli_num_rows($teachers) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr><th>Name</th><th>Email</th><th>Assigned Subjects</th></tr>
        <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
        <tr>
            <td><?php echo htmlspecialchars($t['name']); ?></td>
            <td><?php echo htmlspecialchars($t['email']); ?></td>
            <td><?php echo $t['subjects'] ? htmlspecialchars($t['subjects']) : '<span class="text-muted">Not assigned</span>'; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="empty-icon">👩‍🏫</div>No teachers added yet. <a href="add_teacher.php">Add Teacher</a></div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
