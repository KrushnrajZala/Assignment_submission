<?php
require_once '../config.php';
$pageTitle = 'Student Dashboard';
requireStudent();

$student_id  = (int)$_SESSION['user_id'];
$semester_id = (int)($_SESSION['semester_id'] ?? 0);
$course_id   = (int)($_SESSION['course_id'] ?? 0);

// Fetch current student info (fresh from DB)
$student_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT u.*, c.course_name, c.course_code, s.semester_name
     FROM users u
     LEFT JOIN courses c ON u.course_id = c.id
     LEFT JOIN semesters s ON u.semester_id = s.id
     WHERE u.id = $student_id LIMIT 1"));

// Sync session semester (in case it was updated)
$semester_id = (int)$student_info['semester_id'];
$_SESSION['semester_id'] = $semester_id;

// Count stats
$total_subs = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM submissions WHERE student_id=$student_id"))['c'];
$marked_subs = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM submissions s JOIN marks m ON s.id=m.submission_id WHERE s.student_id=$student_id"))['c'];
$avg_marks = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(m.marks_obtained) AS avg FROM submissions s JOIN marks m ON s.id=m.submission_id WHERE s.student_id=$student_id"))['avg'];

// Current semester subjects
$subjects = mysqli_query($conn,
    "SELECT * FROM subjects WHERE semester_id = $semester_id ORDER BY is_elective, subject_code");

include '../includes/header.php';
?>

<div class="page-title">🎓 Student Dashboard</div>

<!-- Student Info -->
<div class="card" style="background:linear-gradient(90deg,#e3f2fd,#fff);border-left:5px solid #1565c0;padding:16px 20px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:18px;font-weight:bold;color:#1565c0;">
                👋 Welcome, <?php echo htmlspecialchars($student_info['name']); ?>
            </div>
            <div style="color:#555;font-size:13px;margin-top:4px;">
                <?php echo htmlspecialchars($student_info['course_name'] ?? 'N/A'); ?> &nbsp;|&nbsp;
                <?php echo htmlspecialchars($student_info['semester_name'] ?? 'N/A'); ?> &nbsp;|&nbsp;
                <?php echo htmlspecialchars($student_info['email']); ?>
            </div>
        </div>
        <a href="update_semester.php" class="btn btn-primary btn-sm">🔄 Update Semester</a>
    </div>
</div>

<!-- Stats -->
<div class="dashboard-grid">
    <div class="dash-card dash-card-blue">
        <div class="dash-number"><?php echo $total_subs; ?></div>
        <div class="dash-label">Submitted</div>
    </div>
    <div class="dash-card dash-card-green">
        <div class="dash-number"><?php echo $marked_subs; ?></div>
        <div class="dash-label">Marked</div>
    </div>
    <div class="dash-card dash-card-orange">
        <div class="dash-number"><?php echo $avg_marks ? number_format($avg_marks, 1) : 'N/A'; ?></div>
        <div class="dash-label">Avg Marks /5</div>
    </div>
    <div class="dash-card dash-card-purple">
        <div class="dash-number"><?php echo ($total_subs - $marked_subs); ?></div>
        <div class="dash-label">Pending Review</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="padding:14px 18px;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="submit_assignment.php"  class="btn btn-primary">📤 Submit Assignment</a>
        <a href="view_assignments.php"   class="btn btn-success">📋 My Submissions</a>
        <a href="view_marks.php"         class="btn" style="background:#6a1b9a;color:#fff;">📊 My Marks</a>
        <a href="update_semester.php"    class="btn" style="background:#e65100;color:#fff;">🔄 Update Semester</a>
    </div>
</div>

<!-- Subjects for current semester -->
<div class="card">
    <div class="card-title">
        📚 Subjects &mdash; <?php echo htmlspecialchars($student_info['semester_name'] ?? 'Not Set'); ?>
    </div>

    <?php if ($semester_id && $subjects && mysqli_num_rows($subjects) > 0): ?>
    <div class="subject-grid">
        <?php while ($sub = mysqli_fetch_assoc($subjects)):
            // Count submissions for this subject
            $subj_id  = (int)$sub['id'];
            $sub_count = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) AS c FROM submissions WHERE student_id=$student_id AND subject_id=$subj_id"))['c'];
        ?>
        <div class="subject-card <?php echo $sub['is_elective'] ? 'elective' : ''; ?>">
            <div class="sub-code"><?php echo htmlspecialchars($sub['subject_code']); ?></div>
            <div class="sub-name"><?php echo htmlspecialchars($sub['subject_name']); ?></div>
            <?php if ($sub['is_elective']): ?>
            <span class="elective-tag">Elective</span><br>
            <?php endif; ?>
            <small class="text-muted"><?php echo $sub_count; ?> submission(s)</small><br>
            <a href="submit_assignment.php?subject_id=<?php echo $sub['id']; ?>"
               class="btn btn-primary btn-sm" style="margin-top:8px;">Submit</a>
        </div>
        <?php endwhile; ?>
    </div>
    <?php elseif (!$semester_id): ?>
    <div class="empty-state">
        <div class="empty-icon">⚠️</div>
        Semester not set. Please <a href="update_semester.php">update your semester</a>.
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        No subjects found for current semester.
    </div>
    <?php endif; ?>
</div>

<!-- Recent submissions -->
<div class="card">
    <div class="card-title">Recent Submissions</div>
    <?php
    $recent = mysqli_query($conn,
        "SELECT s.title, sub.subject_code, s.submitted_at, m.marks_obtained
         FROM submissions s
         JOIN subjects sub ON s.subject_id = sub.id
         LEFT JOIN marks m ON s.id = m.submission_id
         WHERE s.student_id = $student_id
         ORDER BY s.submitted_at DESC LIMIT 5");
    ?>
    <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
    <table>
        <tr><th>Title</th><th>Subject</th><th>Submitted</th><th>Marks</th></tr>
        <?php while ($r = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['title']); ?></td>
            <td><?php echo htmlspecialchars($r['subject_code']); ?></td>
            <td><?php echo date('d M Y', strtotime($r['submitted_at'])); ?></td>
            <td>
                <?php if ($r['marks_obtained'] !== null): ?>
                <span class="marks-score"><?php echo $r['marks_obtained']; ?>/5</span>
                <?php else: ?>
                <span class="badge badge-orange">Pending</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <div class="empty-state"><div class="empty-icon">📄</div>No submissions yet. <a href="submit_assignment.php">Submit your first assignment</a>.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
