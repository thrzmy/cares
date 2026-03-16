-- CARES setup seed
-- Import after database/schema.sql
-- Seeds stable configuration data: academic years, semesters,
-- exam part categories, core exam parts, courses, and workbook-based matrix values.
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

INSERT INTO courses (course_code, course_name, course_category, is_deleted) VALUES
    ('BSED-ENG', 'Major in English', 'BS Secondary Education', 0),
    ('BSED-FIL', 'Major in Filipino', 'BS Secondary Education', 0),
    ('BSED-MATH', 'Major in Mathematics', 'BS Secondary Education', 0),
    ('BSED-SS', 'Major in Social Studies', 'BS Secondary Education', 0),
    ('BSED-SCI', 'Major in Science', 'BS Secondary Education', 0),
    ('BSHRDM', 'Major in Human Resources Development Management', 'School of Business and Management', 0),
    ('BSMM', 'Major in Marketing Management', 'School of Business and Management', 0),
    ('BSOA', 'Major in Office Administration', 'School of Business and Management', 0),
    ('BSTM', 'Major in Tourism', 'School of Hospitality and Tourism Management', 0),
    ('BSHM', 'Major in Hospitality Management', 'School of Hospitality and Tourism Management', 0),
    ('BSIT', 'Information and Technology', 'School of Computer Studies', 0),
    ('BSCS', 'Computer Science', 'School of Computer Studies', 0),
    ('ABPSY', 'Psychology', 'School of Arts and Sciences', 0)
ON DUPLICATE KEY UPDATE
    course_name = VALUES(course_name),
    course_category = VALUES(course_category),
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
    ('Teaching Aptitude', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 11, 0),
    ('Non-Verbal Reasoning / Spatial', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 12, 0),
    ('Verbal Aptitude', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 13, 0),
    ('Inter-Personal Aptitude', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 14, 0),
    ('Environmental Aptitude', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 15, 0),
    ('Customer Service', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 16, 0),
    ('Entrepreneurial', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 17, 0),
    ('Clerical', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 18, 0),
    ('Coding', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 19, 0),
    ('Speed & Accuracy', 10.00, (SELECT id FROM exam_part_categories WHERE name = 'Part 2 - Aptitude Test Set A'), 20, 0),
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

-- Upsert workbook-based matrix values from Admission-Scoring.xlsx
-- Part 2 Set B uses the clarified mapping:
-- R = 5, I = 2, A = 3, S = 1, E = 1, C = 1
INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
SELECT
    c.id,
    ep.id,
    m.weight,
    0,
    1,
    1
