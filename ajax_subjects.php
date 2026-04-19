<?php
require_once 'config.php';
$semester_id = (int)($_GET['semester_id'] ?? 0);
echo '<option value="">-- Select Subject --</option>';
if ($semester_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM subjects WHERE semester_id=$semester_id ORDER BY subject_code");
    while ($row = mysqli_fetch_assoc($res)) {
        $elective = $row['is_elective'] ? ' [Elective]' : '';
        echo "<option value='{$row['id']}'>" . htmlspecialchars($row['subject_code'] . ' - ' . $row['subject_name'] . $elective) . "</option>";
    }
}
?>
