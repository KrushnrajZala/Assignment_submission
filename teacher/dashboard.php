<?php
require_once '../config.php';
$pageTitle = 'Teacher Dashboard';
requireTeacher();

$teacher_id = (int)$_SESSION['user_id'];

// Count pending (unmarked) submissions for this teacher's subjects
$pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM submissions s
     JOIN teacher_assignments ta ON s.subject_id=ta.subject_id AND ta.teacher_id=$teacher_id
     WHERE s.id NOT IN (SELECT submission_id FROM marks)"))['c'];

$total_sub = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM submissions s
     JOIN teacher_assignments ta ON s.subject_id=ta.subject_id AND ta.teacher_id=$teacher_id"))['c'];

$marked = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM marks WHERE teacher_id=$teacher_id"))['c'];

// Assigned subjects
$assigned = mysqli_query($conn,
    "SELECT ta.id, sub.subject_name, sub.subject_code, sub.is_elective, sem.semester_name
     FROM teacher_assignments ta
     JOIN subjects sub ON ta.subject_id=sub.id
     JOIN semesters sem ON ta.semester_id=sem.id
     WHERE ta.teacher_id=$teacher_id
     ORDER BY sem.semester_number, sub.subject_code");

include '../includes/header.php';
?>

<div class="page-title">👩‍🏫 Teacher Dashboard</div>

<div class="dashboard-grid">
    <div class="dash-card dash-card-orange">
        <div class="dash-number"><?php echo $pending; ?></div>
        <div class="dash-label">Pending to Mark</div>
    </div>
    <div class="dash-card dash-card-blue">
        <div class="dash-number"><?php echo $total_sub; ?></div>
        <div class="dash-label">Total Submissions</div>
    </div>
    <div class="dash-card dash-card-green">
        <div class="dash-number"><?php echo $marked; ?></div>
        <div class="dash-label">Marked</div>
    </div>
</div>

<div class="card">
    <div class="card-title">Assigned Subjects</div>
    <?php if ($assigned && mysqli_num_rows($assigned) > 0): ?>
    <div class="subject-grid">
        <?php while ($sub = mysqli_fetch_assoc($assigned)):
            $subj_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM subjects WHERE subject_code='{$sub['subject_code']}' LIMIT 1"))['id'] ?? 0;
            $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM submissions WHERE subject_id=(SELECT id FROM subjects WHERE subject_code='{$sub['subject_code']}' LIMIT 1)"))['c'];
        ?>
        <div class="subject-card <?php echo $sub['is_elective'] ? 'elective' : ''; ?>">
            <div class="sub-code"><?php echo htmlspecialchars($sub['subject_code']); ?></div>
            <div class="sub-name"><?php echo htmlspecialchars($sub['subject_name']); ?></div>
            <?php if ($sub['is_elective']): ?>
            <span class="elective-tag">Elective</span><br>
            <?php endif; ?>
            <small class="text-muted"><?php echo htmlspecialchars($sub['semester_name']); ?></small><br>
            <small class="text-muted"><?php echo $count; ?> submission(s)</small><br>
            <a href="view_submissions.php?subject_code=<?php echo urlencode($sub['subject_code']); ?>"
               class="btn btn-primary btn-sm" style="margin-top:8px;">View Submissions</a>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        No subjects assigned yet. Please contact Admin.
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-title">Quick Actions</div>
    <a href="view_submissions.php" class="btn btn-primary" style="margin-right:10px;">📋 All Submissions</a>
    <a href="../change_password.php" class="btn" style="background:#f0f2f5;color:#333;">🔒 Change Password</a>
</div>

<?php include '../includes/footer.php'; ?>
