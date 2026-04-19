<?php
require_once 'config.php';
$pageTitle = 'Home';

if (isLoggedIn()) {
    if (isAdmin())   redirect(BASE_URL . 'admin/dashboard.php');
    if (isTeacher()) redirect(BASE_URL . 'teacher/dashboard.php');
    if (isStudent()) redirect(BASE_URL . 'student/dashboard.php');
}

include 'includes/header.php';
?>

<div class="hero">
    <h1>📚 Assignment Submission Portal</h1>
    <p>Goverment Degree College of Science &amp; Technology &mdash; MCA Department</p>
    <div class="hero-buttons">
        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-white">Login</a>
        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-white">Student Register</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dash-card dash-card-blue">
        <div class="dash-number">🎓</div>
        <div class="dash-label">MCA Course</div>
    </div>
    <div class="dash-card dash-card-green">
        <div class="dash-number">4</div>
        <div class="dash-label">Semesters</div>
    </div>
    <div class="dash-card dash-card-orange">
        <div class="dash-number">20</div>
        <div class="dash-label">Subjects</div>
    </div>
    <div class="dash-card dash-card-purple">
        <div class="dash-number">📝</div>
        <div class="dash-label">Submit Assignments</div>
    </div>
</div>

<div class="card">
    <div class="card-title">About This Portal</div>
    <p style="line-height:1.8;color:#555;">
        The GDCST Assignment Submission Portal allows MCA students to submit their internal assignments online.
        Teachers can review submissions, assign marks (out of 5), and add review comments.
        The Admin manages courses, semesters, subjects, and teacher accounts.
    </p>
    <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary">Login to Portal</a>
        <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-success">Student Registration</a>
        <a href="<?php echo BASE_URL; ?>forgot_password.php" class="btn" style="background:#f0f2f5;color:#333;">Forgot Password</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
