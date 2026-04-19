<?php
require_once '../config.php';
$pageTitle = 'Assign Teacher';
requireAdmin();

$error = $success = '';
$selected_teacher = (int)($_GET['teacher_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id  = (int)($_POST['teacher_id'] ?? 0);
    $semester_id = (int)($_POST['semester_id'] ?? 0);
    $subject_id  = (int)($_POST['subject_id'] ?? 0);

    if (!$teacher_id || !$semester_id || !$subject_id) {
        $error = 'All fields are required.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM teacher_assignments WHERE teacher_id=$teacher_id AND subject_id=$subject_id");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'This teacher is already assigned to that subject.';
        } else {
            mysqli_query($conn, "INSERT INTO teacher_assignments (teacher_id, semester_id, subject_id)
                                 VALUES ($teacher_id, $semester_id, $subject_id)");
            $success = 'Teacher assigned successfully!';
        }
    }
}

// Handle unassign
if (isset($_GET['unassign'])) {
    $aid = (int)$_GET['unassign'];
    mysqli_query($conn, "DELETE FROM teacher_assignments WHERE id=$aid");
    redirect(BASE_URL . 'admin/assign_teacher.php?msg=unassigned');
}

$teachers  = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role='teacher' ORDER BY name");
$semesters = mysqli_query($conn, "SELECT s.*, c.course_code FROM semesters s JOIN courses c ON s.course_id=c.id ORDER BY s.semester_number");

// Existing assignments
$assignments = mysqli_query($conn, "SELECT ta.id, u.name AS teacher_name, sem.semester_name, sub.subject_name, sub.subject_code
                                     FROM teacher_assignments ta
                                     JOIN users u ON ta.teacher_id=u.id
                                     JOIN semesters sem ON ta.semester_id=sem.id
                                     JOIN subjects sub ON ta.subject_id=sub.id
                                     ORDER BY sem.semester_number, u.name");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Assign Teacher
</div>
<div class="page-title">🔗 Assign Teacher to Subject</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'unassigned'): ?>
<div class="alert alert-success">Assignment removed.</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">
<div class="card">
    <div class="card-title">New Assignment</div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Teacher *</label>
            <select name="teacher_id" id="sel_teacher">
                <option value="">-- Select Teacher --</option>
                <?php while ($t = mysqli_fetch_assoc($teachers)): ?>
                <option value="<?php echo $t['id']; ?>" <?php echo ($t['id']==$selected_teacher)?'selected':''; ?>>
                    <?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['email']; ?>)
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Semester *</label>
            <select name="semester_id" id="sel_semester" onchange="loadSubjectsForSem(this.value)">
                <option value="">-- Select Semester --</option>
                <?php while ($s = mysqli_fetch_assoc($semesters)): ?>
                <option value="<?php echo $s['id']; ?>">
                    <?php echo htmlspecialchars($s['course_code'] . ' - ' . $s['semester_name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Subject *</label>
            <select name="subject_id" id="sel_subject">
                <option value="">-- Select Semester First --</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Assign Teacher</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Current Assignments</div>
    <?php if ($assignments && mysqli_num_rows($assignments) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr><th>Teacher</th><th>Semester</th><th>Subject</th><th>Action</th></tr>
        <?php while ($a = mysqli_fetch_assoc($assignments)): ?>
        <tr>
            <td><?php echo htmlspecialchars($a['teacher_name']); ?></td>
            <td><?php echo htmlspecialchars($a['semester_name']); ?></td>
            <td><?php echo htmlspecialchars($a['subject_code'] . ' - ' . $a['subject_name']); ?></td>
            <td>
                <a href="assign_teacher.php?unassign=<?php echo $a['id']; ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Remove this assignment?');">Remove</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">No assignments yet.</div>
    <?php endif; ?>
</div>
</div>

<script>
function loadSubjectsForSem(semId) {
    var sel = document.getElementById('sel_subject');
    sel.innerHTML = '<option value="">Loading...</option>';
    if (!semId) { sel.innerHTML = '<option value="">-- Select Semester First --</option>'; return; }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo BASE_URL; ?>ajax_subjects.php?semester_id=' + semId, true);
    xhr.onload = function() { if (xhr.status===200) sel.innerHTML = xhr.responseText; };
    xhr.send();
}
</script>

<?php include '../includes/footer.php'; ?>
