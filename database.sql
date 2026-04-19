-- ============================================================
-- Assignment Submission Portal - GDCST
-- Database: assignment_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS assignment_db;
USE assignment_db;

-- -------------------------------------------------------
-- Table: courses
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: semesters
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semester_name VARCHAR(50) NOT NULL,
    semester_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: subjects
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    subject_name VARCHAR(150) NOT NULL,
    subject_code VARCHAR(30) NOT NULL,
    is_elective TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    course_id INT DEFAULT NULL,
    semester_id INT DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL
);

-- -------------------------------------------------------
-- Table: teacher_assignments (teacher assigned to subject/semester)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    semester_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: assignments (created implicitly via submissions)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    semester_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: submissions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    semester_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: marks
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    teacher_id INT NOT NULL,
    marks_obtained DECIMAL(3,1) DEFAULT NULL,
    review TEXT DEFAULT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- Table: semester_history (track old semester marks)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS semester_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester_id INT NOT NULL,
    total_marks DECIMAL(5,2) DEFAULT 0,
    moved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Admin user (email: admin@gdcst.ac.in | password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@gdcst.ac.in', MD5('Admin@123'), 'admin');

-- MCA Course
INSERT INTO courses (course_name, course_code) VALUES
('Master of Computer Applications', 'MCA');

-- MCA Semesters
INSERT INTO semesters (course_id, semester_name, semester_number) VALUES
(1, 'MCA Semester 1', 1),
(1, 'MCA Semester 2', 2),
(1, 'MCA Semester 3', 3),
(1, 'MCA Semester 4', 4);

-- MCA Sem 1 Subjects
INSERT INTO subjects (semester_id, subject_name, subject_code, is_elective) VALUES
(1, 'Python Programming', 'PS01CMCA51', 0),
(1, 'Data Structures', 'PS01CMCA52', 0),
(1, 'Database Management Systems', 'PS01CMCA53', 0),
(1, 'Computer Networks', 'PS01CMCA54', 0),
(1, 'Elective: Web Technologies', 'PS01EMCA51', 1);

-- MCA Sem 2 Subjects
INSERT INTO subjects (semester_id, subject_name, subject_code, is_elective) VALUES
(2, 'Advanced Java Programming', 'PS02CMCA51', 0),
(2, 'Operating Systems', 'PS02CMCA52', 0),
(2, 'Software Engineering', 'PS02CMCA53', 0),
(2, 'Design and Analysis of Algorithms', 'PS02CMCA54', 0),
(2, 'Elective: Mobile Application Development', 'PS02EMCA51', 1);

-- MCA Sem 3 Subjects
INSERT INTO subjects (semester_id, subject_name, subject_code, is_elective) VALUES
(3, 'Machine Learning', 'PS03CMCA51', 0),
(3, 'Cloud Computing', 'PS03CMCA52', 0),
(3, 'Information Security', 'PS03CMCA53', 0),
(3, 'Advanced Database Systems', 'PS03CMCA54', 0),
(3, 'Elective: Internet of Things', 'PS03EMCA51', 1);

-- MCA Sem 4 Subjects
INSERT INTO subjects (semester_id, subject_name, subject_code, is_elective) VALUES
(4, 'Artificial Intelligence', 'PS04CMCA51', 0),
(4, 'Big Data Analytics', 'PS04CMCA52', 0),
(4, 'Project Work', 'PS04CMCA53', 0),
(4, 'Research Methodology', 'PS04CMCA54', 0),
(4, 'Elective: Deep Learning', 'PS04EMCA51', 1);

-- ============================================================
-- END OF database.sql
-- ============================================================
