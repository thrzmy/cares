<?php
declare(strict_types=1);

// Reports module smoke tests.
// Run: php scripts/tests/modules/reports_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

$studentsTotal = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE is_deleted = 0")->fetchColumn();
$scoreEntries = (int)$pdo->query("SELECT COUNT(*) FROM student_exam_scores WHERE is_deleted = 0")->fetchColumn();
$studentsWithScores = (int)$pdo->query("SELECT COUNT(DISTINCT student_id) FROM student_exam_scores WHERE is_deleted = 0")->fetchColumn();
$logsTotal = (int)$pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();

expect($studentsTotal > 0, 'Reports can read students summary');
expect($scoreEntries >= $studentsWithScores, 'Reports keep score-entry invariants');
expect($studentsWithScores <= $studentsTotal, 'Reports keep student-count invariants');
expect($logsTotal > 0, 'Reports can read logs summary');

$topActions = $pdo->query("SELECT action, COUNT(*) AS total FROM logs GROUP BY action ORDER BY total DESC LIMIT 6")
    ->fetchAll(PDO::FETCH_ASSOC);
expect(is_array($topActions), 'Reports can aggregate top actions');

$recommendations = $pdo->query(
    "WITH ranked AS (
        SELECT
            ses.student_id,
            c.course_code,
            SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
            ROW_NUMBER() OVER (
                PARTITION BY ses.student_id
                ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
            ) AS rn
        FROM student_exam_scores ses
        INNER JOIN exam_parts ep ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
        INNER JOIN weights w ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
        INNER JOIN courses c ON c.id = w.course_id AND c.is_deleted = 0
        WHERE ses.is_deleted = 0
        GROUP BY ses.student_id, c.id
    )
    SELECT course_code, COUNT(*) AS student_count
    FROM ranked
    WHERE rn = 1
    GROUP BY course_code
    ORDER BY student_count DESC
    LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);
expect(is_array($recommendations), 'Reports can aggregate recommendation stats');

echo "Reports tests passed.\n";
