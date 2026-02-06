-- CARES seed data
-- Compatible with MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Clear data for repeatable seeding
TRUNCATE TABLE student_exam_scores;
TRUNCATE TABLE students;
TRUNCATE TABLE weights;
TRUNCATE TABLE exam_parts;
TRUNCATE TABLE courses;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE email_verifications;
TRUNCATE TABLE logs;
DELETE FROM users WHERE role = 'admission';
ALTER TABLE users AUTO_INCREMENT = 2;

INSERT INTO users (
    id,
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
    force_password_change,
    failed_login_attempts,
    locked_until,
    is_deleted,
    created_at,
    updated_at,
    deleted_at
)
VALUES
    (2, 'Admission Personnel', 'admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'verified', '2026-02-01 09:05:00', 1, '2026-02-01 09:10:00', NULL, NULL, NULL, 1, 0, 0, NULL, 0, '2026-01-21 08:15:00', '2026-02-01 09:10:00', NULL),
    (3, 'Maria Quinto', 'rejected_admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'rejected', '2026-01-31 15:00:00', 1, '2026-01-31 15:30:00', 1, '2026-02-02 08:20:00', 'Incomplete requirements', 0, 1, 0, NULL, 0, '2026-01-25 11:45:00', '2026-02-02 08:20:00', NULL),
    (4, 'Joy Reyes', 'inactive_admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'verified', '2026-01-29 13:15:00', 1, '2026-01-29 13:25:00', NULL, NULL, NULL, 0, 0, 0, NULL, 0, '2026-01-24 14:10:00', '2026-01-29 13:25:00', NULL),
    (5, 'Mark Santos', 'locked_admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'verified', '2026-01-28 09:12:00', 1, '2026-01-28 09:20:00', NULL, NULL, NULL, 1, 0, 5, '2026-02-07 09:30:00', 0, '2026-01-22 08:30:00', '2026-02-03 09:30:00', NULL),
    (6, 'Cathy Pineda', 'deleted_admission@cares.local', '$2y$10$u8xUhBOWcGw2Vsn9FEAJ6.1ibjgSAOatpZBt10sBvCwqkm0KtXvFa', 'admission', 'verified', '2026-01-27 08:05:00', 1, '2026-01-27 08:10:00', NULL, NULL, NULL, 0, 0, 0, NULL, 1, '2026-01-18 07:50:00', '2026-02-01 10:15:00', '2026-02-01 10:15:00');

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
    (1, 1, 35.00, 2, 2),
    (1, 2, 25.00, 2, 2),
    (1, 3, 20.00, 2, 2),
    (1, 4, 10.00, 2, 2),
    (1, 5, 10.00, 2, 2),
    -- BSIT
    (2, 1, 30.00, 2, 2),
    (2, 2, 25.00, 2, 2),
    (2, 3, 20.00, 2, 2),
    (2, 4, 10.00, 2, 2),
    (2, 5, 15.00, 2, 2),
    -- BSECE
    (3, 1, 35.00, 2, 2),
    (3, 2, 30.00, 2, 2),
    (3, 3, 15.00, 2, 2),
    (3, 4, 5.00, 2, 2),
    (3, 5, 15.00, 2, 2),
    -- BSCE
    (4, 1, 35.00, 2, 2),
    (4, 2, 30.00, 2, 2),
    (4, 3, 15.00, 2, 2),
    (4, 4, 5.00, 2, 2),
    (4, 5, 15.00, 2, 2),
    -- BSME
    (5, 1, 35.00, 2, 2),
    (5, 2, 30.00, 2, 2),
    (5, 3, 15.00, 2, 2),
    (5, 4, 5.00, 2, 2),
    (5, 5, 15.00, 2, 2),
    -- BSCPE
    (6, 1, 35.00, 2, 2),
    (6, 2, 25.00, 2, 2),
    (6, 3, 20.00, 2, 2),
    (6, 4, 5.00, 2, 2),
    (6, 5, 15.00, 2, 2),
    -- BSBA
    (7, 1, 20.00, 2, 2),
    (7, 2, 15.00, 2, 2),
    (7, 3, 30.00, 2, 2),
    (7, 4, 25.00, 2, 2),
    (7, 5, 10.00, 2, 2),
    -- BSA
    (8, 1, 25.00, 2, 2),
    (8, 2, 15.00, 2, 2),
    (8, 3, 30.00, 2, 2),
    (8, 4, 20.00, 2, 2),
    (8, 5, 10.00, 2, 2),
    -- BSHM
    (9, 1, 15.00, 2, 2),
    (9, 2, 15.00, 2, 2),
    (9, 3, 30.00, 2, 2),
    (9, 4, 30.00, 2, 2),
    (9, 5, 10.00, 2, 2),
    -- BSTM
    (10, 1, 15.00, 2, 2),
    (10, 2, 15.00, 2, 2),
    (10, 3, 30.00, 2, 2),
    (10, 4, 30.00, 2, 2),
    (10, 5, 10.00, 2, 2),
    -- BSED
    (11, 1, 20.00, 2, 2),
    (11, 2, 20.00, 2, 2),
    (11, 3, 25.00, 2, 2),
    (11, 4, 25.00, 2, 2),
    (11, 5, 10.00, 2, 2),
    -- BEED
    (12, 1, 20.00, 2, 2),
    (12, 2, 20.00, 2, 2),
    (12, 3, 25.00, 2, 2),
    (12, 4, 25.00, 2, 2),
    (12, 5, 10.00, 2, 2),
    -- BSCrim
    (13, 1, 20.00, 2, 2),
    (13, 2, 25.00, 2, 2),
    (13, 3, 20.00, 2, 2),
    (13, 4, 15.00, 2, 2),
    (13, 5, 20.00, 2, 2);

INSERT INTO students (
    id_number,
    name,
    email,
    status,
    is_deleted,
    created_by,
    updated_by,
    created_at,
    updated_at,
    deleted_at
)
VALUES
    (NULL, 'Juan Dela Cruz', 'juan@student.local', 'pending', 0, 2, NULL, '2026-01-26 10:10:00', NULL, NULL),
    ('S-2026-0002', 'Maria Santos', 'maria@student.local', 'admitted', 0, 2, 2, '2026-01-26 11:25:00', '2026-02-02 09:30:00', NULL),
    (NULL, 'Jose Reyes', 'jose@student.local', 'waitlisted', 0, 2, NULL, '2026-01-27 09:05:00', NULL, NULL),
    (NULL, 'Ana Garcia', 'ana@student.local', 'rejected', 0, 2, 2, '2026-01-27 10:40:00', '2026-02-01 14:10:00', NULL),
    ('S-2026-0005', 'Mark Santos', 'mark.santos@student.local', 'pending', 0, 2, NULL, '2026-01-27 13:20:00', NULL, NULL),
    ('S-2026-0006', 'Joy Reyes', 'joy.reyes@student.local', 'admitted', 0, 2, 2, '2026-01-28 08:50:00', '2026-02-01 09:05:00', NULL),
    ('S-2026-0007', 'Paolo Cruz', 'paolo.cruz@student.local', 'admitted', 0, 2, 2, '2026-01-28 09:10:00', '2026-02-01 09:20:00', NULL),
    (NULL, 'Mae Lloren', 'mae.lloren@student.local', 'waitlisted', 0, 2, NULL, '2026-01-28 14:00:00', NULL, NULL),
    (NULL, 'Gina Flores', 'gina.flores@student.local', 'pending', 0, 2, NULL, '2026-01-29 08:15:00', NULL, NULL),
    (NULL, 'Arman Lopez', 'arman.lopez@student.local', 'rejected', 0, 2, 2, '2026-01-29 10:00:00', '2026-02-01 11:25:00', NULL),
    (NULL, 'Karen Abad', 'karen.abad@student.local', 'pending', 0, 2, NULL, '2026-01-29 15:45:00', NULL, NULL),
    ('S-2026-0012', 'Rico Lim', 'rico.lim@student.local', 'admitted', 0, 2, 2, '2026-01-30 09:05:00', '2026-02-02 08:45:00', NULL),
    (NULL, 'Nica Ramos', 'nica.ramos@student.local', 'pending', 0, 2, NULL, '2026-01-30 10:30:00', NULL, NULL),
    ('S-2026-0014', 'Erwin Uy', 'erwin.uy@student.local', 'waitlisted', 0, 2, NULL, '2026-01-30 12:10:00', NULL, NULL),
    ('S-2026-0015', 'Janice Palma', 'janice.palma@student.local', 'admitted', 0, 2, 2, '2026-01-30 15:40:00', '2026-02-02 13:20:00', NULL),
    (NULL, 'Alvin Sy', 'alvin.sy@student.local', 'pending', 0, 2, NULL, '2026-01-31 08:50:00', NULL, NULL),
    (NULL, 'Cathy Pineda', 'cathy.pineda@student.local', 'pending', 1, 2, 2, '2026-01-31 09:30:00', '2026-02-01 10:15:00', '2026-02-01 10:15:00'),
    ('S-2026-0018', 'Leo Santos', 'leo.santos@student.local', 'admitted', 0, 2, 2, '2026-01-31 11:05:00', '2026-02-02 08:35:00', NULL),
    (NULL, 'Bianca Torres', 'bianca.torres@student.local', 'pending', 0, 2, NULL, '2026-02-01 09:15:00', NULL, NULL),
    (NULL, 'Julius Manalo', 'julius.manalo@student.local', 'rejected', 0, 2, 2, '2026-02-01 11:20:00', '2026-02-02 08:10:00', NULL),
    ('S-2026-0021', 'Hazel Dizon', 'hazel.dizon@student.local', 'admitted', 0, 2, 2, '2026-02-01 13:05:00', '2026-02-02 08:55:00', NULL),
    (NULL, 'Mia Velasco', 'mia.velasco@student.local', 'pending', 0, 2, NULL, '2026-02-01 15:30:00', NULL, NULL),
    ('S-2026-0023', 'Carl De Vera', 'carl.devera@student.local', 'waitlisted', 0, 2, NULL, '2026-02-02 09:20:00', NULL, NULL),
    ('S-2026-0024', 'Shane Bautista', 'shane.bautista@student.local', 'admitted', 0, 2, 2, '2026-02-02 10:10:00', '2026-02-02 13:45:00', NULL);

INSERT INTO student_exam_scores (student_id, exam_part_id, score, encoded_by, updated_by)
VALUES
    -- Admitted students (complete scores)
    (2, 1, 92.50, 2, 2),
    (2, 2, 88.00, 2, 2),
    (2, 3, 90.25, 2, 2),
    (2, 4, 84.00, 2, 2),
    (2, 5, 86.75, 2, 2),
    (6, 1, 85.00, 2, 2),
    (6, 2, 81.50, 2, 2),
    (6, 3, 78.00, 2, 2),
    (6, 4, 75.00, 2, 2),
    (6, 5, 83.00, 2, 2),
    (7, 1, 88.00, 2, 2),
    (7, 2, 86.00, 2, 2),
    (7, 3, 79.00, 2, 2),
    (7, 4, 73.50, 2, 2),
    (7, 5, 85.00, 2, 2),
    (12, 1, 90.00, 2, 2),
    (12, 2, 88.00, 2, 2),
    (12, 3, 92.00, 2, 2),
    (12, 4, 89.00, 2, 2),
    (12, 5, 91.00, 2, 2),
    (15, 1, 82.00, 2, 2),
    (15, 2, 80.00, 2, 2),
    (15, 3, 76.00, 2, 2),
    (15, 4, 78.25, 2, 2),
    (15, 5, 79.00, 2, 2),
    (18, 1, 74.00, 2, 2),
    (18, 2, 73.00, 2, 2),
    (18, 3, 71.00, 2, 2),
    (18, 4, 69.50, 2, 2),
    (18, 5, 72.00, 2, 2),
    (21, 1, 91.00, 2, 2),
    (21, 2, 89.00, 2, 2),
    (21, 3, 88.50, 2, 2),
    (21, 4, 87.00, 2, 2),
    (21, 5, 90.00, 2, 2),
    (24, 1, 65.00, 2, 2),
    (24, 2, 63.00, 2, 2),
    (24, 3, 68.00, 2, 2),
    (24, 4, 66.00, 2, 2),
    (24, 5, 64.00, 2, 2),
    -- Pending/waitlisted students (partial or mixed scores)
    (1, 1, 70.00, 2, 2),
    (1, 2, 68.50, 2, 2),
    (1, 3, 0.00, 2, 2),
    (3, 1, 78.00, 2, 2),
    (3, 2, 82.00, 2, 2),
    (9, 1, 83.00, 2, 2),
    (9, 2, 79.00, 2, 2),
    (9, 3, 82.00, 2, 2),
    (9, 4, 77.00, 2, 2),
    (9, 5, 80.00, 2, 2),
    (14, 1, 100.00, 2, 2),
    (14, 2, 71.00, 2, 2),
    (14, 3, 69.00, 2, 2),
    (14, 4, 74.00, 2, 2),
    (14, 5, 70.00, 2, 2);

INSERT INTO password_resets (user_id, token_hash, expires_at, used_at, created_at)
VALUES
    (3, 'd3b07384d113edec49eaa6238ad5ff00d3b07384d113edec49eaa6238ad5ff00', '2026-02-06 12:00:00', NULL, '2026-02-06 08:30:00'),
    (5, '5f4dcc3b5aa765d61d8327deb882cf995f4dcc3b5aa765d61d8327deb882cf99', '2026-02-05 17:00:00', '2026-02-05 15:45:00', '2026-02-05 14:10:00');

INSERT INTO email_verifications (user_id, code_hash, expires_at, used_at, created_at)
VALUES
    (3, '12dea96fec20593566ab75692c99495912dea96fec20593566ab75692c994959', '2026-02-06 18:00:00', NULL, '2026-02-06 09:00:00');

INSERT INTO logs (user_id, action, entity, entity_id, details, created_at)
VALUES
    (2, 'USER_CREATED', 'users', 4, 'Created admission account', '2026-02-01 09:10:00'),
    (2, 'USER_REJECTED', 'users', 3, 'Rejected admission account', '2026-02-02 08:20:00'),
    (2, 'STUDENT_CREATED', 'students', 2, 'Admitted student created', '2026-02-02 09:30:00'),
    (2, 'SCORES_UPDATED', 'student_exam_scores', 2, 'Updated math and science scores', '2026-02-02 09:45:00'),
    (2, 'WEIGHTS_UPDATED', 'weights', 1, 'Adjusted weights for BSCS', '2026-02-03 11:05:00'),
    (5, 'LOGIN_LOCKED', 'users', 5, 'Account locked after failed attempts', '2026-02-03 09:30:00');

SET FOREIGN_KEY_CHECKS = 1;
