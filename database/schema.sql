-- CARES database schema
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

-- Default admin account
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
    ('Admin', 'cares.cct@gmail.com', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'administrator', 'verified', '2026-02-01 08:55:00', 1, '2026-02-01 09:00:00', NULL, NULL, NULL, 1, 0);

SET FOREIGN_KEY_CHECKS = 1;
