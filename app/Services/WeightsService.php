<?php
declare(strict_types=1);

final class WeightsService
{
    public static function getExamParts(): array
    {
        $sql = "SELECT id, name, max_score
                FROM exam_parts
                WHERE is_deleted = 0
                ORDER BY id ASC";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function getCourses(): array
    {
        $sql = "SELECT id, course_code, course_name
                FROM courses
                WHERE is_deleted = 0
                ORDER BY course_code ASC";
        return Database::pdo()->query($sql)->fetchAll();
    }

    /**
     * Returns weights mapped as [course_id][exam_part_id] => weight
     */
    public static function getWeightsMap(): array
    {
        $sql = "SELECT course_id, exam_part_id, weight
                FROM weights
                WHERE is_deleted = 0";
        $rows = Database::pdo()->query($sql)->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['course_id']][(int)$r['exam_part_id']] = (float)$r['weight'];
        }
        return $map;
    }

    /**
     * Upsert matrix weights.
     * $matrix format: [courseId][examPartId] => weightString
     */
    public static function saveMatrix(array $matrix, int $userId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $sql = "INSERT INTO weights (course_id, exam_part_id, weight, created_by, updated_by)
                    VALUES (:course_id, :exam_part_id, :weight, :created_by, :updated_by)
                    ON DUPLICATE KEY UPDATE
                    weight = VALUES(weight),
                    updated_by = :updated_by2,
                    updated_at = CURRENT_TIMESTAMP,
                    is_deleted = 0,
                    deleted_at = NULL,
                    deleted_by = NULL";


            $stmt = $pdo->prepare($sql);

            foreach ($matrix as $courseId => $parts) {
                foreach ($parts as $examPartId => $weightRaw) {
                    $weightRaw = trim((string)$weightRaw);

                    // Allow blank to mean "skip" (don't overwrite)
                    if ($weightRaw === '') {
                        continue;
                    }

                    // Validate numeric
                    if (!is_numeric($weightRaw)) {
                        throw new RuntimeException("Invalid weight for course {$courseId}, part {$examPartId}");
                    }

                    $weight = (float)$weightRaw;

                    // Simple bounds (capstone-safe). Adjust later if needed.
                    if ($weight < 0 || $weight > 100) {
                        throw new RuntimeException("Weight out of range (0-100) for course {$courseId}, part {$examPartId}");
                    }

                    $stmt->execute([
                        ':course_id'   => (int)$courseId,
                        ':exam_part_id'=> (int)$examPartId,
                        ':weight'      => $weight,
                        ':created_by'  => $userId,
                        ':updated_by'  => $userId,
                        ':updated_by2' => $userId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
