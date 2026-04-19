<?php
require_once 'config.php';
$pageTitle = 'Change Password';
requireLogin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new_p   = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $uid     = (int)$_SESSION['user_id'];

    if (empty($current) || empty($new_p) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_p) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_p !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed_current = md5($current);
        $res = mysqli_query($conn, "SELECT id FROM users WHERE id=$uid AND password='$hashed_current'");
        if (!$res || mysqli_num_rows($res) === 0) {
            $error = 'Current password is incorrect.';
        } else {
            $hashed_new = md5($new_p);
            mysqli_query($conn, "UPDATE users SET password='$hashed_new' WHERE id=$uid");
            $success = 'Password changed successfully!';
        }
    }
}

// Determine back link
$back = BASE_URL;
if (isAdmin())   $back = BASE_URL . 'admin/dashboard.php';
if (isTeacher()) $back = BASE_URL . 'teacher/dashboard.php';
if (isStudent()) $back = BASE_URL . 'student/dashboard.php';

include 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="<?php echo $back; ?>">Dashboard</a> <span>&rsaquo;</span> Change Password
</div>

<div class="auth-page">
<div class="auth-box">
    <h2>🔒 Change Password</h2>
    <p class="auth-sub">Logged in as: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>

    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Enter current password">
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Min 6 characters">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Update Password</button>
    </form>

    <div class="auth-links">
        <a href="<?php echo $back; ?>">&larr; Back to Dashboard</a>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
