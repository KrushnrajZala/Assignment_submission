<?php
require_once '../config.php';
$pageTitle = 'My Submissions';
requireStudent();

$student_id  = (int)$_SESSION['user_id'];
$semester_id = (int)($_SESSION['semester_id'] ?? 0);

// Refresh semester_id from DB in case it changed
$info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT semester_id FROM users WHERE id=$student_id"));
$semester_id = (int)$info['semester_id'];

// Filter by semester
$filter_sem = (int)($_GET['semester_id'] ?? 0);

$where = "s.student_id = $student_id";
if ($filter_sem) {
    $where .= " AND s.semester_id = $filter_sem";
}

$submissions = mysqli_query($conn,
    "SELECT s.id, s.title, s.file_name, s.submitted_at,
            sub.subject_name, sub.subject_code, sub.is_elective,
            sem.semester_name,
            m.marks_obtained, m.review, m.marked_at
     FROM submissions s
     JOIN subjects sub ON s.subject_id = sub.id
     JOIN semesters sem ON s.semester_id = sem.id
     LEFT JOIN marks m ON s.id = m.submission_id
     WHERE $where
     ORDER BY s.submitted_at DESC");

// Get all student semesters for filter
$all_semesters = mysqli_query($conn,
    "SELECT DISTINCT sem.id, sem.semester_name
     FROM submissions s
     JOIN semesters sem ON s.semester_id = sem.id
     WHERE s.student_id = $student_id
     ORDER BY sem.semester_number");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> My Submissions
</div>
<div class="page-title">📋 My Submissions</div>

<!-- Filter -->
<div class="card" style="padding:12px 18px;">
    <form method="GET" action="" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <label style="font-weight:bold;font-size:13px;">Filter by Semester:</label>
        <select name="semester_id" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ccc;border-radius:4px;">
            <option value="">-- All Semesters --</option>
            <?php while ($sem = mysqli_fetch_assoc($all_semesters)): ?>
            <option value="<?php echo $sem['id']; ?>" <?php echo ($filter_sem === (int)$sem['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($sem['semester_name']); ?>
            </option>
            <?php endwhile; ?>
        </select>
        <?php if ($filter_sem): ?>
        <a href="view_assignments.php" class="btn" style="background:#f0f2f5;color:#333;padding:7px 12px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-title">All My Submitted Assignments</div>

    <?php if ($submissions && mysqli_num_rows($submissions) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Subject</th>
            <th>Semester</th>
            <th>Submitted</th>
            <th>File</th>
            <th>Marks</th>
            <th>Review</th>
        </tr>
        <?php $i = 1; while ($sub = mysqli_fetch_assoc($submissions)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($sub['title']); ?></td>
            <td>
                <strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($sub['subject_name']); ?></small>
                <?php if ($sub['is_elective']): ?>
                <br><span class="badge badge-orange" style="font-size:10px;">Elective</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($sub['semester_name']); ?></td>
            <td><?php echo date('d M Y', strtotime($sub['submitted_at'])); ?><br>
                <small class="text-muted"><?php echo date('h:i A', strtotime($sub['submitted_at'])); ?></small>
            </td>
            <td>
                <a href="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($sub['file_name']); ?>"
                   target="_blank" class="btn btn-primary btn-sm">📥 View</a>
            </td>
            <td style="text-align:center;">
                <?php if ($sub['marks_obtained'] !== null): ?>
                <span class="marks-score"><?php echo $sub['marks_obtained']; ?>/5</span>
                <?php else: ?>
                <span class="badge badge-orange">Pending</span>
                <?php endif; ?>
            </td>
            <td style="max-width:180px;">
                <?php if (!empty($sub['review'])): ?>
                <span style="font-size:12px;color:#555;"><?php echo nl2br(htmlspecialchars($sub['review'])); ?></span>
                <?php else: ?>
                <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📄</div>
        No submissions found. <a href="submit_assignment.php">Submit your first assignment</a>.
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
