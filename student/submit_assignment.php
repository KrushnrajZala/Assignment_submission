<?php
require_once '../config.php';
$pageTitle = 'Submit Assignment';
requireStudent();

$student_id  = (int)$_SESSION['user_id'];
$semester_id = (int)($_SESSION['semester_id'] ?? 0);
$preselect_subject = (int)($_GET['subject_id'] ?? 0);

if (!$semester_id) {
    include '../includes/header.php';
    echo '<div class="page-title">📤 Submit Assignment</div>';
    echo '<div class="card"><div class="alert alert-error">Semester not set. Please <a href="update_semester.php">update your semester</a> first.</div></div>';
    include '../includes/footer.php';
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = sanitize($conn, $_POST['title'] ?? '');
    $subject_id = (int)($_POST['subject_id'] ?? 0);

    if (empty($title) || !$subject_id) {
        $error = 'Assignment title and subject are required.';
    } elseif (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== 0) {
        $error = 'Please upload a file.';
    } else {
        $file       = $_FILES['assignment_file'];
        $orig_name  = basename($file['name']);
        $ext        = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $allowed    = ['pdf','doc','docx','txt','zip','png','jpg','jpeg'];
        $max_size   = 5 * 1024 * 1024; // 5MB

        if (!in_array($ext, $allowed)) {
            $error = 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG.';
        } elseif ($file['size'] > $max_size) {
            $error = 'File size must be under 5MB.';
        } else {
            // Create uploads dir if not exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }

            $new_name = 'student_' . $student_id . '_sub_' . $subject_id . '_' . time() . '.' . $ext;
            $dest     = UPLOAD_DIR . $new_name;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                mysqli_query($conn,
                    "INSERT INTO submissions (student_id, subject_id, semester_id, title, file_name, file_path)
                     VALUES ($student_id, $subject_id, $semester_id, '$title', '$new_name', '$dest')");
                $success = 'Assignment submitted successfully!';
            } else {
                $error = 'File upload failed. Check uploads/ folder permissions.';
            }
        }
    }
}

// Fetch subjects for this semester
$subjects = mysqli_query($conn, "SELECT * FROM subjects WHERE semester_id=$semester_id ORDER BY is_elective, subject_code");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Submit Assignment
</div>
<div class="page-title">📤 Submit Assignment</div>

<div class="card" style="max-width:560px;">
    <div class="card-title">New Submission</div>

    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Subject *</label>
            <select name="subject_id" required>
                <option value="">-- Select Subject --</option>
                <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                <option value="<?php echo $sub['id']; ?>"
                    <?php echo ($sub['id'] === $preselect_subject) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($sub['subject_code'] . ' - ' . $sub['subject_name']); ?>
                    <?php echo $sub['is_elective'] ? ' [Elective]' : ''; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Assignment Title *</label>
            <input type="text" name="title"
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="e.g. Unit 1 Assignment - Python Basics">
        </div>

        <div class="form-group">
            <label>Upload File *</label>
            <input type="file" name="assignment_file"
                   accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg">
            <div class="hint">Allowed: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG. Max size: 5MB.</div>
        </div>

        <div style="display:flex;gap:10px;margin-top:6px;">
            <button type="submit" class="btn btn-primary">📤 Submit Assignment</button>
            <a href="dashboard.php" class="btn" style="background:#f0f2f5;color:#333;">Cancel</a>
        </div>
    </form>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-title">📋 Recent Submissions</div>
    <?php
    $recent = mysqli_query($conn,
        "SELECT s.title, sub.subject_code, sub.subject_name, s.submitted_at, m.marks_obtained
         FROM submissions s
         JOIN subjects sub ON s.subject_id = sub.id
         LEFT JOIN marks m ON s.id = m.submission_id
         WHERE s.student_id = $student_id
         ORDER BY s.submitted_at DESC LIMIT 5");
    ?>
    <?php if ($recent && mysqli_num_rows($recent) > 0): ?>
    <table>
        <tr><th>Title</th><th>Subject</th><th>Date</th><th>Marks</th></tr>
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
    <div class="empty-state" style="padding:20px;">No submissions yet.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
