-- Legacy supplemental migration for student semester linkage.
-- Preferred canonical migration: migration_v2.sql
-- Use this only on older schemas that have not yet adopted migration_v2.sql.

ALTER TABLE students
ADD COLUMN semester_id INT UNSIGNED NULL AFTER other_bonus_points,
ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted,
ADD CONSTRAINT fk_students_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL;
