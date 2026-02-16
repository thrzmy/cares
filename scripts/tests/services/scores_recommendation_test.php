<?php
declare(strict_types=1);

// ScoresService + recommendation smoke tests.
// Run: php scripts/tests/services/scores_recommendation_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

$studentEmail = 'module_scores@student.local';
$userEmail = 'module_scores_user@test.local';

$pdo->exec("DELETE FROM users WHERE email = '{$userEmail}'");
$pdo->exec("DELETE FROM students WHERE email = '{$studentEmail}'");

$userHash = password_hash('ModulePass@123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, role, account_status, email_verified_at, is_active, force_password_change)
               VALUES ('Module Scores User', :email, :password, 'admission', 'verified', NOW(), 1, 0)")
    ->execute([
        ':email' => $userEmail,
        ':password' => $userHash,
    ]);
$userId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO students (id_number, name, email, status, created_by)
               VALUES (NULL, 'Module Scores Student', :email, 'pending', :created_by)")
    ->execute([
        ':email' => $studentEmail,
        ':created_by' => $userId,
    ]);
$studentId = (int)$pdo->lastInsertId();

try {
    expect(!ScoresService::hasScores($studentId), 'ScoresService reports no scores before encoding');

    $parts = WeightsService::getExamParts();
    expect(count($parts) > 0, 'ScoresService dependencies include exam parts');

    $validScores = [];
    foreach ($parts as $part) {
        $pid = (int)$part['id'];
        $max = (float)$part['max_score'];
        $validScores[$pid] = (string)round($max * 0.6, 2);
    }

    ScoresService::saveStudentScores($studentId, $validScores, $userId);
    expect(ScoresService::hasScores($studentId), 'ScoresService detects saved scores');

    $scoresMap = ScoresService::getStudentScoresMap($studentId);
    expect(count($scoresMap) === count($parts), 'ScoresService stores score per exam part');

    $firstPartId = (int)$parts[0]['id'];
    $firstPartMax = (float)$parts[0]['max_score'];
    $validScores[$firstPartId] = (string)$firstPartMax;
    ScoresService::saveStudentScores($studentId, $validScores, $userId);

    $scoresMap = ScoresService::getStudentScoresMap($studentId);
    expect(abs((float)$scoresMap[$firstPartId] - $firstPartMax) < 0.0001, 'ScoresService updates existing score entries');

    $invalidScores = $validScores;
    $invalidScores[$firstPartId] = (string)($firstPartMax + 1);
    expectThrows(
        static function () use ($studentId, $invalidScores, $userId): void {
            ScoresService::saveStudentScores($studentId, $invalidScores, $userId);
        },
        'ScoresService rejects scores above max score'
    );

    $recommendationSql = "WITH ranked AS (
                            SELECT
                                c.course_code,
                                SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) AS total_score,
                                ROW_NUMBER() OVER (
                                    ORDER BY SUM((ses.score / NULLIF(ep.max_score, 0)) * w.weight) DESC
                                ) AS rn
                            FROM student_exam_scores ses
                            INNER JOIN exam_parts ep
                                ON ep.id = ses.exam_part_id AND ep.is_deleted = 0
                            INNER JOIN weights w
                                ON w.exam_part_id = ses.exam_part_id AND w.is_deleted = 0
                            INNER JOIN courses c
                                ON c.id = w.course_id AND c.is_deleted = 0
                            WHERE ses.is_deleted = 0
                              AND ses.student_id = :student_id
                            GROUP BY c.id
                          )
                          SELECT course_code, total_score, rn
                          FROM ranked
                          WHERE rn <= 3
                          ORDER BY rn";
    $recSt = $pdo->prepare($recommendationSql);
    $recSt->execute([':student_id' => $studentId]);
    $recommendations = $recSt->fetchAll(PDO::FETCH_ASSOC);
    expect(count($recommendations) > 0 && count($recommendations) <= 3, 'Recommendation query returns top courses');
} finally {
    $pdo->prepare("DELETE FROM student_exam_scores WHERE student_id = :student_id")
        ->execute([':student_id' => $studentId]);
    $pdo->prepare("DELETE FROM students WHERE id = :id")->execute([':id' => $studentId]);
    $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $userId]);
}

echo "Scores/recommendation tests passed.\n";