FROM (
    SELECT 'BSED-ENG' AS course_code, 'English' AS part_name, 25.00 AS weight UNION ALL
    SELECT 'BSED-ENG', 'Filipino', 20.00 UNION ALL
    SELECT 'BSED-ENG', 'Literature', 20.00 UNION ALL
    SELECT 'BSED-ENG', 'Math', 15.00 UNION ALL
    SELECT 'BSED-ENG', 'Science', 15.00 UNION ALL
    SELECT 'BSED-ENG', 'Studies', 15.00 UNION ALL
    SELECT 'BSED-ENG', 'Humanities', 15.00 UNION ALL

    SELECT 'BSED-FIL', 'English', 15.00 UNION ALL
    SELECT 'BSED-FIL', 'Filipino', 25.00 UNION ALL
    SELECT 'BSED-FIL', 'Literature', 20.00 UNION ALL
    SELECT 'BSED-FIL', 'Math', 15.00 UNION ALL
    SELECT 'BSED-FIL', 'Science', 15.00 UNION ALL
    SELECT 'BSED-FIL', 'Studies', 15.00 UNION ALL
    SELECT 'BSED-FIL', 'Humanities', 15.00 UNION ALL

    SELECT 'BSED-MATH', 'English', 15.00 UNION ALL
    SELECT 'BSED-MATH', 'Filipino', 20.00 UNION ALL
    SELECT 'BSED-MATH', 'Literature', 15.00 UNION ALL
    SELECT 'BSED-MATH', 'Math', 25.00 UNION ALL
    SELECT 'BSED-MATH', 'Science', 15.00 UNION ALL
    SELECT 'BSED-MATH', 'Studies', 15.00 UNION ALL
    SELECT 'BSED-MATH', 'Humanities', 15.00 UNION ALL

    SELECT 'BSED-SS', 'English', 20.00 UNION ALL
    SELECT 'BSED-SS', 'Filipino', 15.00 UNION ALL
    SELECT 'BSED-SS', 'Literature', 15.00 UNION ALL
    SELECT 'BSED-SS', 'Math', 10.00 UNION ALL
    SELECT 'BSED-SS', 'Science', 15.00 UNION ALL
    SELECT 'BSED-SS', 'Studies', 25.00 UNION ALL
    SELECT 'BSED-SS', 'Humanities', 25.00 UNION ALL

    SELECT 'BSED-SCI', 'English', 20.00 UNION ALL
    SELECT 'BSED-SCI', 'Filipino', 15.00 UNION ALL
    SELECT 'BSED-SCI', 'Literature', 15.00 UNION ALL
    SELECT 'BSED-SCI', 'Math', 20.00 UNION ALL
    SELECT 'BSED-SCI', 'Science', 25.00 UNION ALL
    SELECT 'BSED-SCI', 'Studies', 15.00 UNION ALL
    SELECT 'BSED-SCI', 'Humanities', 15.00 UNION ALL

    SELECT 'BSHRDM', 'English', 20.00 UNION ALL
    SELECT 'BSHRDM', 'Filipino', 15.00 UNION ALL
    SELECT 'BSHRDM', 'Literature', 15.00 UNION ALL
    SELECT 'BSHRDM', 'Math', 15.00 UNION ALL
    SELECT 'BSHRDM', 'Science', 15.00 UNION ALL
    SELECT 'BSHRDM', 'Studies', 15.00 UNION ALL
    SELECT 'BSHRDM', 'Humanities', 15.00 UNION ALL

    SELECT 'BSMM', 'English', 20.00 UNION ALL
    SELECT 'BSMM', 'Filipino', 15.00 UNION ALL
    SELECT 'BSMM', 'Literature', 15.00 UNION ALL
    SELECT 'BSMM', 'Math', 20.00 UNION ALL
    SELECT 'BSMM', 'Science', 15.00 UNION ALL
    SELECT 'BSMM', 'Studies', 15.00 UNION ALL
    SELECT 'BSMM', 'Humanities', 13.00 UNION ALL

    SELECT 'BSOA', 'English', 15.00 UNION ALL
    SELECT 'BSOA', 'Filipino', 15.00 UNION ALL
    SELECT 'BSOA', 'Literature', 15.00 UNION ALL
    SELECT 'BSOA', 'Math', 15.00 UNION ALL
    SELECT 'BSOA', 'Science', 15.00 UNION ALL
    SELECT 'BSOA', 'Studies', 15.00 UNION ALL
    SELECT 'BSOA', 'Humanities', 15.00 UNION ALL

    SELECT 'BSTM', 'English', 20.00 UNION ALL
    SELECT 'BSTM', 'Filipino', 20.00 UNION ALL
    SELECT 'BSTM', 'Literature', 12.00 UNION ALL
    SELECT 'BSTM', 'Math', 12.00 UNION ALL
    SELECT 'BSTM', 'Science', 12.00 UNION ALL
    SELECT 'BSTM', 'Studies', 15.00 UNION ALL
    SELECT 'BSTM', 'Humanities', 12.00 UNION ALL

    SELECT 'BSHM', 'English', 20.00 UNION ALL
    SELECT 'BSHM', 'Filipino', 12.00 UNION ALL
    SELECT 'BSHM', 'Literature', 12.00 UNION ALL
    SELECT 'BSHM', 'Math', 12.00 UNION ALL
    SELECT 'BSHM', 'Science', 12.00 UNION ALL
    SELECT 'BSHM', 'Studies', 12.00 UNION ALL
    SELECT 'BSHM', 'Humanities', 12.00 UNION ALL

    SELECT 'BSIT', 'English', 15.00 UNION ALL
    SELECT 'BSIT', 'Filipino', 12.00 UNION ALL
    SELECT 'BSIT', 'Literature', 12.00 UNION ALL
    SELECT 'BSIT', 'Math', 20.00 UNION ALL
    SELECT 'BSIT', 'Science', 20.00 UNION ALL
    SELECT 'BSIT', 'Studies', 12.00 UNION ALL
    SELECT 'BSIT', 'Humanities', 12.00 UNION ALL

    SELECT 'BSCS', 'English', 15.00 UNION ALL
    SELECT 'BSCS', 'Filipino', 12.00 UNION ALL
    SELECT 'BSCS', 'Literature', 12.00 UNION ALL
    SELECT 'BSCS', 'Math', 20.00 UNION ALL
    SELECT 'BSCS', 'Science', 20.00 UNION ALL
    SELECT 'BSCS', 'Studies', 12.00 UNION ALL
    SELECT 'BSCS', 'Humanities', 12.00 UNION ALL

    SELECT 'ABPSY', 'English', 25.00 UNION ALL
    SELECT 'ABPSY', 'Filipino', 15.00 UNION ALL
    SELECT 'ABPSY', 'Literature', 12.00 UNION ALL
    SELECT 'ABPSY', 'Math', 20.00 UNION ALL
    SELECT 'ABPSY', 'Science', 20.00 UNION ALL
    SELECT 'ABPSY', 'Studies', 15.00 UNION ALL
    SELECT 'ABPSY', 'Humanities', 15.00 UNION ALL

    SELECT 'BSED-ENG', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'BSED-ENG', 'Non-Verbal Reasoning / Spatial', 5.00 UNION ALL
    SELECT 'BSED-ENG', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-ENG', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-ENG', 'Environmental Aptitude', 5.00 UNION ALL
    SELECT 'BSED-ENG', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Clerical', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Coding', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Realistic', 5.00 UNION ALL
    SELECT 'BSED-ENG', 'Investigative', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Artistic', 3.00 UNION ALL
    SELECT 'BSED-ENG', 'Social', 1.00 UNION ALL
    SELECT 'BSED-ENG', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Conventional', 0.00 UNION ALL
    SELECT 'BSED-ENG', 'Openness', 8.00 UNION ALL
    SELECT 'BSED-ENG', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSED-ENG', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSED-ENG', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSED-ENG', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSED-FIL', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'BSED-FIL', 'Non-Verbal Reasoning / Spatial', 5.00 UNION ALL
    SELECT 'BSED-FIL', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-FIL', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-FIL', 'Environmental Aptitude', 5.00 UNION ALL
    SELECT 'BSED-FIL', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Clerical', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Coding', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Realistic', 5.00 UNION ALL
    SELECT 'BSED-FIL', 'Investigative', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Artistic', 3.00 UNION ALL
    SELECT 'BSED-FIL', 'Social', 1.00 UNION ALL
    SELECT 'BSED-FIL', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Conventional', 0.00 UNION ALL
    SELECT 'BSED-FIL', 'Openness', 8.00 UNION ALL
    SELECT 'BSED-FIL', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSED-FIL', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSED-FIL', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSED-FIL', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSED-MATH', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'BSED-MATH', 'Non-Verbal Reasoning / Spatial', 8.00 UNION ALL
    SELECT 'BSED-MATH', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-MATH', 'Inter-Personal Aptitude', 3.00 UNION ALL
    SELECT 'BSED-MATH', 'Environmental Aptitude', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Clerical', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Coding', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Realistic', 5.00 UNION ALL
    SELECT 'BSED-MATH', 'Investigative', 2.00 UNION ALL
    SELECT 'BSED-MATH', 'Artistic', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Social', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSED-MATH', 'Conventional', 1.00 UNION ALL
    SELECT 'BSED-MATH', 'Openness', 8.00 UNION ALL
    SELECT 'BSED-MATH', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSED-MATH', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSED-MATH', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSED-MATH', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSED-SS', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SS', 'Non-Verbal Reasoning / Spatial', 5.00 UNION ALL
    SELECT 'BSED-SS', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SS', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SS', 'Environmental Aptitude', 9.00 UNION ALL
    SELECT 'BSED-SS', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Clerical', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Coding', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Realistic', 5.00 UNION ALL
    SELECT 'BSED-SS', 'Investigative', 2.00 UNION ALL
    SELECT 'BSED-SS', 'Artistic', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Social', 1.00 UNION ALL
    SELECT 'BSED-SS', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Conventional', 0.00 UNION ALL
    SELECT 'BSED-SS', 'Openness', 8.00 UNION ALL
    SELECT 'BSED-SS', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSED-SS', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSED-SS', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSED-SS', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSED-SCI', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Non-Verbal Reasoning / Spatial', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Inter-Personal Aptitude', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Environmental Aptitude', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Clerical', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Coding', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Realistic', 5.00 UNION ALL
    SELECT 'BSED-SCI', 'Investigative', 2.00 UNION ALL
    SELECT 'BSED-SCI', 'Artistic', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Social', 1.00 UNION ALL
    SELECT 'BSED-SCI', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Conventional', 0.00 UNION ALL
    SELECT 'BSED-SCI', 'Openness', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSED-SCI', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSED-SCI', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSED-SCI', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSHRDM', 'Teaching Aptitude', 3.00 UNION ALL
    SELECT 'BSHRDM', 'Non-Verbal Reasoning / Spatial', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Verbal Aptitude', 8.00 UNION ALL
    SELECT 'BSHRDM', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'BSHRDM', 'Environmental Aptitude', 3.00 UNION ALL
    SELECT 'BSHRDM', 'Customer Service', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Entrepreneurial', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Clerical', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Coding', 0.00 UNION ALL
    SELECT 'BSHRDM', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSHRDM', 'Realistic', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Investigative', 0.00 UNION ALL
    SELECT 'BSHRDM', 'Artistic', 0.00 UNION ALL
    SELECT 'BSHRDM', 'Social', 1.00 UNION ALL
    SELECT 'BSHRDM', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSHRDM', 'Conventional', 1.00 UNION ALL
    SELECT 'BSHRDM', 'Openness', 8.00 UNION ALL
    SELECT 'BSHRDM', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSHRDM', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSHRDM', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSHRDM', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSMM', 'Teaching Aptitude', 0.00 UNION ALL
    SELECT 'BSMM', 'Non-Verbal Reasoning / Spatial', 0.00 UNION ALL
    SELECT 'BSMM', 'Verbal Aptitude', 0.00 UNION ALL
    SELECT 'BSMM', 'Inter-Personal Aptitude', 9.00 UNION ALL
    SELECT 'BSMM', 'Environmental Aptitude', 0.00 UNION ALL
    SELECT 'BSMM', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSMM', 'Entrepreneurial', 5.00 UNION ALL
    SELECT 'BSMM', 'Clerical', 0.00 UNION ALL
    SELECT 'BSMM', 'Coding', 0.00 UNION ALL
    SELECT 'BSMM', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSMM', 'Realistic', 0.00 UNION ALL
    SELECT 'BSMM', 'Investigative', 0.00 UNION ALL
    SELECT 'BSMM', 'Artistic', 3.00 UNION ALL
    SELECT 'BSMM', 'Social', 0.00 UNION ALL
    SELECT 'BSMM', 'Enterprising', 1.00 UNION ALL
    SELECT 'BSMM', 'Conventional', 0.00 UNION ALL
    SELECT 'BSMM', 'Openness', 8.00 UNION ALL
    SELECT 'BSMM', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSMM', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSMM', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSMM', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSOA', 'Teaching Aptitude', 0.00 UNION ALL
    SELECT 'BSOA', 'Non-Verbal Reasoning / Spatial', 0.00 UNION ALL
    SELECT 'BSOA', 'Verbal Aptitude', 0.00 UNION ALL
    SELECT 'BSOA', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'BSOA', 'Environmental Aptitude', 0.00 UNION ALL
    SELECT 'BSOA', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSOA', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSOA', 'Clerical', 8.00 UNION ALL
    SELECT 'BSOA', 'Coding', 0.00 UNION ALL
    SELECT 'BSOA', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSOA', 'Realistic', 5.00 UNION ALL
    SELECT 'BSOA', 'Investigative', 0.00 UNION ALL
    SELECT 'BSOA', 'Artistic', 0.00 UNION ALL
    SELECT 'BSOA', 'Social', 0.00 UNION ALL
    SELECT 'BSOA', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSOA', 'Conventional', 1.00 UNION ALL
    SELECT 'BSOA', 'Openness', 8.00 UNION ALL
    SELECT 'BSOA', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSOA', 'Extraversion', 5.00 UNION ALL
    SELECT 'BSOA', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSOA', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSTM', 'Teaching Aptitude', 0.00 UNION ALL
    SELECT 'BSTM', 'Non-Verbal Reasoning / Spatial', 7.00 UNION ALL
    SELECT 'BSTM', 'Verbal Aptitude', 7.00 UNION ALL
    SELECT 'BSTM', 'Inter-Personal Aptitude', 7.00 UNION ALL
    SELECT 'BSTM', 'Environmental Aptitude', 3.00 UNION ALL
    SELECT 'BSTM', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSTM', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSTM', 'Clerical', 0.00 UNION ALL
    SELECT 'BSTM', 'Coding', 0.00 UNION ALL
    SELECT 'BSTM', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSTM', 'Realistic', 5.00 UNION ALL
    SELECT 'BSTM', 'Investigative', 0.00 UNION ALL
    SELECT 'BSTM', 'Artistic', 0.00 UNION ALL
    SELECT 'BSTM', 'Social', 1.00 UNION ALL
    SELECT 'BSTM', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSTM', 'Conventional', 1.00 UNION ALL
    SELECT 'BSTM', 'Openness', 8.00 UNION ALL
    SELECT 'BSTM', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSTM', 'Extraversion', 8.00 UNION ALL
    SELECT 'BSTM', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSTM', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSHM', 'Teaching Aptitude', 0.00 UNION ALL
    SELECT 'BSHM', 'Non-Verbal Reasoning / Spatial', 7.00 UNION ALL
    SELECT 'BSHM', 'Verbal Aptitude', 7.00 UNION ALL
    SELECT 'BSHM', 'Inter-Personal Aptitude', 7.00 UNION ALL
    SELECT 'BSHM', 'Environmental Aptitude', 7.00 UNION ALL
    SELECT 'BSHM', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSHM', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSHM', 'Clerical', 0.00 UNION ALL
    SELECT 'BSHM', 'Coding', 0.00 UNION ALL
    SELECT 'BSHM', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSHM', 'Realistic', 5.00 UNION ALL
    SELECT 'BSHM', 'Investigative', 0.00 UNION ALL
    SELECT 'BSHM', 'Artistic', 0.00 UNION ALL
    SELECT 'BSHM', 'Social', 1.00 UNION ALL
    SELECT 'BSHM', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSHM', 'Conventional', 1.00 UNION ALL
    SELECT 'BSHM', 'Openness', 8.00 UNION ALL
    SELECT 'BSHM', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSHM', 'Extraversion', 8.00 UNION ALL
    SELECT 'BSHM', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSHM', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSIT', 'Teaching Aptitude', 5.00 UNION ALL
    SELECT 'BSIT', 'Non-Verbal Reasoning / Spatial', 9.00 UNION ALL
    SELECT 'BSIT', 'Verbal Aptitude', 0.00 UNION ALL
    SELECT 'BSIT', 'Inter-Personal Aptitude', 0.00 UNION ALL
    SELECT 'BSIT', 'Environmental Aptitude', 0.00 UNION ALL
    SELECT 'BSIT', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSIT', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSIT', 'Clerical', 0.00 UNION ALL
    SELECT 'BSIT', 'Coding', 8.00 UNION ALL
    SELECT 'BSIT', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSIT', 'Realistic', 5.00 UNION ALL
    SELECT 'BSIT', 'Investigative', 2.00 UNION ALL
    SELECT 'BSIT', 'Artistic', 0.00 UNION ALL
    SELECT 'BSIT', 'Social', 0.00 UNION ALL
    SELECT 'BSIT', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSIT', 'Conventional', 1.00 UNION ALL
    SELECT 'BSIT', 'Openness', 8.00 UNION ALL
    SELECT 'BSIT', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSIT', 'Extraversion', 4.00 UNION ALL
    SELECT 'BSIT', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSIT', 'Neuroticism', 5.00 UNION ALL

    SELECT 'BSCS', 'Teaching Aptitude', 5.00 UNION ALL
    SELECT 'BSCS', 'Non-Verbal Reasoning / Spatial', 9.00 UNION ALL
    SELECT 'BSCS', 'Verbal Aptitude', 0.00 UNION ALL
    SELECT 'BSCS', 'Inter-Personal Aptitude', 0.00 UNION ALL
    SELECT 'BSCS', 'Environmental Aptitude', 0.00 UNION ALL
    SELECT 'BSCS', 'Customer Service', 0.00 UNION ALL
    SELECT 'BSCS', 'Entrepreneurial', 0.00 UNION ALL
    SELECT 'BSCS', 'Clerical', 0.00 UNION ALL
    SELECT 'BSCS', 'Coding', 8.00 UNION ALL
    SELECT 'BSCS', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'BSCS', 'Realistic', 5.00 UNION ALL
    SELECT 'BSCS', 'Investigative', 2.00 UNION ALL
    SELECT 'BSCS', 'Artistic', 0.00 UNION ALL
    SELECT 'BSCS', 'Social', 0.00 UNION ALL
    SELECT 'BSCS', 'Enterprising', 0.00 UNION ALL
    SELECT 'BSCS', 'Conventional', 1.00 UNION ALL
    SELECT 'BSCS', 'Openness', 8.00 UNION ALL
    SELECT 'BSCS', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'BSCS', 'Extraversion', 4.00 UNION ALL
    SELECT 'BSCS', 'Agreeableness', 8.00 UNION ALL
    SELECT 'BSCS', 'Neuroticism', 5.00 UNION ALL

    SELECT 'ABPSY', 'Teaching Aptitude', 8.00 UNION ALL
    SELECT 'ABPSY', 'Non-Verbal Reasoning / Spatial', 8.00 UNION ALL
    SELECT 'ABPSY', 'Verbal Aptitude', 9.00 UNION ALL
    SELECT 'ABPSY', 'Inter-Personal Aptitude', 8.00 UNION ALL
    SELECT 'ABPSY', 'Environmental Aptitude', 5.00 UNION ALL
    SELECT 'ABPSY', 'Customer Service', 5.00 UNION ALL
    SELECT 'ABPSY', 'Entrepreneurial', 5.00 UNION ALL
    SELECT 'ABPSY', 'Clerical', 5.00 UNION ALL
    SELECT 'ABPSY', 'Coding', 0.00 UNION ALL
    SELECT 'ABPSY', 'Speed & Accuracy', 0.00 UNION ALL
    SELECT 'ABPSY', 'Realistic', 0.00 UNION ALL
    SELECT 'ABPSY', 'Investigative', 2.00 UNION ALL
    SELECT 'ABPSY', 'Artistic', 0.00 UNION ALL
    SELECT 'ABPSY', 'Social', 1.00 UNION ALL
    SELECT 'ABPSY', 'Enterprising', 0.00 UNION ALL
    SELECT 'ABPSY', 'Conventional', 0.00 UNION ALL
    SELECT 'ABPSY', 'Openness', 9.00 UNION ALL
    SELECT 'ABPSY', 'Conscientiousness', 9.00 UNION ALL
    SELECT 'ABPSY', 'Extraversion', 5.00 UNION ALL
    SELECT 'ABPSY', 'Agreeableness', 8.00 UNION ALL
    SELECT 'ABPSY', 'Neuroticism', 5.00
) AS m
INNER JOIN courses c
    ON c.course_code = m.course_code
INNER JOIN exam_parts ep
    ON ep.name = m.part_name
ON DUPLICATE KEY UPDATE
    weight = VALUES(weight),
    is_deleted = 0,
    deleted_at = NULL,
    deleted_by = NULL,
    updated_by = 1;

-- End of setup seed
