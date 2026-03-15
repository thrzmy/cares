<?php
declare(strict_types=1);

final class WeightsService
{
    private static ?bool $examPartCategoryColumnExists = null;

    public static function getExamParts(): array
    {
        try {
            $sql = "SELECT id, name, max_score, category_id
                    FROM exam_parts
                    WHERE is_deleted = 0
                    ORDER BY id ASC";
            return Database::pdo()->query($sql)->fetchAll();
        } catch (Throwable) {
            $sql = "SELECT id, name, max_score
                    FROM exam_parts
                    WHERE is_deleted = 0
                    ORDER BY id ASC";
            return Database::pdo()->query($sql)->fetchAll();
        }
    }

    public static function getCourses(): array
    {
        $sql = "SELECT id, course_code, course_name
                FROM courses
                WHERE is_deleted = 0
                ORDER BY course_code ASC";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function getCoursesCount(): int
    {
        $sql = "SELECT COUNT(*)
                FROM courses
                WHERE is_deleted = 0";
        return (int)Database::pdo()->query($sql)->fetchColumn();
    }

    public static function getCoursesPage(int $limit, int $offset): array
    {
        $sql = "SELECT id, course_code, course_name
                FROM courses
                WHERE is_deleted = 0
                ORDER BY course_code ASC
                LIMIT :limit OFFSET :offset";
        $st = Database::pdo()->prepare($sql);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function getAllCourses(): array
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
    public static function saveMatrix(array $matrix, int $userId): bool
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $existingRows = $pdo->query("SELECT course_id, exam_part_id, weight, is_deleted FROM weights")->fetchAll();
            $existingMap = [];
            foreach ($existingRows as $row) {
                $existingMap[(int)$row['course_id']][(int)$row['exam_part_id']] = [
                    'weight' => (float)$row['weight'],
                    'is_deleted' => (int)$row['is_deleted'],
                ];
            }

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
            $hasChanges = false;

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

                    $courseKey = (int)$courseId;
                    $partKey = (int)$examPartId;
                    $existing = $existingMap[$courseKey][$partKey] ?? null;
                    if ($existing !== null && (int)$existing['is_deleted'] === 0 && abs(((float)$existing['weight']) - $weight) < 0.00001) {
                        continue;
                    }

                    $stmt->execute([
                        ':course_id'   => $courseKey,
                        ':exam_part_id'=> $partKey,
                        ':weight'      => $weight,
                        ':created_by'  => $userId,
                        ':updated_by'  => $userId,
                        ':updated_by2' => $userId,
                    ]);
                    $hasChanges = true;
                }
            }

            $pdo->commit();
            return $hasChanges;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Add a new course.
     */
    public static function addCourse(string $code, string $name): int
    {
        $pdo = Database::pdo();

        // Check for soft-deleted duplicate
        $check = $pdo->prepare("SELECT id, is_deleted FROM courses WHERE course_code = :code LIMIT 1");
        $check->execute([':code' => $code]);
        $existing = $check->fetch();

        if ($existing) {
            if ((int)$existing['is_deleted'] === 1) {
                // Restore
                $pdo->prepare("UPDATE courses SET course_name = :name, is_deleted = 0, deleted_at = NULL WHERE id = :id")
                    ->execute([':name' => $name, ':id' => (int)$existing['id']]);
                return (int)$existing['id'];
            }
            throw new RuntimeException("Course code '{$code}' already exists.");
        }

        $pdo->prepare("INSERT INTO courses (course_code, course_name) VALUES (:code, :name)")
            ->execute([':code' => $code, ':name' => $name]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Soft-delete a course.
     */
    public static function deleteCourse(int $courseId, int $userId): void
    {
        Database::pdo()->prepare(
            "UPDATE courses SET is_deleted = 1, deleted_at = NOW() WHERE id = :id AND is_deleted = 0"
        )->execute([':id' => $courseId]);

        // Also soft-delete its weights
        Database::pdo()->prepare(
            "UPDATE weights SET is_deleted = 1, deleted_at = NOW(), deleted_by = :uid WHERE course_id = :cid AND is_deleted = 0"
        )->execute([':uid' => $userId, ':cid' => $courseId]);
    }

    /**
     * Update max scores for exam parts.
     */
    public static function updateMaxScores(array $maxScores): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE exam_parts SET max_score = :max WHERE id = :id AND is_deleted = 0");

        foreach ($maxScores as $partId => $maxRaw) {
            $maxRaw = trim((string)$maxRaw);
            if ($maxRaw === '' || !is_numeric($maxRaw)) continue;
            $max = (float)$maxRaw;
            if ($max < 0) continue;
            $stmt->execute([':max' => $max, ':id' => (int)$partId]);
        }
    }

    /**
     * Get exam part categories for grouping.
     */
    public static function getExamPartCategories(): array
    {
        $sql = "SELECT id, name, sort_order FROM exam_part_categories ORDER BY sort_order ASC";
        try {
            return Database::pdo()->query($sql)->fetchAll();
        } catch (Throwable) {
            // Table may not exist yet (before migration)
            return [];
        }
    }

    /**
     * Get exam parts grouped by category.
     */
    public static function getExamPartsGrouped(): array
    {
        $parts = self::getExamParts();
        $categories = self::getExamPartCategories();

        if (empty($categories)) {
            // No categories yet — return all under one group
            return [['category_name' => 'Exam Parts', 'parts' => $parts]];
        }

        $catMap = [];
        foreach ($categories as $cat) {
            $catMap[(int)$cat['id']] = ['category_name' => $cat['name'], 'parts' => []];
        }
        $catMap[0] = ['category_name' => 'Uncategorized', 'parts' => []];

        foreach ($parts as $part) {
            $catId = (int)($part['category_id'] ?? 0);
            if (!isset($catMap[$catId])) $catId = 0;
            $catMap[$catId]['parts'][] = $part;
        }

        // Remove empty groups
        return array_values(array_filter($catMap, fn($g) => !empty($g['parts'])));
    }

    /**
     * Add a new exam part.
     */
    public static function addExamPart(string $name, float $maxScore, ?int $categoryId): int
    {
        $pdo = Database::pdo();
        if (self::hasExamPartCategoryColumn()) {
            $pdo->prepare("INSERT INTO exam_parts (name, max_score, category_id) VALUES (:name, :max, :cat)")
                ->execute([':name' => $name, ':max' => $maxScore, ':cat' => $categoryId]);
        } else {
            $pdo->prepare("INSERT INTO exam_parts (name, max_score) VALUES (:name, :max)")
                ->execute([':name' => $name, ':max' => $maxScore]);
        }
        return (int)$pdo->lastInsertId();
    }

    private static function hasExamPartCategoryColumn(): bool
    {
        if (self::$examPartCategoryColumnExists !== null) {
            return self::$examPartCategoryColumnExists;
        }

        try {
            $st = Database::pdo()->query("SHOW COLUMNS FROM exam_parts LIKE 'category_id'");
            self::$examPartCategoryColumnExists = (bool)$st->fetch();
        } catch (Throwable) {
            self::$examPartCategoryColumnExists = false;
        }

        return self::$examPartCategoryColumnExists;
    }
}
