<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="header-logo">
            <span class="college-short">GDCST</span>
            <div class="header-text">
                <span class="college-name">Post Graduate Department Of Computer Science &amp; Technology</span>
                <span class="portal-name">Assignment Submission Portal</span>
            </div>
        </div>
        <?php if (isLoggedIn()): ?>
        <nav class="header-nav">
            <span class="nav-user">
                👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                <span class="role-badge role-<?php echo $_SESSION['role']; ?>">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </span>
            </span>

            <?php if (isAdmin()): ?>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>admin/add_course.php">Courses</a>
                <a href="<?php echo BASE_URL; ?>admin/add_teacher.php">Teachers</a>
            <?php elseif (isTeacher()): ?>
                <a href="<?php echo BASE_URL; ?>teacher/dashboard.php">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>teacher/view_submissions.php">Submissions</a>
            <?php elseif (isStudent()): ?>
                <a href="<?php echo BASE_URL; ?>student/dashboard.php">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>student/submit_assignment.php">Submit</a>
                <a href="<?php echo BASE_URL; ?>student/view_marks.php">My Marks</a>
            <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>change_password.php">Change Password</a>
            
        </nav>
        <nav class="log">
            <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
        </nav>
        <?php endif; ?>
    </div>
</header>

<main class="site-main">
