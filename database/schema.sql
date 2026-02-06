-- CARES database schema + seed data
-- Compatible with MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS student_exam_scores;
DROP TABLE IF EXISTS weights;
DROP TABLE IF EXISTS exam_parts;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('administrator', 'admission') NOT NULL DEFAULT 'admission',
    account_status ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'verified',
    email_verified_at DATETIME NULL,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    rejected_by INT UNSIGNED NULL,
    rejected_at DATETIME NULL,
    rejection_reason VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    force_password_change TINYINT(1) NOT NULL DEFAULT 0,
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_status (account_status),
    INDEX idx_users_active (is_active),
    INDEX idx_users_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(50) NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    status ENUM('pending', 'admitted', 'rejected', 'waitlisted') NOT NULL DEFAULT 'pending',
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_students_id_number (id_number),
    CHECK (status <> 'admitted' OR id_number IS NOT NULL),
    INDEX idx_students_status (status),
    INDEX idx_students_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NULL,
    entity_id INT UNSIGNED NULL,
    details TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_password_resets_token (token_hash),
    INDEX idx_password_resets_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    code_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_verifications_user (user_id),
    INDEX idx_email_verifications_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_courses_code (course_code),
    INDEX idx_courses_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE exam_parts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_exam_parts_name (name),
    INDEX idx_exam_parts_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE student_exam_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    exam_part_id INT UNSIGNED NOT NULL,
    score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    encoded_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_student_exam_part (student_id, exam_part_id),
    INDEX idx_student_scores_student (student_id),
    INDEX idx_student_scores_part (exam_part_id),
    INDEX idx_student_scores_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE weights (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    exam_part_id INT UNSIGNED NOT NULL,
    weight DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_weights_course_part (course_id, exam_part_id),
    INDEX idx_weights_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (
    name,
    email,
    password,
    role,
    account_status,
    email_verified_at,
    verified_by,
    verified_at,
    rejected_by,
    rejected_at,
    rejection_reason,
    is_active,
    force_password_change
)
VALUES
    ('Admin', 'admin@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'administrator', 'verified', '2026-02-01 08:55:00', 1, '2026-02-01 09:00:00', NULL, NULL, NULL, 1, 0),
    ('Admission Personnel', 'admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'verified', '2026-02-01 09:05:00', 1, '2026-02-01 09:10:00', NULL, NULL, NULL, 1, 0),
    ('Juan De Vera', 'pending_admin@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'administrator', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1),
    ('Maria Quinto', 'rejected_admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'rejected', '2026-01-31 15:00:00', 1, '2026-01-31 15:30:00', 1, '2026-02-02 08:20:00', 'Incomplete requirements', 0, 1);

INSERT INTO courses (course_code, course_name)
VALUES
    ('BSCS', 'B.S. in Computer Science'),
    ('BSIT', 'B.S. in Information Technology'),
    ('BSECE', 'B.S. in Electronics Engineering'),
    ('BSCE', 'B.S. in Civil Engineering'),
    ('BSME', 'B.S. in Mechanical Engineering'),
    ('BSCPE', 'B.S. in Computer Engineering'),
    ('BSBA', 'B.S. in Business Administration'),
    ('BSA', 'B.S. in Accountancy'),
    ('BSHM', 'B.S. in Hospitality Management'),
    ('BSTM', 'B.S. in Tourism Management'),
    ('BSED', 'B.S. in Secondary Education'),
    ('BEED', 'B.S. in Elementary Education'),
    ('BSCrim', 'B.S. in Criminology');

INSERT INTO exam_parts (name, max_score)
VALUES
    ('Mathematics', 100.00),
    ('Science', 100.00),
    ('English', 100.00),
    ('Filipino', 100.00),
    ('Abstract Reasoning', 100.00);

INSERT INTO weights (course_id, exam_part_id, weight, created_by, updated_by)
VALUES
    -- BSCS
    (1, 1, 35.00, 1, 1),
    (1, 2, 25.00, 1, 1),
    (1, 3, 20.00, 1, 1),
    (1, 4, 10.00, 1, 1),
    (1, 5, 10.00, 1, 1),
    -- BSIT
    (2, 1, 30.00, 1, 1),
    (2, 2, 25.00, 1, 1),
    (2, 3, 20.00, 1, 1),
    (2, 4, 10.00, 1, 1),
    (2, 5, 15.00, 1, 1),
    -- BSECE
    (3, 1, 35.00, 1, 1),
    (3, 2, 30.00, 1, 1),
    (3, 3, 15.00, 1, 1),
    (3, 4, 5.00, 1, 1),
    (3, 5, 15.00, 1, 1),
    -- BSCE
    (4, 1, 35.00, 1, 1),
    (4, 2, 30.00, 1, 1),
    (4, 3, 15.00, 1, 1),
    (4, 4, 5.00, 1, 1),
    (4, 5, 15.00, 1, 1),
    -- BSME
    (5, 1, 35.00, 1, 1),
    (5, 2, 30.00, 1, 1),
    (5, 3, 15.00, 1, 1),
    (5, 4, 5.00, 1, 1),
    (5, 5, 15.00, 1, 1),
    -- BSCPE
    (6, 1, 35.00, 1, 1),
    (6, 2, 25.00, 1, 1),
    (6, 3, 20.00, 1, 1),
    (6, 4, 5.00, 1, 1),
    (6, 5, 15.00, 1, 1),
    -- BSBA
    (7, 1, 20.00, 1, 1),
    (7, 2, 15.00, 1, 1),
    (7, 3, 30.00, 1, 1),
    (7, 4, 25.00, 1, 1),
    (7, 5, 10.00, 1, 1),
    -- BSA
    (8, 1, 25.00, 1, 1),
    (8, 2, 15.00, 1, 1),
    (8, 3, 30.00, 1, 1),
    (8, 4, 20.00, 1, 1),
    (8, 5, 10.00, 1, 1),
    -- BSHM
    (9, 1, 15.00, 1, 1),
    (9, 2, 15.00, 1, 1),
    (9, 3, 30.00, 1, 1),
    (9, 4, 30.00, 1, 1),
    (9, 5, 10.00, 1, 1),
    -- BSTM
    (10, 1, 15.00, 1, 1),
    (10, 2, 15.00, 1, 1),
    (10, 3, 30.00, 1, 1),
    (10, 4, 30.00, 1, 1),
    (10, 5, 10.00, 1, 1),
    -- BSED
    (11, 1, 20.00, 1, 1),
    (11, 2, 20.00, 1, 1),
    (11, 3, 25.00, 1, 1),
    (11, 4, 25.00, 1, 1),
    (11, 5, 10.00, 1, 1),
    -- BEED
    (12, 1, 20.00, 1, 1),
    (12, 2, 20.00, 1, 1),
    (12, 3, 25.00, 1, 1),
    (12, 4, 25.00, 1, 1),
    (12, 5, 10.00, 1, 1),
    -- BSCrim
    (13, 1, 20.00, 1, 1),
    (13, 2, 25.00, 1, 1),
    (13, 3, 20.00, 1, 1),
    (13, 4, 15.00, 1, 1),
    (13, 5, 20.00, 1, 1);

INSERT INTO students (id_number, name, email, status, created_by)
VALUES
    ('S-2026-0001', 'Juan Dela Cruz', 'juan@student.local', 'pending', 1),
    ('S-2026-0002', 'Maria Santos', 'maria@student.local', 'admitted', 1),
    ('S-2026-0003', 'Jose Reyes', 'jose@student.local', 'waitlisted', 1),
    ('S-2026-0004', 'Ana Garcia', 'ana@student.local', 'rejected', 1),
    ('S-2026-0005', 'Mark Santos', 'mark.santos@student.local', 'pending', 1),
    ('S-2026-0006', 'Joy Reyes', 'joy.reyes@student.local', 'pending', 1),
    ('S-2026-0007', 'Paolo Cruz', 'paolo.cruz@student.local', 'admitted', 1),
    ('S-2026-0008', 'Mae Lloren', 'mae.lloren@student.local', 'waitlisted', 1),
    ('S-2026-0009', 'Gina Flores', 'gina.flores@student.local', 'pending', 1),
    ('S-2026-0010', 'Arman Lopez', 'arman.lopez@student.local', 'pending', 1),
    ('S-2026-0011', 'Karen Abad', 'karen.abad@student.local', 'admitted', 1),
    ('S-2026-0012', 'Rico Lim', 'rico.lim@student.local', 'pending', 1),
    ('S-2026-0013', 'Nica Ramos', 'nica.ramos@student.local', 'pending', 1),
    ('S-2026-0014', 'Erwin Uy', 'erwin.uy@student.local', 'pending', 1),
    ('S-2026-0015', 'Janice Palma', 'janice.palma@student.local', 'waitlisted', 1),
    ('S-2026-0016', 'Alvin Sy', 'alvin.sy@student.local', 'pending', 1),
    ('S-2026-0017', 'Cathy Pineda', 'cathy.pineda@student.local', 'pending', 1),
    ('S-2026-0018', 'Leo Santos', 'leo.santos@student.local', 'pending', 1),
    ('S-2026-0019', 'Bianca Torres', 'bianca.torres@student.local', 'pending', 1),
    ('S-2026-0020', 'Julius Manalo', 'julius.manalo@student.local', 'pending', 1),
    ('S-2026-0021', 'Hazel Dizon', 'hazel.dizon@student.local', 'admitted', 1),
    ('S-2026-0022', 'Mia Velasco', 'mia.velasco@student.local', 'pending', 1),
    ('S-2026-0023', 'Carl De Vera', 'carl.devera@student.local', 'pending', 1),
    ('S-2026-0024', 'Shane Bautista', 'shane.bautista@student.local', 'pending', 1),
    ('S-2026-0025', 'Owen Pastor', 'owen.pastor@student.local', 'pending', 1),
    ('S-2026-0026', 'Clarice Ong', 'clarice.ong@student.local', 'pending', 1),
    ('S-2026-0027', 'Vince Ortega', 'vince.ortega@student.local', 'rejected', 1),
    ('S-2026-0028', 'Trisha Luna', 'trisha.luna@student.local', 'pending', 1),
    ('S-2026-0029', 'Noel Suarez', 'noel.suarez@student.local', 'pending', 1),
    ('S-2026-0030', 'Lara Cruz', 'lara.cruz@student.local', 'pending', 1);

INSERT INTO student_exam_scores (student_id, exam_part_id, score, encoded_by, updated_by)
VALUES
    (2, 1, 92.00, 1, 1),
    (2, 2, 88.00, 1, 1),
    (2, 3, 90.00, 1, 1),
    (2, 4, 84.00, 1, 1),
    (2, 5, 86.00, 1, 1),
    (3, 1, 78.00, 1, 1),
    (3, 2, 82.00, 1, 1),
    (3, 3, 80.00, 1, 1),
    (3, 4, 79.00, 1, 1),
    (3, 5, 77.00, 1, 1),
    (4, 1, 60.00, 1, 1),
    (4, 2, 58.00, 1, 1),
    (4, 3, 62.00, 1, 1),
    (4, 4, 59.00, 1, 1),
    (4, 5, 61.00, 1, 1),
    (5, 1, 85.00, 1, 1),
    (5, 2, 81.00, 1, 1),
    (5, 3, 78.00, 1, 1),
    (5, 4, 75.00, 1, 1),
    (5, 5, 83.00, 1, 1),
    (6, 1, 72.00, 1, 1),
    (6, 2, 70.00, 1, 1),
    (6, 3, 76.00, 1, 1),
    (6, 4, 80.00, 1, 1),
    (6, 5, 74.00, 1, 1),
    (7, 1, 88.00, 1, 1),
    (7, 2, 86.00, 1, 1),
    (7, 3, 79.00, 1, 1),
    (7, 4, 73.00, 1, 1),
    (7, 5, 85.00, 1, 1),
    (8, 1, 69.00, 1, 1),
    (8, 2, 72.00, 1, 1),
    (8, 3, 74.00, 1, 1),
    (8, 4, 78.00, 1, 1),
    (8, 5, 71.00, 1, 1),
    (9, 1, 83.00, 1, 1),
    (9, 2, 79.00, 1, 1),
    (9, 3, 82.00, 1, 1),
    (9, 4, 77.00, 1, 1),
    (9, 5, 80.00, 1, 1),
    (10, 1, 75.00, 1, 1),
    (10, 2, 74.00, 1, 1),
    (10, 3, 70.00, 1, 1),
    (10, 4, 68.00, 1, 1),
    (10, 5, 72.00, 1, 1),
    (11, 1, 90.00, 1, 1),
    (11, 2, 88.00, 1, 1),
    (11, 3, 92.00, 1, 1),
    (11, 4, 89.00, 1, 1),
    (11, 5, 91.00, 1, 1),
    (12, 1, 66.00, 1, 1),
    (12, 2, 64.00, 1, 1),
    (12, 3, 70.00, 1, 1),
    (12, 4, 72.00, 1, 1),
    (12, 5, 68.00, 1, 1),
    (13, 1, 79.00, 1, 1),
    (13, 2, 77.00, 1, 1),
    (13, 3, 81.00, 1, 1),
    (13, 4, 75.00, 1, 1),
    (13, 5, 78.00, 1, 1),
    (14, 1, 73.00, 1, 1),
    (14, 2, 71.00, 1, 1),
    (14, 3, 69.00, 1, 1),
    (14, 4, 74.00, 1, 1),
    (14, 5, 70.00, 1, 1),
    (15, 1, 82.00, 1, 1),
    (15, 2, 80.00, 1, 1),
    (15, 3, 76.00, 1, 1),
    (15, 4, 78.00, 1, 1),
    (15, 5, 79.00, 1, 1),
    (16, 1, 68.00, 1, 1),
    (16, 2, 66.00, 1, 1),
    (16, 3, 72.00, 1, 1),
    (16, 4, 70.00, 1, 1),
    (16, 5, 67.00, 1, 1),
    (17, 1, 87.00, 1, 1),
    (17, 2, 83.00, 1, 1),
    (17, 3, 85.00, 1, 1),
    (17, 4, 82.00, 1, 1),
    (17, 5, 84.00, 1, 1),
    (18, 1, 74.00, 1, 1),
    (18, 2, 73.00, 1, 1),
    (18, 3, 71.00, 1, 1),
    (18, 4, 69.00, 1, 1),
    (18, 5, 72.00, 1, 1),
    (19, 1, 91.00, 1, 1),
    (19, 2, 89.00, 1, 1),
    (19, 3, 88.00, 1, 1),
    (19, 4, 87.00, 1, 1),
    (19, 5, 90.00, 1, 1),
    (20, 1, 65.00, 1, 1),
    (20, 2, 63.00, 1, 1),
    (20, 3, 68.00, 1, 1),
    (20, 4, 66.00, 1, 1),
    (20, 5, 64.00, 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
