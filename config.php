<?php
// ============================================================
// config.php - Database Configuration
// Assignment Submission Portal - GDCST
// ============================================================

session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'assignment_db');

define('SITE_NAME', 'Assignment Submission Portal');
define('COLLEGE_NAME', 'GDCST');
define('BASE_URL', 'http://localhost/Assignment_submission_portal/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='color:red;padding:20px;font-family:Arial;'>
        <h3>Database Connection Failed!</h3>
        <p>" . mysqli_connect_error() . "</p>
        <p>Please check your database settings in config.php</p>
    </div>");
}

mysqli_set_charset($conn, 'utf8');

// -------------------------------------------------------
// Helper Functions
// -------------------------------------------------------

function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect(BASE_URL . 'login.php');
    }
}

function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        redirect(BASE_URL . 'login.php');
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        redirect(BASE_URL . 'login.php');
    }
}

function showMessage($msg, $type = 'success') {
    $color = ($type === 'success') ? '#2e7d32' : '#c62828';
    $bg    = ($type === 'success') ? '#e8f5e9' : '#ffebee';
    $border= ($type === 'success') ? '#a5d6a7' : '#ef9a9a';
    echo "<div style='background:{$bg};color:{$color};border:1px solid {$border};
          padding:10px 16px;border-radius:5px;margin:10px 0;font-size:14px;'>
          {$msg}</div>";
}
?>
