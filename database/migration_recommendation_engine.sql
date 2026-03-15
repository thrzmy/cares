-- CARES Recommendation Engine Migration
-- Adds RIASEC and Big Five match columns, populates all 13 programs

-- Step 1: Add new columns to course_requirements
ALTER TABLE course_requirements
    ADD COLUMN riasec_match VARCHAR(50) NULL AFTER min_part_1_raw_score,
    ADD COLUMN big_five_match VARCHAR(255) NULL AFTER riasec_match;

-- Step 2: Clear existing course_requirements and re-seed all 13 programs
DELETE FROM course_requirements;

INSERT INTO course_requirements (
    course_id, min_part_1_raw_score, riasec_match, big_five_match,
    allowed_shs_strands, choice_condition,
    base_weight_a, base_weight_b, base_weight_c
) VALUES
-- 1: BSED-ENG  (BS Sec Ed - English)
(1,  125, 'S,A',   'Openness,Extraversion,Agreeableness',       'Any', '1st or 2nd choice', 50, 30, 20),
-- 2: BSED-FIL  (BS Sec Ed - Filipino)
(2,  120, 'S,A',   'Openness,Agreeableness,Conscientiousness',  'Any', '1st or 2nd choice', 50, 30, 20),
-- 3: BSED-MATH (BS Sec Ed - Mathematics)
(3,  120, 'I,C',   'Conscientiousness,Openness',                'Any', '1st or 2nd choice', 50, 30, 20),
-- 4: BSED-SS   (BS Sec Ed - Social Studies)
(4,  125, 'S,I',   'Openness,Agreeableness',                    'Any', '1st or 2nd choice', 50, 30, 20),
-- 5: BSED-SCI  (BS Sec Ed - Science)
(5,  125, 'I,R',   'Openness,Conscientiousness',                'Any', '1st or 2nd choice', 50, 30, 20),
-- 6: BSHRDM    (Business - HR Dev't & Mgmt)
(6,  110, 'E,S',   'Extraversion,Agreeableness,Conscientiousness', 'Any', '1st or 2nd choice', 50, 30, 20),
-- 7: BSMM      (Business - Marketing Mgmt)
(7,  113, 'E,C',   'Extraversion,Conscientiousness',            'Any', '1st or 2nd choice', 50, 30, 20),
-- 8: BSOA      (Business - Office Admin)
(8,  105, 'C,E',   'Conscientiousness,Extraversion',            'Any', '1st or 2nd choice', 50, 30, 20),
-- 9: BSTM      (Hospitality - Tourism)
(9,  103, 'E,S',   'Extraversion,Agreeableness,Openness',       'Any', '1st or 2nd choice', 50, 30, 20),
-- 10: BSHM     (Hospitality - Hosp. Management)
(10,  92, 'S,E',   'Agreeableness,Extraversion',                'Any', '1st or 2nd choice', 50, 30, 20),
-- 11: BSIT     (CS - Information Technology)
(11, 103, 'I,C',   'Conscientiousness,Openness',                'Any', '1st or 2nd choice', 50, 30, 20),
-- 12: BSCS     (CS - Computer Science)
(12, 103, 'I,R',   'Openness,Conscientiousness',                'Any', '1st or 2nd choice', 50, 30, 20),
-- 13: BSP      (Arts & Sciences - Psychology)
(13, 122, 'S,I',   'Openness,Agreeableness,Conscientiousness',  'Any', '1st or 2nd choice', 50, 30, 20);
