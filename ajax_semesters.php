<?php
require_once 'config.php';
$course_id = (int)($_GET['course_id'] ?? 0);
echo '<option value="">-- Select Semester --</option>';
if ($course_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM semesters WHERE course_id=$course_id ORDER BY semester_number");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['semester_name']) . "</option>";
    }
}
?>
