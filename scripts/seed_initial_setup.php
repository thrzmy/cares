<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Env.php';
Env::load(__DIR__ . '/../.env');
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/Database.php';

function readJsonFile(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("Missing file: {$path}");
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Unable to read file: {$path}");
    }

    // Tolerate UTF-8 BOM (common when files are saved from Windows editors/PowerShell).
    if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) {
        $raw = substr($raw, 3);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON in {$path} (" . json_last_error_msg() . ')');
    }

    return $data;
}

function loadSetupPayload(): array
{
    $payload = readJsonFile(__DIR__ . '/../database/setup.json');
    if (!isset($payload['courses'], $payload['exam_parts'], $payload['weights'])) {
        throw new RuntimeException('database/setup.json must contain courses, exam_parts, and weights');
    }

    return [
        'courses' => is_array($payload['courses']) ? $payload['courses'] : [],
        'exam_parts' => is_array($payload['exam_parts']) ? $payload['exam_parts'] : [],
        'weights' => is_array($payload['weights']) ? $payload['weights'] : [],
    ];
}

$data = loadSetupPayload();
$courses = $data['courses'];
$examParts = $data['exam_parts'];
$weights = $data['weights'];

$fresh = in_array('--fresh', $argv ?? [], true);
$pdo = Database::pdo();

try {
    if ($fresh) {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE weights');
        $pdo->exec('TRUNCATE TABLE exam_parts');
        $pdo->exec('TRUNCATE TABLE courses');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    $pdo->beginTransaction();

    $courseStmt = $pdo->prepare(
        'INSERT INTO courses (course_code, course_name, is_deleted) VALUES (:course_code, :course_name, 0)
         ON DUPLICATE KEY UPDATE course_name = VALUES(course_name), is_deleted = 0, deleted_at = NULL'
    );

    foreach ($courses as $row) {
        if (!isset($row['course_code'], $row['course_name'])) {
            throw new RuntimeException('Course entries must contain course_code and course_name');
        }

        $courseStmt->execute([
            ':course_code' => (string)$row['course_code'],
            ':course_name' => (string)$row['course_name'],
        ]);
    }

    $partStmt = $pdo->prepare(
        'INSERT INTO exam_parts (name, max_score, is_deleted) VALUES (:name, :max_score, 0)
         ON DUPLICATE KEY UPDATE max_score = VALUES(max_score), is_deleted = 0, deleted_at = NULL'
    );

    foreach ($examParts as $row) {
        if (!isset($row['name'], $row['max_score'])) {
            throw new RuntimeException('Exam part entries must contain name and max_score');
        }

        $partStmt->execute([
            ':name' => (string)$row['name'],
            ':max_score' => (float)$row['max_score'],
        ]);
    }

    $courseMap = [];
    foreach ($pdo->query('SELECT id, course_code FROM courses WHERE is_deleted = 0') as $row) {
        $courseMap[(string)$row['course_code']] = (int)$row['id'];
    }

    $partMap = [];
    foreach ($pdo->query('SELECT id, name FROM exam_parts WHERE is_deleted = 0') as $row) {
        $partMap[(string)$row['name']] = (int)$row['id'];
    }

    $weightStmt = $pdo->prepare(
        'INSERT INTO weights (course_id, exam_part_id, weight, is_deleted, created_by, updated_by)
         VALUES (:course_id, :exam_part_id, :weight, 0, 1, 1)
         ON DUPLICATE KEY UPDATE weight = VALUES(weight), is_deleted = 0, deleted_at = NULL, deleted_by = NULL, updated_by = 1'
    );

    $weightCount = 0;
    foreach ($weights as $row) {
        $courseCode = (string)($row['course_code'] ?? '');
        $weightMap = $row['weights'] ?? null;

        if ($courseCode === '' || !is_array($weightMap)) {
            throw new RuntimeException('Weight entries must contain course_code and weights object');
        }

        if (!isset($courseMap[$courseCode])) {
            throw new RuntimeException("Unknown course_code in setup data: {$courseCode}");
        }

        foreach ($weightMap as $partName => $weight) {
            if (!isset($partMap[$partName])) {
                throw new RuntimeException("Unknown exam part name in setup data: {$partName}");
            }

            $weightStmt->execute([
                ':course_id' => $courseMap[$courseCode],
                ':exam_part_id' => $partMap[$partName],
                ':weight' => (float)$weight,
            ]);
            $weightCount++;
        }
    }

    $pdo->commit();

    echo "Setup seed complete.\n";
    echo '- Courses: ' . count($courses) . "\n";
    echo '- Exam parts: ' . count($examParts) . "\n";
    echo '- Weight rows processed: ' . $weightCount . "\n";
    echo $fresh ? "Mode: fresh\n" : "Mode: upsert\n";
    echo "Source: database/setup.json\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
