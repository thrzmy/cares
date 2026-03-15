-- Legacy supplemental migration for school_years/semesters only.
-- Preferred canonical migration: migration_v2.sql
-- This file is not sufficient by itself for the current semester feature set,
-- so it includes the archive columns expected by the app.

CREATE TABLE IF NOT EXISTS school_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_sy_active (is_active),
    INDEX idx_sy_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS semesters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_year_id INT UNSIGNED NOT NULL,
    name ENUM('1st Semester', '2nd Semester', 'Summer') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_sy_semester (school_year_id, name),
    INDEX idx_sem_active (is_active),
    INDEX idx_sem_deleted (is_deleted),
    FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
