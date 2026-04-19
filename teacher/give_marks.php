<?php
require_once '../config.php';
$pageTitle = 'Give Marks';
requireTeacher();

$teacher_id   = (int)$_SESSION['user_id'];
$submission_id = (int)($_GET['submission_id'] ?? 0);

if (!$submission_id) {
    redirect(BASE_URL . 'teacher/view_submissions.php');
}

// Verify teacher has access to this submission's subject
$sub_res = mysqli_query($conn,
    "SELECT s.*, u.name AS student_name, u.email AS student_email,
            sub.subject_name, sub.subject_code,
            sem.semester_name,
            m.id AS mark_id, m.marks_obtained, m.review
     FROM submissions s
     JOIN users u ON s.student_id = u.id
     JOIN subjects sub ON s.subject_id = sub.id
     JOIN semesters sem ON s.semester_id = sem.id
     LEFT JOIN marks m ON s.id = m.submission_id
     WHERE s.id = $submission_id
       AND s.subject_id IN (
           SELECT subject_id FROM teacher_assignments WHERE teacher_id = $teacher_id
       )
     LIMIT 1");

if (!$sub_res || mysqli_num_rows($sub_res) === 0) {
    redirect(BASE_URL . 'teacher/view_submissions.php');
}

$submission = mysqli_fetch_assoc($sub_res);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marks  = $_POST['marks'] ?? '';
    $review = sanitize($conn, $_POST['review'] ?? '');

    if ($marks === '' || !is_numeric($marks)) {
        $error = 'Please enter valid marks.';
    } elseif ((float)$marks < 0 || (float)$marks > 5) {
        $error = 'Marks must be between 0 and 5.';
    } else {
        $marks = (float)$marks;
        if ($submission['mark_id']) {
            // Update existing marks
            $mid = (int)$submission['mark_id'];
            mysqli_query($conn, "UPDATE marks SET marks_obtained=$marks, review='$review', marked_at=NOW()
                                 WHERE id=$mid");
        } else {
            // Insert new marks
            mysqli_query($conn, "INSERT INTO marks (submission_id, teacher_id, marks_obtained, review)
                                 VALUES ($submission_id, $teacher_id, $marks, '$review')");
        }
        $success = 'Marks saved successfully!';
        // Refresh submission data
        $sub_res2 = mysqli_query($conn,
            "SELECT m.marks_obtained, m.review, m.id AS mark_id FROM submissions s
             LEFT JOIN marks m ON s.id=m.submission_id WHERE s.id=$submission_id LIMIT 1");
        if ($sub_res2) {
            $upd = mysqli_fetch_assoc($sub_res2);
            $submission['marks_obtained'] = $upd['marks_obtained'];
            $submission['review']         = $upd['review'];
            $submission['mark_id']        = $upd['mark_id'];
        }
    }
}

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span>
    <a href="view_submissions.php">Submissions</a> <span>&rsaquo;</span> Give Marks
</div>
<div class="page-title">✏️ Give Marks</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

<!-- Submission Details -->
<div class="card">
    <div class="card-title">Submission Details</div>
    <table>
        <tr>
            <td style="font-weight:bold;width:38%;padding:7px 0;">Student</td>
            <td><?php echo htmlspecialchars($submission['student_name']); ?><br>
                <small class="text-muted"><?php echo htmlspecialchars($submission['student_email']); ?></small></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Subject</td>
            <td><?php echo htmlspecialchars($submission['subject_code']); ?> &mdash;
                <?php echo htmlspecialchars($submission['subject_name']); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Semester</td>
            <td><?php echo htmlspecialchars($submission['semester_name']); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Assignment Title</td>
            <td><?php echo htmlspecialchars($submission['title']); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Submitted On</td>
            <td><?php echo date('d M Y, h:i A', strtotime($submission['submitted_at'])); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">File</td>
            <td>
                <a href="<?php echo BASE_URL . 'uploads/' . htmlspecialchars($submission['file_name']); ?>"
                   target="_blank" class="btn btn-primary btn-sm">📥 Download File</a>
            </td>
        </tr>
    </table>
</div>

<!-- Give Marks Form -->
<div class="card">
    <div class="card-title">
        <?php echo $submission['mark_id'] ? 'Update Marks' : 'Assign Marks'; ?>
    </div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Marks (out of 5) *</label>
            <input type="number" name="marks" min="0" max="5" step="0.5"
                   value="<?php echo htmlspecialchars($submission['marks_obtained'] ?? ''); ?>"
                   placeholder="e.g. 4 or 4.5">
            <div class="hint">Enter marks between 0 and 5. Half marks allowed (0.5, 1.5, etc.)</div>
        </div>

        <!-- Star visual helper -->
        <div class="form-group">
            <label>Quick Select:</label>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php for ($v = 0; $v <= 5; $v += 0.5): ?>
                <button type="button" class="btn btn-sm"
                        style="background:#f0f2f5;color:#333;padding:4px 10px;"
                        onclick="document.querySelector('[name=marks]').value='<?php echo $v; ?>'">
                    <?php echo $v; ?>
                </button>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Review / Comment</label>
            <textarea name="review" placeholder="Write your feedback for the student..."><?php echo htmlspecialchars($submission['review'] ?? ''); ?></textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success">💾 Save Marks</button>
            <a href="view_submissions.php" class="btn" style="background:#f0f2f5;color:#333;">Cancel</a>
        </div>
    </form>
</div>
</div>

<?php include '../includes/footer.php'; ?>
