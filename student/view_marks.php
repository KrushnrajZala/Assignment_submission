<?php
require_once '../config.php';
$pageTitle = 'My Marks';
requireStudent();

$student_id = (int)$_SESSION['user_id'];

// Fetch current semester info
$student_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT u.*, c.course_name, c.course_code, s.semester_name, s.id AS cur_sem_id
     FROM users u
     LEFT JOIN courses c ON u.course_id = c.id
     LEFT JOIN semesters s ON u.semester_id = s.id
     WHERE u.id = $student_id LIMIT 1"));

$current_sem_id = (int)$student_info['cur_sem_id'];

// Marks for current semester
$current_marks = mysqli_query($conn,
    "SELECT sub.subject_code, sub.subject_name, sub.is_elective,
            s.title, s.submitted_at,
            m.marks_obtained, m.review, m.marked_at
     FROM submissions s
     JOIN subjects sub ON s.subject_id = sub.id
     LEFT JOIN marks m ON s.id = m.submission_id
     WHERE s.student_id = $student_id
       AND s.semester_id = $current_sem_id
     ORDER BY sub.subject_code, s.submitted_at DESC");

// Calculate total/average for current semester
$totals_res = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(m.id) AS marked, SUM(m.marks_obtained) AS total, AVG(m.marks_obtained) AS avg
     FROM submissions s
     JOIN marks m ON s.id = m.submission_id
     WHERE s.student_id = $student_id AND s.semester_id = $current_sem_id"));

// OLD semester marks (history)
$history_sems = mysqli_query($conn,
    "SELECT DISTINCT sem.id, sem.semester_name, sem.semester_number
     FROM submissions s
     JOIN semesters sem ON s.semester_id = sem.id
     WHERE s.student_id = $student_id
       AND s.semester_id != $current_sem_id
     ORDER BY sem.semester_number");

include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> My Marks
</div>
<div class="page-title">📊 My Marks &amp; Reviews</div>

<!-- Summary Card -->
<div class="card" style="background:linear-gradient(90deg,#e8f5e9,#fff);border-left:5px solid #2e7d32;padding:16px 20px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-size:16px;font-weight:bold;color:#2e7d32;">
                Current: <?php echo htmlspecialchars($student_info['semester_name'] ?? 'N/A'); ?>
            </div>
            <div style="color:#555;font-size:13px;margin-top:4px;">
                <?php echo htmlspecialchars($student_info['course_name'] ?? ''); ?>
            </div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;">
            <div style="text-align:center;">
                <div style="font-size:24px;font-weight:bold;color:#2e7d32;">
                    <?php echo $totals_res['marked'] ?? 0; ?>
                </div>
                <div style="font-size:12px;color:#888;">Assignments Marked</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:24px;font-weight:bold;color:#1565c0;">
                    <?php echo $totals_res['total'] ? number_format($totals_res['total'], 1) : '0'; ?>
                </div>
                <div style="font-size:12px;color:#888;">Total Marks</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:24px;font-weight:bold;color:#e65100;">
                    <?php echo $totals_res['avg'] ? number_format($totals_res['avg'], 1) : 'N/A'; ?>/5
                </div>
                <div style="font-size:12px;color:#888;">Average</div>
            </div>
        </div>
    </div>
</div>

<!-- Current Semester Marks -->
<div class="card">
    <div class="card-title">
        📘 <?php echo htmlspecialchars($student_info['semester_name'] ?? 'Current Semester'); ?> &mdash; Marks
    </div>
    <?php if ($current_marks && mysqli_num_rows($current_marks) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Assignment Title</th>
            <th>Submitted</th>
            <th>Marks /5</th>
            <th>Grade</th>
            <th>Teacher Review</th>
        </tr>
        <?php $i=1; while ($m = mysqli_fetch_assoc($current_marks)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td>
                <strong><?php echo htmlspecialchars($m['subject_code']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($m['subject_name']); ?></small>
                <?php if ($m['is_elective']): ?><br><span class="badge badge-orange" style="font-size:10px;">Elective</span><?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($m['title']); ?></td>
            <td><?php echo date('d M Y', strtotime($m['submitted_at'])); ?></td>
            <td style="text-align:center;">
                <?php if ($m['marks_obtained'] !== null): ?>
                <span class="marks-score"><?php echo $m['marks_obtained']; ?></span>
                <!-- Stars -->
                <div class="marks-stars" style="font-size:13px;">
                    <?php
                    $mo = (float)$m['marks_obtained'];
                    for ($s = 1; $s <= 5; $s++) {
                        if ($mo >= $s) echo '★';
                        elseif ($mo >= $s - 0.5) echo '½';
                        else echo '☆';
                    }
                    ?>
                </div>
                <?php else: ?>
                <span class="badge badge-orange">Pending</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <?php if ($m['marks_obtained'] !== null):
                    $mo = (float)$m['marks_obtained'];
                    if ($mo >= 4.5) { $grade='O'; $gc='badge-green'; }
                    elseif ($mo >= 4.0) { $grade='A+'; $gc='badge-green'; }
                    elseif ($mo >= 3.5) { $grade='A'; $gc='badge-blue'; }
                    elseif ($mo >= 3.0) { $grade='B+'; $gc='badge-blue'; }
                    elseif ($mo >= 2.5) { $grade='B'; $gc='badge-grey'; }
                    elseif ($mo >= 2.0) { $grade='C'; $gc='badge-grey'; }
                    else { $grade='F'; $gc='badge-orange'; }
                ?>
                <span class="badge <?php echo $gc; ?>"><?php echo $grade; ?></span>
                <?php else: echo '<span class="text-muted">—</span>'; endif; ?>
            </td>
            <td style="max-width:200px;font-size:12px;color:#555;">
                <?php echo !empty($m['review']) ? nl2br(htmlspecialchars($m['review'])) : '<span class="text-muted">No review yet</span>'; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📊</div>
        No marks for current semester yet. <a href="submit_assignment.php">Submit assignments</a> to get marks.
    </div>
    <?php endif; ?>
</div>

<!-- Old Semester History -->
<?php if ($history_sems && mysqli_num_rows($history_sems) > 0): ?>
<div class="card">
    <div class="card-title">📁 Previous Semester Marks (History)</div>
    <?php while ($hs = mysqli_fetch_assoc($history_sems)):
        $hsid = (int)$hs['id'];
        $old_marks = mysqli_query($conn,
            "SELECT sub.subject_code, sub.subject_name,
                    s.title, s.submitted_at, m.marks_obtained, m.review
             FROM submissions s
             JOIN subjects sub ON s.subject_id = sub.id
             LEFT JOIN marks m ON s.id = m.submission_id
             WHERE s.student_id = $student_id AND s.semester_id = $hsid
             ORDER BY sub.subject_code");
        $old_totals = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT SUM(m.marks_obtained) AS total, AVG(m.marks_obtained) AS avg, COUNT(m.id) AS cnt
             FROM submissions s JOIN marks m ON s.id=m.submission_id
             WHERE s.student_id=$student_id AND s.semester_id=$hsid"));
    ?>
    <div style="background:#f5f7fa;border:1px solid #dde3ea;border-radius:6px;padding:14px 16px;margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
            <strong style="color:#1565c0;"><?php echo htmlspecialchars($hs['semester_name']); ?></strong>
            <div style="font-size:13px;color:#555;">
                Marked: <?php echo $old_totals['cnt'] ?? 0; ?> &nbsp;|&nbsp;
                Total: <strong><?php echo $old_totals['total'] ? number_format($old_totals['total'],1) : '0'; ?></strong> &nbsp;|&nbsp;
                Avg: <strong><?php echo $old_totals['avg'] ? number_format($old_totals['avg'],1) : 'N/A'; ?>/5</strong>
            </div>
        </div>
        <?php if ($old_marks && mysqli_num_rows($old_marks) > 0): ?>
        <table style="font-size:13px;">
            <tr><th>Subject</th><th>Title</th><th>Submitted</th><th>Marks /5</th><th>Review</th></tr>
            <?php while ($om = mysqli_fetch_assoc($old_marks)): ?>
            <tr>
                <td><?php echo htmlspecialchars($om['subject_code']); ?></td>
                <td><?php echo htmlspecialchars($om['title']); ?></td>
                <td><?php echo date('d M Y', strtotime($om['submitted_at'])); ?></td>
                <td>
                    <?php if ($om['marks_obtained'] !== null): ?>
                    <span class="marks-score"><?php echo $om['marks_obtained']; ?></span>
                    <?php else: ?>
                    <span class="badge badge-grey">N/A</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#555;max-width:160px;">
                    <?php echo !empty($om['review']) ? htmlspecialchars(substr($om['review'],0,80)) . (strlen($om['review'])>80?'...':'') : '—'; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <div class="text-muted" style="font-size:13px;">No submission records found for this semester.</div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Grade Legend -->
<div class="card">
    <div class="card-title">📋 Grade Scale (out of 5)</div>
    <table style="max-width:400px;">
        <tr><th>Marks</th><th>Grade</th><th>Remark</th></tr>
        <tr><td>4.5 – 5.0</td><td><span class="badge badge-green">O</span></td><td>Outstanding</td></tr>
        <tr><td>4.0 – 4.4</td><td><span class="badge badge-green">A+</span></td><td>Excellent</td></tr>
        <tr><td>3.5 – 3.9</td><td><span class="badge badge-blue">A</span></td><td>Very Good</td></tr>
        <tr><td>3.0 – 3.4</td><td><span class="badge badge-blue">B+</span></td><td>Good</td></tr>
        <tr><td>2.5 – 2.9</td><td><span class="badge badge-grey">B</span></td><td>Average</td></tr>
        <tr><td>2.0 – 2.4</td><td><span class="badge badge-grey">C</span></td><td>Below Average</td></tr>
        <tr><td>0.0 – 1.9</td><td><span class="badge badge-orange">F</span></td><td>Fail</td></tr>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
