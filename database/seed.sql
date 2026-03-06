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
    ('BSED-ENG', 'B.S. Secondary Education - Major in English'),
    ('BSED-FIL', 'B.S. Secondary Education - Major in Filipino'),
    ('BSED-MATH', 'B.S. Secondary Education - Major in Mathematics'),
    ('BSED-SS', 'B.S. Secondary Education - Major in Social Studies'),
    ('BSED-SCI', 'B.S. Secondary Education - Major in Science'),
    ('BSHRDM', 'B.S. in Human Resources Devt & Management'),
    ('BSMM', 'B.S. in Marketing Management'),
    ('BSOA', 'B.S. in Office Administration'),
    ('BSTM', 'B.S. Tourism Management'),
    ('BSHM', 'Hospitality Management'),
    ('BSIT', 'B.S. Information and Technology'),
    ('BSCS', 'B.S. Computer Science'),
    ('ABPSY', 'AB Psychology');

INSERT INTO exam_parts (name, max_score)
VALUES
    ('English', 30),
    ('Filipino', 30),
    ('Literature', 30),
    ('Math', 30),
    ('Science', 30),
    ('Studies', 30),
    ('Humanities', 30);

INSERT INTO weights (course_id, exam_part_id, weight, created_by, updated_by)
VALUES
    -- B.S. Secondary Education - Major in English
    (1, 1, 40.00, 2, 2),
    (1, 2, 10.00, 2, 2),
    (1, 3, 20.00, 2, 2),
    (1, 4, 5.00, 2, 2),
    (1, 5, 5.00, 2, 2),
    (1, 6, 10.00, 2, 2),
    (1, 7, 10.00, 2, 2),
    -- B.S. Secondary Education - Major in Filipino
    (2, 1, 10.00, 2, 2),
    (2, 2, 40.00, 2, 2),
    (2, 3, 20.00, 2, 2),
    (2, 4, 5.00, 2, 2),
    (2, 5, 5.00, 2, 2),
    (2, 6, 10.00, 2, 2),
    (2, 7, 10.00, 2, 2),
    -- B.S. Secondary Education - Major in Mathematics
    (3, 1, 20.00, 2, 2),
    (3, 2, 10.00, 2, 2),
    (3, 3, 10.00, 2, 2),
    (3, 4, 40.00, 2, 2),
    (3, 5, 10.00, 2, 2),
    (3, 6, 5.00, 2, 2),
    (3, 7, 5.00, 2, 2),
    -- B.S. Secondary Education - Major in Social Studies
    (4, 1, 10.00, 2, 2),
    (4, 2, 10.00, 2, 2),
    (4, 3, 10.00, 2, 2),
    (4, 4, 5.00, 2, 2),
    (4, 5, 5.00, 2, 2),
    (4, 6, 40.00, 2, 2),
    (4, 7, 20.00, 2, 2),
    -- B.S. Secondary Education - Major in Science
    (5, 1, 10.00, 2, 2),
    (5, 2, 10.00, 2, 2),
    (5, 3, 5.00, 2, 2),
    (5, 4, 20.00, 2, 2),
    (5, 5, 40.00, 2, 2),
    (5, 6, 5.00, 2, 2),
    (5, 7, 10.00, 2, 2),
    -- B.S. in Human Resources Devt & Management
    (6, 1, 30.00, 2, 2),
    (6, 2, 10.00, 2, 2),
    (6, 3, 10.00, 2, 2),
    (6, 4, 20.00, 2, 2),
    (6, 5, 10.00, 2, 2),
    (6, 6, 10.00, 2, 2),
    (6, 7, 10.00, 2, 2),
    -- B.S. in Marketing Management
    (7, 1, 30.00, 2, 2),
    (7, 2, 10.00, 2, 2),
    (7, 3, 10.00, 2, 2),
    (7, 4, 20.00, 2, 2),
    (7, 5, 10.00, 2, 2),
    (7, 6, 10.00, 2, 2),
    (7, 7, 10.00, 2, 2),
    -- B.S. in Office Administration
    (8, 1, 25.00, 2, 2),
    (8, 2, 10.00, 2, 2),
    (8, 3, 10.00, 2, 2),
    (8, 4, 25.00, 2, 2),
    (8, 5, 10.00, 2, 2),
    (8, 6, 10.00, 2, 2),
    (8, 7, 10.00, 2, 2),
    -- B.S. Tourism Management
    (9, 1, 30.00, 2, 2),
    (9, 2, 15.00, 2, 2),
    (9, 3, 10.00, 2, 2),
    (9, 4, 10.00, 2, 2),
    (9, 5, 10.00, 2, 2),
    (9, 6, 15.00, 2, 2),
    (9, 7, 10.00, 2, 2),
    -- Hospitality Management
    (10, 1, 30.00, 2, 2),
    (10, 2, 10.00, 2, 2),
    (10, 3, 10.00, 2, 2),
    (10, 4, 15.00, 2, 2),
    (10, 5, 10.00, 2, 2),
    (10, 6, 10.00, 2, 2),
    (10, 7, 15.00, 2, 2),
    -- B.S. Information and Technology
    (11, 1, 15.00, 2, 2),
    (11, 2, 5.00, 2, 2),
    (11, 3, 5.00, 2, 2),
    (11, 4, 40.00, 2, 2),
    (11, 5, 20.00, 2, 2),
    (11, 6, 10.00, 2, 2),
    (11, 7, 5.00, 2, 2),
    -- B.S. Computer Science
    (12, 1, 15.00, 2, 2),
    (12, 2, 5.00, 2, 2),
    (12, 3, 5.00, 2, 2),
    (12, 4, 40.00, 2, 2),
    (12, 5, 20.00, 2, 2),
    (12, 6, 10.00, 2, 2),
    (12, 7, 5.00, 2, 2),
    -- AB Psychology
    (13, 1, 20.00, 2, 2),
    (13, 2, 10.00, 2, 2),
    (13, 3, 10.00, 2, 2),
    (13, 4, 20.00, 2, 2),
    (13, 5, 20.00, 2, 2),
    (13, 6, 10.00, 2, 2),
    (13, 7, 10.00, 2, 2);

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
    (2, 1, 28, 2, 2),
    (2, 2, 27, 2, 2),
    (2, 3, 27, 2, 2),
    (2, 4, 25, 2, 2),
    (2, 5, 26, 2, 2),
    (2, 6, 25, 2, 2),
    (2, 7, 26, 2, 2),
    (6, 1, 26, 2, 2),
    (6, 2, 24, 2, 2),
    (6, 3, 23, 2, 2),
    (6, 4, 23, 2, 2),
    (6, 5, 25, 2, 2),
    (6, 6, 22, 2, 2),
    (6, 7, 24, 2, 2),
    (7, 1, 26, 2, 2),
    (7, 2, 26, 2, 2),
    (7, 3, 24, 2, 2),
    (7, 4, 22, 2, 2),
    (7, 5, 24, 2, 2),
    (7, 6, 22, 2, 2),
    (7, 7, 23, 2, 2),
    (12, 1, 28, 2, 2),
    (12, 2, 27, 2, 2),
    (12, 3, 28, 2, 2),
    (12, 4, 27, 2, 2),
    (12, 5, 28, 2, 2),
    (12, 6, 26, 2, 2),
    (12, 7, 26, 2, 2),
    (15, 1, 24, 2, 2),
    (15, 2, 24, 2, 2),
    (15, 3, 22, 2, 2),
    (15, 4, 23, 2, 2),
    (15, 5, 24, 2, 2),
    (15, 6, 23, 2, 2),
    (15, 7, 21, 2, 2),
    (18, 1, 22, 2, 2),
    (18, 2, 21, 2, 2),
    (18, 3, 21, 2, 2),
    (18, 4, 20, 2, 2),
    (18, 5, 21, 2, 2),
    (18, 6, 20, 2, 2),
    (18, 7, 20, 2, 2),
    (21, 1, 29, 2, 2),
    (21, 2, 28, 2, 2),
    (21, 3, 27, 2, 2),
    (21, 4, 27, 2, 2),
    (21, 5, 28, 2, 2),
    (21, 6, 26, 2, 2),
    (21, 7, 27, 2, 2),
    (24, 1, 20, 2, 2),
    (24, 2, 19, 2, 2),
    (24, 3, 20, 2, 2),
    (24, 4, 20, 2, 2),
    (24, 5, 19, 2, 2),
    (24, 6, 19, 2, 2),
    (24, 7, 18, 2, 2),
    -- Pending/waitlisted students (partial or mixed scores)
    (1, 1, 21, 2, 2),
    (1, 2, 21, 2, 2),
    (1, 3, 0, 2, 2),
    (3, 1, 24, 2, 2),
    (3, 2, 25, 2, 2),
    (3, 4, 22, 2, 2),
    (9, 1, 25, 2, 2),
    (9, 2, 24, 2, 2),
    (9, 3, 24, 2, 2),
    (9, 4, 23, 2, 2),
    (9, 5, 23, 2, 2),
    (9, 6, 22, 2, 2),
    (9, 7, 21, 2, 2),
    (14, 1, 30, 2, 2),
    (14, 2, 21, 2, 2),
    (14, 3, 21, 2, 2),
    (14, 4, 22, 2, 2),
    (14, 5, 21, 2, 2),
    (14, 6, 20, 2, 2),
    (14, 7, 20, 2, 2);

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
