<?php

declare(strict_types=1);

final class GuidanceController
{
    public static function dashboard(): void
    {
        RoleMiddleware::requireRole('guidance');
        View::render('guidance/dashboard', ['title' => 'Guidance Dashboard']);
    }

    public static function weights(): void
    {
        RoleMiddleware::requireRole('guidance');

        $courses = WeightsService::getCourses();
        $parts = WeightsService::getExamParts();
        $weightsMap = WeightsService::getWeightsMap();

        View::render('guidance/weights', [
            'title' => 'Weights Matrix',
            'courses' => $courses,
            'parts' => $parts,
            'weightsMap' => $weightsMap,
            'success' => null,
            'error' => null,
        ]);
    }

    public static function saveWeights(): void
    {
        verifyCsrfOrFail();
        RoleMiddleware::requireRole('guidance');

        $userId = currentUserId();
        if ($userId === null) {
            redirect('/login');
        }

        $matrix = $_POST['weights'] ?? [];

        try {
            WeightsService::saveMatrix($matrix, $userId);
            Logger::log($userId, 'UPDATED_WEIGHTS', 'weights', null, 'Updated weights matrix');

            flash('success', 'Weights saved successfully.');
            redirect('/guidance/weights');

        } catch (Throwable $e) {
            // Re-render with error message
            $courses = WeightsService::getCourses();
            $parts = WeightsService::getExamParts();
            $weightsMap = WeightsService::getWeightsMap();

            View::render('guidance/weights', [
                'title' => 'Weights Matrix',
                'courses' => $courses,
                'parts' => $parts,
                'weightsMap' => $weightsMap,
                'success' => null,
                'error' => APP_DEBUG ? $e->getMessage() : 'Failed to save weights.',
            ]);
        }
    }
}
