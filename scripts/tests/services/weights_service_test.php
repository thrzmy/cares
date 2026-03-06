<?php
declare(strict_types=1);

// WeightsService smoke tests.
// Run: php scripts/tests/services/weights_service_test.php

require_once __DIR__ . '/../_bootstrap.php';

$pdo = getPdo();

$parts = WeightsService::getExamParts();
$courses = WeightsService::getCourses();
$weightsMap = WeightsService::getWeightsMap();
$courseCount = WeightsService::getCoursesCount();
$pagedCourses = WeightsService::getCoursesPage(5, 0);

expect(count($parts) > 0, 'WeightsService returns exam parts');
expect(count($courses) > 0, 'WeightsService returns courses');
expect($courseCount === count($courses), 'WeightsService count matches course list');
expect(count($pagedCourses) > 0 && count($pagedCourses) <= 5, 'WeightsService paginates courses');
expect(!empty($weightsMap), 'WeightsService returns weight map');

$courseId = (int)$courses[0]['id'];
$partId = (int)$parts[0]['id'];
$beforeWeight = (float)($weightsMap[$courseId][$partId] ?? 0.0);
$newWeight = $beforeWeight >= 99.0 ? 80.0 : 99.0;

try {
    WeightsService::saveMatrix([
        $courseId => [
            $partId => (string)$newWeight,
        ],
    ], 1);

    $weightRow = $pdo->prepare("SELECT weight FROM weights WHERE course_id = :course_id AND exam_part_id = :exam_part_id AND is_deleted = 0 LIMIT 1");
    $weightRow->execute([':course_id' => $courseId, ':exam_part_id' => $partId]);
    $afterWeight = (float)$weightRow->fetchColumn();
    expect(abs($afterWeight - $newWeight) < 0.0001, 'WeightsService saves matrix value');

    expectThrows(
        static function () use ($courseId, $partId): void {
            WeightsService::saveMatrix([
                $courseId => [
                    $partId => '101',
                ],
            ], 1);
        },
        'WeightsService rejects out-of-range matrix values'
    );
} finally {
    $restoreSt = $pdo->prepare("UPDATE weights
                                SET weight = :weight, updated_at = CURRENT_TIMESTAMP
                                WHERE course_id = :course_id AND exam_part_id = :exam_part_id");
    $restoreSt->execute([
        ':weight' => $beforeWeight,
        ':course_id' => $courseId,
        ':exam_part_id' => $partId,
    ]);
}

echo "WeightsService tests passed.\n";
