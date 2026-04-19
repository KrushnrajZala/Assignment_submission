<?php
require_once '../config.php';
$pageTitle = 'Manage Teachers';
requireAdmin();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($conn, $_POST['name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed = md5($password);
            mysqli_query($conn, "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$hashed','teacher')");
            $success = "Teacher account created! Email: $email | Password: " . htmlspecialchars($password);
        }
    }
}

$teachers = mysqli_query($conn, "SELECT u.*, GROUP_CONCAT(sub.subject_name SEPARATOR ', ') AS subjects
                                  FROM users u
                                  LEFT JOIN teacher_assignments ta ON u.id=ta.teacher_id
                                  LEFT JOIN subjects sub ON ta.subject_id=sub.id
                                  WHERE u.role='teacher'
                                  GROUP BY u.id ORDER BY u.name");
include '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span>&rsaquo;</span> Manage Teachers
</div>
<div class="page-title">👩‍🏫 Manage Teachers</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">
<div class="card">
    <div class="card-title">Create Teacher Account</div>
    <div class="alert alert-info" style="font-size:12px;">
        Teachers can only be created by Admin. They login using assigned email &amp; password.
    </div>
    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Teacher Name *</label>
            <input type="text" name="name" placeholder="e.g. Prof. Ramesh Shah">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="e.g. mca1.teacher@gdcst.ac.in">
            <div class="hint">Example: mca1@gdcst.ac.in (for MCA Sem 1 teacher)</div>
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="text" name="password" placeholder="Set a password (min 6 chars)">
        </div>
        <button type="submit" class="btn btn-success">Create Teacher</button>
    </form>
</div>

<div class="card">
    <div class="card-title">All Teachers</div>
    <?php if ($teachers && mysqli_num_rows($teachers) > 0): ?>
    <div class="table-wrapper">
    <table>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Assigned Subjects</th><th>Actions</th></tr>
        <?php $i=1; while ($t = mysqli_fetch_assoc($teachers)): ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($t['name']); ?></td>
            <td><?php echo htmlspecialchars($t['email']); ?></td>
            <td><?php echo $t['subjects'] ? htmlspecialchars($t['subjects']) : '<span class="text-muted">Not assigned</span>'; ?></td>
            <td><a href="assign_teacher.php?teacher_id=<?php echo $t['id']; ?>" class="btn btn-primary btn-sm">Assign</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state"><div class="empty-icon">👩‍🏫</div>No teachers added yet.</div>
    <?php endif; ?>
</div>
</div>

<?php include '../includes/footer.php'; ?>
