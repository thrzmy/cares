-- CARES Migration v2: LEGACY upgrade path for older databases only
-- Fresh installs should use:
--   1. database/schema.sql
--   2. database/setup_seed.sql
--   3. optional database/seed.sql
-- Keep this file only for upgrading an existing pre-semester database in place.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── School Years ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS school_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_school_years_name (name),
    INDEX idx_school_years_active (is_active),
    INDEX idx_school_years_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Semesters ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS semesters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_year_id INT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uk_semesters_sy_name (school_year_id, name),
    INDEX idx_semesters_active (is_active),
    INDEX idx_semesters_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Exam Part Categories ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS exam_part_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_epc_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Add columns to students table ──────────────────────────────────
-- semester_id: links student to a semester
-- is_archived: for semester-based archival
ALTER TABLE students
    ADD COLUMN IF NOT EXISTS semester_id INT UNSIGNED NULL AFTER status,
    ADD COLUMN IF NOT EXISTS is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted;

-- ─── Add category_id to exam_parts ──────────────────────────────────
ALTER TABLE exam_parts
    ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED NULL AFTER max_score,
    ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER category_id;

-- ─── Seed exam part categories ──────────────────────────────────────
INSERT INTO exam_part_categories (name, sort_order) VALUES
    ('Part 1 - Achievement Test (Scholastic Ability)', 1),
    ('Part 2 - Aptitude Test Set A', 2),
    ('Part 2 - Aptitude Test Set B (SDS-RIASEC)', 3),
    ('Part 4 - Personality Characteristics', 4)
ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order);

-- ─── Update existing exam parts with category ──────────────────────
-- Part 1 subjects (already exist)
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 1 WHERE name = 'English';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 2 WHERE name = 'Filipino';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 3 WHERE name = 'Literature';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 4 WHERE name = 'Math';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 5 WHERE name = 'Science';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 6 WHERE name = 'Studies';
UPDATE exam_parts SET category_id = (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 1%'), sort_order = 7 WHERE name = 'Humanities';

-- ─── Insert Part 2 Set A exam parts ─────────────────────────────────
INSERT INTO exam_parts (name, max_score, category_id, sort_order) VALUES
    ('Teaching Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 1),
    ('Non-Verbal Reasoning / Spatial', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 2),
    ('Verbal Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 3),
    ('Inter-Personal Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 4),
    ('Environmental Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 5),
    ('Customer Service', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 6),
    ('Entrepreneurial', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 7),
    ('Clerical', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 8),
    ('Coding', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 9),
    ('Speed & Accuracy', 30.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set A'), 10)
ON DUPLICATE KEY UPDATE
    max_score = VALUES(max_score),
    category_id = VALUES(category_id),
    sort_order = VALUES(sort_order),
    is_deleted = 0,
    deleted_at = NULL;

-- ─── Insert Part 2 Set B (SDS-RIASEC) exam parts ───────────────────
INSERT INTO exam_parts (name, max_score, category_id, sort_order) VALUES
    ('Realistic', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 1),
    ('Investigative', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 2),
    ('Artistic', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 3),
    ('Social', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 4),
    ('Enterprising', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 5),
    ('Conventional', 5.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 2 - Aptitude Test Set B%'), 6)
ON DUPLICATE KEY UPDATE
    max_score = VALUES(max_score),
    category_id = VALUES(category_id),
    sort_order = VALUES(sort_order),
    is_deleted = 0,
    deleted_at = NULL;

-- ─── Insert Part 4 Personality Characteristics exam parts ───────────
INSERT INTO exam_parts (name, max_score, category_id, sort_order) VALUES
    ('Openness', 10.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 4%'), 1),
    ('Conscientiousness', 10.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 4%'), 2),
    ('Extraversion', 10.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 4%'), 3),
    ('Agreeableness', 10.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 4%'), 4),
    ('Neuroticism', 10.00, (SELECT id FROM exam_part_categories WHERE name LIKE 'Part 4%'), 5)
ON DUPLICATE KEY UPDATE
    max_score = VALUES(max_score),
    category_id = VALUES(category_id),
    sort_order = VALUES(sort_order),
    is_deleted = 0,
    deleted_at = NULL;

-- ─── Seed a default school year and semester ────────────────────────
INSERT INTO school_years (name, is_active, created_by) VALUES
    ('2025-2026', 1, 1)
ON DUPLICATE KEY UPDATE is_active = 1;

INSERT INTO semesters (school_year_id, name, is_active, created_by) VALUES
    ((SELECT id FROM school_years WHERE name = '2025-2026'), '1st Semester', 0, 1),
    ((SELECT id FROM school_years WHERE name = '2025-2026'), '2nd Semester', 1, 1)
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active);

SET FOREIGN_KEY_CHECKS = 1;
