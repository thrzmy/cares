-- CARES setup seed
-- Import after database/schema.sql
-- Seeds stable configuration data: academic years, semesters,
-- exam part categories, core exam parts, courses, and weights.
SET NAMES utf8mb4;

INSERT INTO school_years (id, name, is_active, is_archived, is_deleted, created_by) VALUES
    (1, '2025-2026', 1, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    is_active = 1,
    is_archived = 0,
    is_deleted = 0,
    created_by = 1;

INSERT INTO semesters (id, school_year_id, name, is_active, is_archived, is_deleted, created_by) VALUES
    (1, 1, '1st Semester', 0, 0, 0, 1),
    (2, 1, '2nd Semester', 1, 0, 0, 1),
    (3, 1, 'Summer', 0, 0, 0, 1)
ON DUPLICATE KEY UPDATE
    school_year_id = VALUES(school_year_id),
    is_active = VALUES(is_active),
    is_archived = 0,
    is_deleted = 0,
    created_by = 1;

INSERT INTO exam_part_categories (name, sort_order) VALUES
    ('Part 1 - Achievement Test (Scholastic Ability)', 1),
    ('Part 2 - Aptitude Test Set A', 2),
    ('Part 2 - Aptitude Test Set B (SDS-RIASEC)', 3),
    ('Part 4 - Personality Characteristics', 4)
ON DUPLICATE KEY UPDATE
    sort_order = VALUES(sort_order);

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

INSERT INTO exam_parts (name, max_score, category_id, sort_order, is_deleted) VALUES
    ('English', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 1, 0),
    ('Filipino', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 2, 0),
    ('Literature', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 3, 0),
    ('Math', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 4, 0),
    ('Science', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 5, 0),
    ('Studies', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 6, 0),
    ('Humanities', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 1 - Achievement Test (Scholastic Ability)'), 7, 0),
    ('Teaching Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 11, 0),
    ('Non-Verbal Reasoning / Spatial', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 12, 0),
    ('Verbal Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 13, 0),
    ('Inter-Personal Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 14, 0),
    ('Environmental Aptitude', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 15, 0),
    ('Customer Service', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 16, 0),
    ('Entrepreneurial', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 17, 0),
    ('Clerical', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 18, 0),
    ('Coding', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 19, 0),
    ('Speed & Accuracy', 30.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 20, 0),
    ('Realistic', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 21, 0),
    ('Investigative', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 22, 0),
    ('Artistic', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 23, 0),
    ('Social', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 24, 0),
    ('Enterprising', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 25, 0),
    ('Conventional', 5.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set B (SDS-RIASEC)'), 26, 0),
    ('Openness', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 4 - Personality Characteristics'), 31, 0),
    ('Conscientiousness', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 4 - Personality Characteristics'), 32, 0),
    ('Extraversion', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 4 - Personality Characteristics'), 33, 0),
    ('Agreeableness', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 4 - Personality Characteristics'), 34, 0),
    ('Neuroticism', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 4 - Personality Characteristics'), 35, 0)
ON DUPLICATE KEY UPDATE
    max_score = VALUES(max_score),
    category_id = VALUES(category_id),
    sort_order = VALUES(sort_order),
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
