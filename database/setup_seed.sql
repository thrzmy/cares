-- CARES setup seed (generated from database/setup.json)
-- Safe for phpMyAdmin import (non-destructive upsert)
SET NAMES utf8mb4;

INSERT INTO courses (course_code, course_name, is_deleted) VALUES
    ('BSED-ENG', 'B.S. Secondary Education - Major in English', 0),
    ('BSED-FIL', 'B.S. Secondary Education - Major in Filipino', 0),
    ('BSED-MATH', 'B.S. Secondary Education - Major in Mathematics', 0),
    ('BSED-SS', 'B.S. Secondary Education - Major in Social Studies', 0),
    ('BSED-SCI', 'B.S. Secondary Education - Major in Science', 0),
    ('BSHRDM', 'B.S. in Human Resources Devt & Management', 0),
    ('BSMM', 'B.S. in Marketing Management', 0),
    ('BSOA', 'B.S. in Office Administration', 0),
    ('BSTM', 'B.S. Tourism Management', 0),
    ('BSHM', 'Hospitality Management', 0),
    ('BSIT', 'B.S. Information and Technology', 0),
    ('BSCS', 'B.S. Computer Science', 0),
    ('ABPSY', 'AB Psychology', 0)
ON DUPLICATE KEY UPDATE
    course_name = VALUES(course_name),
    is_deleted = 0,
    deleted_at = NULL;

INSERT INTO exam_parts (name, max_score, is_deleted) VALUES
    ('English', 30.00, 0),
    ('Filipino', 30.00, 0),
    ('Literature', 30.00, 0),
    ('Math', 30.00, 0),
    ('Science', 30.00, 0),
    ('Studies', 30.00, 0),
    ('Humanities', 30.00, 0)
ON DUPLICATE KEY UPDATE
    max_score = VALUES(max_score),
    is_deleted = 0,
    deleted_at = NULL;

-- Upsert weights by matching course_code and exam part name (no hardcoded IDs)
INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSED-ENG'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSED-FIL'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSED-MATH'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSED-SS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSED-SCI'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 30.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSHRDM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 30.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSMM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 25.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 25.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSOA'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 30.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSTM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 30.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSHM'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSIT'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 15.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 40.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 5.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'BSCS'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'English'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Filipino'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Literature'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Math'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 20.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Science'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Studies'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT c.id, ep.id, 10.00, 0, 1, 1
FROM courses c
JOIN exam_parts ep ON ep.name = 'Humanities'
WHERE c.course_code = 'ABPSY'
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

-- End of setup seed
