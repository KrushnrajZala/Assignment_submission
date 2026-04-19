<?php
require_once 'config.php';
$pageTitle = 'Login';

if (isLoggedIn()) {
    if (isAdmin())   redirect(BASE_URL . 'admin/dashboard.php');
    if (isTeacher()) redirect(BASE_URL . 'teacher/dashboard.php');
    if (isStudent()) redirect(BASE_URL . 'student/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $hashed = md5($password);
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$hashed' LIMIT 1");
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email']= $user['email'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['course_id'] = $user['course_id'];
            $_SESSION['semester_id'] = $user['semester_id'];

            if ($user['role'] === 'admin')   redirect(BASE_URL . 'admin/dashboard.php');
            if ($user['role'] === 'teacher') redirect(BASE_URL . 'teacher/dashboard.php');
            if ($user['role'] === 'student') redirect(BASE_URL . 'student/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
<div class="auth-box">
    <h2>🔐 Login</h2>
    <p class="auth-sub">Assignment Submission Portal &mdash; GDCST</p>

    <?php if ($error) echo "<div class='alert alert-error'>$error</div>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email" autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:6px;">Login</button>
    </form>

    <div class="auth-links">
        New student? <a href="register.php">Register here</a><br>
        <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <div style="margin-top:16px;padding:12px;background:#f0f2f5;border-radius:5px;font-size:12px;color:#555;">
        <strong>Demo Admin Login:</strong><br>
        Email: admin@gdcst.ac.in &nbsp;|&nbsp; Password: Admin@123
    </div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
