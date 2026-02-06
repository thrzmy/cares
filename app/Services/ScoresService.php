<?php
declare(strict_types=1);

final class ScoresService
{
    public static function hasScores(int $studentId): bool
    {
        $sql = "SELECT 1
                FROM student_exam_scores
                WHERE student_id = :student_id AND is_deleted = 0
                LIMIT 1";
        $st = Database::pdo()->prepare($sql);
        $st->execute([':student_id' => $studentId]);
        return (bool)$st->fetchColumn();
    }

    public static function getStudentScoresMap(int $studentId): array
    {
        $sql = "SELECT exam_part_id, score
                FROM student_exam_scores
                WHERE student_id = :student_id AND is_deleted = 0";
        $st = Database::pdo()->prepare($sql);
        $st->execute([':student_id' => $studentId]);
        $rows = $st->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['exam_part_id']] = (float)$row['score'];
        }
        return $map;
    }

    /**
     * Save scores for a student. $scores format: [examPartId] => scoreString
     */
    public static function saveStudentScores(int $studentId, array $scores, int $userId): void
    {
        $parts = WeightsService::getExamParts();
        if (empty($parts)) {
            throw new RuntimeException('No exam parts configured. Please contact the administrator.');
        }

        $partsById = [];
        $partsName = [];
        foreach ($parts as $part) {
            $partId = (int)$part['id'];
            $partsById[$partId] = (float)$part['max_score'];
            $partsName[$partId] = (string)$part['name'];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $sql = "INSERT INTO student_exam_scores (student_id, exam_part_id, score, encoded_by, updated_by)
                    VALUES (:student_id, :exam_part_id, :score, :encoded_by, :updated_by)
                    ON DUPLICATE KEY UPDATE
                    score = VALUES(score),
                    updated_by = :updated_by2,
                    updated_at = CURRENT_TIMESTAMP,
                    is_deleted = 0,
                    deleted_at = NULL";
            $stmt = $pdo->prepare($sql);

            foreach ($partsById as $partId => $maxScore) {
                $raw = trim((string)($scores[$partId] ?? ''));
                if ($raw === '' || !is_numeric($raw)) {
                    $label = $partsName[$partId] ?? ('Exam Part ' . $partId);
                    throw new RuntimeException("Please enter a valid score for {$label}.");
                }

                $score = (float)$raw;
                if ($score < 0 || $score > $maxScore) {
                    $label = $partsName[$partId] ?? ('Exam Part ' . $partId);
                    throw new RuntimeException("Score out of range for {$label} (0-{$maxScore}).");
                }

                $stmt->execute([
                    ':student_id' => $studentId,
                    ':exam_part_id' => $partId,
                    ':score' => $score,
                    ':encoded_by' => $userId,
                    ':updated_by' => $userId,
                    ':updated_by2' => $userId,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
