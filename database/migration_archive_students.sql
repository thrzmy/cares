-- Legacy supplemental migration.
-- Preferred canonical migration: migration_v2.sql
-- Use this only on older schemas that already have the base students table.

ALTER TABLE students
ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted,
ADD INDEX idx_students_archived (is_archived);
