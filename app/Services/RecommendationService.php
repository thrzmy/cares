<?php

declare(strict_types=1);

final class RecommendationService
{
    /**
     * Re-evaluate a single student and generate recommendations based on weights matrix.
     * Higher score = better match.
     */
    public static function evaluateStudent(int $studentId): void
    {
        $pdo = Database::pdo();

        // Ensure student exists
        $st = $pdo->prepare("SELECT id FROM students WHERE id = :id AND is_deleted = 0 LIMIT 1");
        $st->execute([':id' => $studentId]);
        if (!$st->fetch()) {
            return;
        }

        // The recommendations are dynamically generated when viewed (using getCourseRecommendationsForStudent
        // in AdminController/AdmissionController), so we don't need to save them back to the database. We just log.
        Logger::log(currentUserId(), 'EVALUATE_STUDENT', 'students', $studentId, 'System generated recommendations for student');
    }
}
