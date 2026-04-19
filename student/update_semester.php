<?php
require_once '../config.php';
$pageTitle = 'Update Semester';
requireStudent();

$student_id = (int)$_SESSION['user_id'];
$course_id  = (int)($_SESSION['course_id'] ?? 0);

// Fetch fresh student info
$student_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT u.*, c.course_name, c.course_code, s.semester_name, s.semester_number
     FROM users u
     LEFT JOIN courses c ON u.course_id=c.id
     LEFT JOIN semesters s ON u.semester_id=s.id
     WHERE u.id=$student_id LIMIT 1"));

$current_sem_number = (int)($student_info['semester_number'] ?? 0);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_semester_id = (int)($_POST['semester_id'] ?? 0);

    if (!$new_semester_id) {
        $error = 'Please select a semester.';
    } else {
        // Verify semester belongs to student's course
        $sem_check = mysqli_query($conn,
            "SELECT id, semester_name, semester_number FROM semesters
             WHERE id=$new_semester_id AND course_id=$course_id LIMIT 1");
        if (!$sem_check || mysqli_num_rows($sem_check) === 0) {
            $error = 'Invalid semester selection.';
        } else {
            $new_sem = mysqli_fetch_assoc($sem_check);
            // Save history entry (store total marks for old semester)
            $old_sem_id = (int)$student_info['semester_id'];
            if ($old_sem_id && $old_sem_id !== $new_semester_id) {
                $totals = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT SUM(m.marks_obtained) AS total
                     FROM submissions s JOIN marks m ON s.id=m.submission_id
                     WHERE s.student_id=$student_id AND s.semester_id=$old_sem_id"));
                $total_marks = (float)($totals['total'] ?? 0);
                // Upsert history
                $hchk = mysqli_query($conn,
                    "SELECT id FROM semester_history WHERE student_id=$student_id AND semester_id=$old_sem_id");
                if (mysqli_num_rows($hchk) === 0) {
                    mysqli_query($conn,
                        "INSERT INTO semester_history (student_id, semester_id, total_marks)
                         VALUES ($student_id, $old_sem_id, $total_marks)");
                }
            }
            // Update student's semester
            mysqli_query($conn, "UPDATE users SET semester_id=$new_semester_id WHERE id=$student_id");
            $_SESSION['semester_id'] = $new_semester_id;
            $success = 'Semester updated to <strong>' . htmlspecialchars($new_sem['semester_name']) . '</strong> successfully!';

            // Refresh info
            $student_info = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT u.*, c.course_name, c.course_code, s.semester_name, s.semester_number
                 FROM users u
                 LEFT JOIN courses c ON u.course_id=c.id
                 LEFT JOIN semesters s ON u.semester_id=s.id
                 WHERE u.id=$student_id LIMIT 1"));
            $current_sem_number = (int)$student_info['semester_number'];
        }
    }
}

// Semesters for this course
$semesters = mysqli_query($conn,
    "SELECT * FROM semesters WHERE course_id=$course_id ORDER BY semester_number");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Update Semester
</div>
<div class="page-title">🔄 Update Semester</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

<div class="card">
    <div class="card-title">Current Status</div>
    <table>
        <tr>
            <td style="font-weight:bold;padding:7px 0;width:40%;">Name</td>
            <td><?php echo htmlspecialchars($student_info['name']); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Course</td>
            <td><?php echo htmlspecialchars($student_info['course_name'] ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;padding:7px 0;">Current Semester</td>
            <td>
                <span class="badge badge-blue">
                    <?php echo htmlspecialchars($student_info['semester_name'] ?? 'Not Set'); ?>
                </span>
            </td>
        </tr>
    </table>

    <div class="alert alert-info" style="margin-top:14px;font-size:13px;">
        <strong>Note:</strong> When you move to next semester, your previous semester marks are saved in history.
        You can view them in <a href="view_marks.php">My Marks</a>.
    </div>
</div>

<div class="card">
    <div class="card-title">Change Semester</div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Select New Semester *</label>
            <select name="semester_id">
                <option value="">-- Select Semester --</option>
                <?php while ($sem = mysqli_fetch_assoc($semesters)): ?>
                <option value="<?php echo $sem['id']; ?>"
                    <?php echo ((int)$sem['id'] === (int)$student_info['semester_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($sem['semester_name']); ?>
                    <?php echo ((int)$sem['id'] === (int)$student_info['semester_id']) ? ' (Current)' : ''; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"
                onclick="return confirm('Are you sure you want to change your semester? Previous semester data will be archived.');">
            🔄 Update Semester
        </button>
    </form>
</div>
</div>

<!-- Semester History -->
<?php
$history = mysqli_query($conn,
    "SELECT sh.*, sem.semester_name, sem.semester_number
     FROM semester_history sh
     JOIN semesters sem ON sh.semester_id=sem.id
     WHERE sh.student_id=$student_id
     ORDER BY sem.semester_number");
?>
<?php if ($history && mysqli_num_rows($history) > 0): ?>
<div class="card">
    <div class="card-title">📁 Semester Transition History</div>
    <table>
        <tr><th>#</th><th>Semester</th><th>Total Marks Earned</th><th>Archived On</th></tr>
        <?php $i=1; while ($h = mysqli_fetch_assoc($history)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($h['semester_name']); ?></td>
            <td>
                <span class="marks-score"><?php echo number_format($h['total_marks'],1); ?></span>
            </td>
            <td><?php echo date('d M Y', strtotime($h['moved_at'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
