</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-col">
            <h4>GDCST</h4>
            <p>Post Graduate Department Of Computer Science &amp; Technology</p>
            <p>Computer Science Department</p>
           <!-- <p>&copy; <?php echo date('Y'); ?> All Rights Reserved</p> -->
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>register.php">Student Register</a></li>
                <li><a href="<?php echo BASE_URL; ?>forgot_password.php">Forgot Password</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Student</h4>
            <ul>
                <?php if (isLoggedIn() && isStudent()): ?>
                <li><a href="<?php echo BASE_URL; ?>student/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>student/submit_assignment.php">Submit Assignment</a></li>
                <li><a href="<?php echo BASE_URL; ?>student/view_assignments.php">My Submissions</a></li>
                <li><a href="<?php echo BASE_URL; ?>student/view_marks.php">My Marks</a></li>
                <li><a href="<?php echo BASE_URL; ?>student/update_semester.php">Update Semester</a></li>
                <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Admin / Teacher</h4>
            <ul>
                <?php if (isLoggedIn() && isAdmin()): ?>
                <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/add_course.php">Manage Courses</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/add_semester.php">Manage Semesters</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/add_subject.php">Manage Subjects</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/add_teacher.php">Manage Teachers</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/assign_teacher.php">Assign Teachers</a></li>
                <?php elseif (isLoggedIn() && isTeacher()): ?>
                <li><a href="<?php echo BASE_URL; ?>teacher/dashboard.php">Teacher Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>teacher/view_submissions.php">View Submissions</a></li>
                <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>login.php">Admin Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>login.php">Teacher Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Developed for GDCST  &nbsp;|&nbsp; Assignment Submission Portal</p>
        <p>&copy; <?php echo date('Y'); ?> All Rights Reserved</p>
    </div>
</footer>

</body>
</html>
