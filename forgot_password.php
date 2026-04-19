<?php
require_once 'config.php';
$pageTitle = 'Forgot Password';

$error   = '';
$success = '';
$step    = 1; // 1=email, 2=reset

// Step 2: Reset password using token
if (isset($_GET['token'])) {
    $token = sanitize($conn, $_GET['token']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token' LIMIT 1");
    if (!$res || mysqli_num_rows($res) === 0) {
        $error = 'Invalid or expired reset link.';
    } else {
        $step = 2;
        $user_row = mysqli_fetch_assoc($res);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
            $np = $_POST['new_password'] ?? '';
            $cp = $_POST['confirm_password'] ?? '';
            if (empty($np) || empty($cp)) {
                $error = 'All fields are required.';
            } elseif (strlen($np) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($np !== $cp) {
                $error = 'Passwords do not match.';
            } else {
                $hashed = md5($np);
                $uid = $user_row['id'];
                mysqli_query($conn, "UPDATE users SET password='$hashed', reset_token=NULL WHERE id=$uid");
                $success = 'Password reset successfully! <a href="login.php">Login now</a>.';
                $step = 3;
            }
        }
    }
}

// Step 1: Email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && $step === 1) {
    $email = sanitize($conn, $_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email.';
    } else {
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
        if (!$res || mysqli_num_rows($res) === 0) {
            $error = 'No account found with this email.';
        } else {
            $token = md5(uniqid($email, true));
            mysqli_query($conn, "UPDATE users SET reset_token='$token' WHERE email='$email'");
            $reset_link = BASE_URL . "forgot_password.php?token=$token";
            $success = "Password reset link generated. Since email is not configured on localhost, use this link directly:<br>
                        <a href='$reset_link'>$reset_link</a>";
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
<div class="auth-box" style="max-width:440px;">
    <h2>🔑 Forgot Password</h2>
    <p class="auth-sub">GDCST Assignment Submission Portal</p>

    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <?php if ($step === 1 && empty($success)): ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Registered Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Get Reset Link</button>
    </form>
    <?php elseif ($step === 2): ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
    </form>
    <?php endif; ?>

    <div class="auth-links">
        <a href="login.php">&larr; Back to Login</a>
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
