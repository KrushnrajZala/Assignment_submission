<?php
require_once '../config.php';
$pageTitle = 'View Submissions';
requireTeacher();

$teacher_id  = (int)$_SESSION['user_id'];
$filter_code = sanitize($conn, $_GET['subject_code'] ?? '');

// Get teacher's assigned subject IDs
$assigned_ids_res = mysqli_query($conn, "SELECT subject_id FROM teacher_assignments WHERE teacher_id=$teacher_id");
$assigned_ids = [];
while ($r = mysqli_fetch_assoc($assigned_ids_res)) {
    $assigned_ids[] = (int)$r['subject_id'];
}

if (empty($assigned_ids)) {
    include '../includes/header.php';
    echo '<div class="page-title">📋 Student Submissions</div>';
    echo '<div class="card"><div class="empty-state"><div class="empty-icon">📚</div>No subjects assigned to you yet.</div></div>';
    include '../includes/footer.php';
    exit;
}

$ids_str = implode(',', $assigned_ids);

// Build query
$where = "s.subject_id IN ($ids_str)";
if (!empty($filter_code)) {
    $where .= " AND sub.subject_code='$filter_code'";
}

$submissions = mysqli_query($conn,
    "SELECT s.id, s.title, s.file_name, s.file_path, s.submitted_at,
            u.name AS student_name, u.email AS student_email,
            sub.subject_name, sub.subject_code,
            sem.semester_name,
            m.marks_obtained, m.review
     FROM submissions s
     JOIN users u    ON s.student_id = u.id
     JOIN subjects sub ON s.subject_id = sub.id
     JOIN semesters sem ON s.semester_id = sem.id
     LEFT JOIN marks m ON s.id = m.submission_id
     WHERE $where
     ORDER BY s.submitted_at DESC");

// Assigned subjects for filter
$subjects_filter = mysqli_query($conn,
    "SELECT sub.subject_code, sub.subject_name
     FROM teacher_assignments ta
     JOIN subjects sub ON ta.subject_id = sub.id
     WHERE ta.teacher_id = $teacher_id
     ORDER BY sub.subject_code");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Student Submissions
</div>
<div class="page-title">📋 Student Submissions</div>

<!-- Filter by subject -->
<div class="card" style="padding:14px 18px;">
    <form method="GET" action="" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <label style="font-weight:bold;font-size:13px;">Filter by Subject:</label>
        <select name="subject_code" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ccc;border-radius:4px;">
            <option value="">-- All Subjects --</option>
            <?php while ($sf = mysqli_fetch_assoc($subjects_filter)): ?>
            <option value="<?php echo htmlspecialchars($sf['subject_code']); ?>"
                <?php echo ($filter_code === $sf['subject_code']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($sf['subject_code'] . ' - ' . $sf['subject_name']); ?>
            </option>
            <?php endwhile; ?>
        </select>
        <?php if (!empty($filter_code)): ?>
        <a href="view_submissions.php" class="btn" style="background:#f0f2f5;color:#333;padding:7px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-title">
        Submissions
        <?php if (!empty($filter_code)) echo " &mdash; " . htmlspecialchars($filter_code); ?>
    </div>

    <?php if ($submissions && mysqli_num_rows($submissions) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr>
            <th>#</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Semester</th>
            <th>Title</th>
            <th>File</th>
            <th>Submitted</th>
            <th>Marks</th>
            <th>Action</th>
        </tr>
        <?php $i=1; while ($sub = mysqli_fetch_assoc($submissions)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td>
                <strong><?php echo htmlspecialchars($sub['student_name']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($sub['student_email']); ?></small>
            </td>
            <td>
                <strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($sub['subject_name']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($sub['semester_name']); ?></td>
            <td><?php echo htmlspecialchars($sub['title']); ?></td>
            <td>
                <a href="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($sub['file_name']); ?>"
                   target="_blank" class="btn btn-primary btn-sm">Download</a>
            </td>
            <td><?php echo date('d M Y', strtotime($sub['submitted_at'])); ?></td>
            <td>
                <?php if ($sub['marks_obtained'] !== null): ?>
                    <span class="marks-score"><?php echo $sub['marks_obtained']; ?>/5</span>
                <?php else: ?>
                    <span class="badge badge-orange">Pending</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="give_marks.php?submission_id=<?php echo $sub['id']; ?>"
                   class="btn btn-success btn-sm">
                    <?php echo ($sub['marks_obtained'] !== null) ? 'Edit Marks' : 'Give Marks'; ?>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📄</div>
        No submissions found<?php echo !empty($filter_code) ? ' for this subject' : ''; ?>.
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
