<?php
require_once 'config.php';
$pageTitle = 'Student Register';

if (isLoggedIn()) redirect(BASE_URL . 'student/dashboard.php');

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($conn, $_POST['name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $course_id   = (int)($_POST['course_id'] ?? 0);
    $semester_id = (int)($_POST['semester_id'] ?? 0);

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($course_id <= 0 || $semester_id <= 0) {
        $error = 'Please select course and semester.';
    } else {
        // Check email exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'This email is already registered.';
        } else {
            $hashed = md5($password);
            $insert = mysqli_query($conn, "INSERT INTO users (name, email, password, role, course_id, semester_id)
                                          VALUES ('$name','$email','$hashed','student','$course_id','$semester_id')");
            if ($insert) {
                $success = 'Registration successful! You can now <a href="login.php">Login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

// Fetch courses
$courses_result = mysqli_query($conn, "SELECT * FROM courses");

include 'includes/header.php';
?>

<div class="auth-page">
<div class="auth-box" style="max-width:500px;">
    <h2>🎓 Student Registration</h2>
    <p class="auth-sub">GDCST &mdash; MCA Department</p>

    <?php if ($error)   echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

    <?php if (empty($success)): ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Enter your full name">
        </div>
        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" placeholder="Repeat password">
            </div>
        </div>
        <div class="form-group">
            <label>Course *</label>
            <select name="course_id" id="course_id" onchange="loadSemesters(this.value)">
                <option value="">-- Select Course --</option>
                <?php while ($c = mysqli_fetch_assoc($courses_result)): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo (($_POST['course_id'] ?? '') == $c['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['course_name']); ?> (<?php echo $c['course_code']; ?>)
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Semester *</label>
            <select name="semester_id" id="semester_id">
                <option value="">-- Select Semester --</option>
                <?php
                // Pre-load semesters if course was selected
                if (!empty($_POST['course_id'])) {
                    $cid = (int)$_POST['course_id'];
                    $sem_res = mysqli_query($conn, "SELECT * FROM semesters WHERE course_id=$cid ORDER BY semester_number");
                    while ($s = mysqli_fetch_assoc($sem_res)) {
                        $sel = (($_POST['semester_id'] ?? '') == $s['id']) ? 'selected' : '';
                        echo "<option value='{$s['id']}' $sel>{$s['semester_name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:6px;">Register</button>
    </form>
    <?php endif; ?>

    <div class="auth-links">
        Already registered? <a href="login.php">Login here</a><br>
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>
</div>

<script>
function loadSemesters(courseId) {
    var sel = document.getElementById('semester_id');
    sel.innerHTML = '<option value="">Loading...</option>';
    if (!courseId) { sel.innerHTML = '<option value="">-- Select Semester --</option>'; return; }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo BASE_URL; ?>ajax_semesters.php?course_id=' + courseId, true);
    xhr.onload = function() {
        if (xhr.status === 200) sel.innerHTML = xhr.responseText;
    };
    xhr.send();
}
</script>

<?php include 'includes/footer.php'; ?>
