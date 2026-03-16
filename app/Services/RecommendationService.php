<?php

declare(strict_types=1);

final class RecommendationService
{
    private static ?bool $cctChoiceColumnExists = null;
    private static ?array $courseExamPartWeightMap = null;

    private const PART1_NAMES = [
        'English',
        'Filipino',
        'Literature',
        'Math',
        'Science',
        'Studies',
        'Humanities',
    ];

    private const PART2A_NAMES = [
        'Teaching Aptitude',
        'Non-Verbal Reasoning / Spatial',
        'Verbal Aptitude',
        'Inter-Personal Aptitude',
        'Environmental Aptitude',
        'Customer Service',
        'Entrepreneurial',
        'Clerical',
        'Coding',
        'Speed & Accuracy',
    ];

    private const PART2B_NAMES = [
        'Realistic',
        'Investigative',
        'Artistic',
        'Social',
        'Enterprising',
        'Conventional',
    ];

    private const PART4_NAMES = [
        'Openness',
        'Conscientiousness',
        'Extraversion',
        'Agreeableness',
        'Neuroticism',
    ];

    public static function getCourseEvaluationsForStudent(int $studentId): array
    {
        $results = self::getCourseEvaluationsForStudents([$studentId]);
        return $results[$studentId] ?? [];
    }

    public static function getPart2BProfileForStudent(int $studentId): array
    {
        return self::calculatePart2BProfile(self::getStudentScoresByName($studentId));
    }

    public static function getExamResultForStudent(int $studentId): string
    {
        return self::determineExamResultFromScoresMap(self::getStudentScoresByName($studentId));
    }

    public static function getExamResultsForStudents(array $studentIds): array
    {
        $results = [];
        foreach (array_values(array_unique(array_map('intval', $studentIds))) as $studentId) {
            if ($studentId > 0) {
                $results[$studentId] = self::getExamResultForStudent($studentId);
            }
        }

        return $results;
    }

    public static function getCourseEvaluationsForStudents(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));
        $studentIds = array_values(array_filter($studentIds, static fn(int $id): bool => $id > 0));
        if (empty($studentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $sql = "SELECT
                    s.id AS student_id,
                    " . (self::hasCctChoiceColumn() ? "s.cct_choice" : "'first'") . " AS cct_choice,
                    s.first_choice,
                    s.second_choice,
                    s.shs_strand,
                    s.gpa,
                    s.physical_requirement_status,
                    s.honors_awards_points,
                    s.residence_points,
                    s.other_screening_points,
                    c.id AS course_id,
                    c.course_code,
                    c.course_name,
                    SUM(
                        CASE
                            WHEN ep.name IN ('English','Filipino','Literature','Math','Science','Studies','Humanities')
                                 AND w.weight > 0
                                 AND COALESCE(ep.max_score, 0) > 0
                                THEN (ses.score / ep.max_score) * w.weight
                            ELSE 0
                        END
                    ) AS matrix_score,
                    SUM(
                        CASE
                            WHEN ep.name IN ('English','Filipino','Literature','Math','Science','Studies','Humanities')
                                 AND w.weight > 0
                                THEN w.weight
                            ELSE 0
                        END
                    ) AS matrix_required_score,
                    CASE
                        WHEN (
                            (COALESCE(s.first_choice, '') REGEXP '^[0-9]+$' AND CAST(COALESCE(s.first_choice, '0') AS UNSIGNED) = c.id)
                            OR UPPER(TRIM(c.course_code)) = UPPER(TRIM(COALESCE(s.first_choice, '')))
                        ) THEN 1
                        WHEN (
                            (COALESCE(s.second_choice, '') REGEXP '^[0-9]+$' AND CAST(COALESCE(s.second_choice, '0') AS UNSIGNED) = c.id)
                            OR UPPER(TRIM(c.course_code)) = UPPER(TRIM(COALESCE(s.second_choice, '')))
                        ) THEN 2
                        ELSE 3
                    END AS choice_priority
                FROM students s
                INNER JOIN student_exam_scores ses
                    ON ses.student_id = s.id
                   AND ses.is_deleted = 0
                INNER JOIN exam_parts ep
                    ON ep.id = ses.exam_part_id
                   AND ep.is_deleted = 0
                INNER JOIN weights w
                    ON w.exam_part_id = ses.exam_part_id
                   AND w.is_deleted = 0
                INNER JOIN courses c
                    ON c.id = w.course_id
                   AND c.is_deleted = 0
                WHERE s.id IN ($placeholders)
                  AND s.is_deleted = 0
                GROUP BY s.id, c.id
                HAVING matrix_required_score > 0
                ORDER BY s.id ASC, choice_priority ASC, c.course_code ASC";

        $st = Database::pdo()->prepare($sql);
        $st->execute($studentIds);

        $scoreMaps = [];
        foreach ($studentIds as $studentId) {
            $scoreMaps[$studentId] = self::getStudentScoresByName((int)$studentId);
        }

        $evaluations = [];
        foreach ($st->fetchAll() as $row) {
            $studentId = (int)$row['student_id'];
            $scoreMap = $scoreMaps[$studentId] ?? [];
            $examResult = self::determineExamResultFromScoresMap($scoreMap);
            $composites = self::calculateCompositeScores($scoreMap);
            $part2bProfile = self::calculatePart2BProfile($scoreMap);
            $requirements = self::courseRequirements((string)$row['course_code']);
            $cctChoiceResult = self::evaluateCctChoiceRequirement(
                (string)($row['cct_choice'] ?? 'none'),
                (string)$requirements['cct_choice_rule']
            );
            $choiceResult = self::evaluateChoiceRequirement(
                (int)$row['course_id'],
                (string)$row['course_code'],
                (string)($row['first_choice'] ?? ''),
                (string)($row['second_choice'] ?? ''),
                $requirements['choice_rule']
            );
            $strandResult = self::evaluateStrandRequirement(
                (string)($row['shs_strand'] ?? ''),
                $requirements['allowed_strands']
            );
            $gpaResult = self::evaluateGpaRequirement(
                $row['gpa'] === null ? null : (float)$row['gpa'],
                $requirements['minimum_gpa']
            );
            $physicalResult = self::evaluatePhysicalRequirement(
                (string)($row['physical_requirement_status'] ?? 'pending'),
                (bool)$requirements['requires_physical']
            );
            $riasecResult = self::evaluateRiasecRequirement(
                $part2bProfile['letters'],
                $requirements['riasec_match']
            );
            $displayScores = self::calculateQualificationDisplayScores(
                $matrixScore = (float)$row['matrix_score'],
                $matrixRequiredScore = (float)$row['matrix_required_score'],
                $requirements,
                (string)$row['course_code'],
                $scoreMap
            );
            $coreContribution = $displayScores['core'];
            $choiceContribution = ($cctChoiceResult ? (float)$requirements['cct_choice_weight'] : 0.0)
                + ($choiceResult ? (float)$requirements['degree_choice_weight'] : 0.0);
            $otherContribution = self::calculateOtherCriteriaContribution(
                (array)$requirements['other_weights'],
                $strandResult,
                $gpaResult,
                $physicalResult,
                $row['honors_awards_points'] === null ? null : (float)$row['honors_awards_points'],
                $row['residence_points'] === null ? null : (float)$row['residence_points'],
                $row['other_screening_points'] === null ? null : (float)$row['other_screening_points']
            );
            $qualificationPercent = min(100.0, $coreContribution + $choiceContribution + $otherContribution['total']);
            $passedThresholds =
                $displayScores['achievement'] >= $requirements['achievement_min']
                && $displayScores['aptitude'] >= $requirements['aptitude_min']
                && $displayScores['personality'] >= $requirements['personality_min'];
            $coursePassed = $matrixRequiredScore > 0 && $matrixScore >= $matrixRequiredScore;
            $qualified =
                $examResult === 'passed'
                && $passedThresholds;
            $bonusScore = self::calculateBonusScore(
                $row['honors_awards_points'] === null ? null : (float)$row['honors_awards_points'],
                $row['residence_points'] === null ? null : (float)$row['residence_points'],
                $row['other_screening_points'] === null ? null : (float)$row['other_screening_points']
            );
            $overallScore = $displayScores['achievement'] + $displayScores['aptitude'] + $displayScores['personality'] + $bonusScore;
            $matrixPercent = $matrixRequiredScore > 0 ? ($matrixScore / $matrixRequiredScore) * 100.0 : 0.0;

            $evaluations[$studentId][] = [
                'course_code' => (string)$row['course_code'],
                'course_name' => (string)$row['course_name'],
                'achieved_score' => $matrixScore,
                'required_score' => $matrixRequiredScore,
                'matrix_score' => $matrixScore,
                'matrix_required_score' => $matrixRequiredScore,
                'achievement_score' => $displayScores['achievement'],
                'aptitude_score' => $displayScores['aptitude'],
                'aptitude_set_a_score' => $displayScores['aptitude'],
                'aptitude_profile_bonus' => $displayScores['aptitude_profile_bonus'],
                'personality_score' => $displayScores['personality'],
                'achievement_required' => $requirements['achievement_min'],
                'aptitude_required' => $requirements['aptitude_min'],
                'personality_required' => $requirements['personality_min'],
                'bonus_score' => $bonusScore,
                'overall_score' => $overallScore,
                'total_score' => $qualificationPercent,
                'qualification_percentage' => $qualificationPercent,
                'core_percentage' => $coreContribution,
                'choice_percentage' => $choiceContribution,
                'other_percentage' => $otherContribution['total'],
                'matrix_percent' => $matrixPercent,
                'qualified' => $qualified,
                'course_passed' => $coursePassed,
                'result_label' => $coursePassed ? 'Passed' : 'Failed',
                'choice_priority' => (int)$row['choice_priority'],
                'is_first_choice' => (int)$row['choice_priority'] === 1,
                'is_second_choice' => (int)$row['choice_priority'] === 2,
                'meets_cct_choice' => $cctChoiceResult,
                'meets_choice' => $choiceResult,
                'meets_strand' => $strandResult,
                'meets_gpa' => $gpaResult,
                'meets_physical' => $physicalResult,
                'meets_riasec' => $riasecResult,
                'exam_result' => $examResult,
                'part2b_profile' => $part2bProfile['profile'],
                'part2b_profile_letters' => $part2bProfile['letters'],
                'part2b_profile_scores' => $part2bProfile['scores'],
                'riasec_required' => $requirements['riasec_match'],
                'qualification_weights' => [
                    'core' => (float)$requirements['core_weight'],
                    'cct_choice' => (float)$requirements['cct_choice_weight'],
                    'degree_choice' => (float)$requirements['degree_choice_weight'],
                    'other' => (float)array_sum((array)$requirements['other_weights']),
                ],
                'other_weights' => (array)$requirements['other_weights'],
                'other_breakdown' => $otherContribution['breakdown'],
            ];
        }

        return $evaluations;
    }

    public static function getQualifiedRecommendationsForStudents(array $studentIds, int $limit = 0): array
    {
        $evaluations = self::getCourseEvaluationsForStudents($studentIds);
        $recommendations = [];

        foreach ($evaluations as $studentId => $items) {
            $qualified = array_values(array_filter($items, static fn(array $item): bool => (bool)$item['qualified']));
            if ($limit > 0) {
                $qualified = array_slice($qualified, 0, $limit);
            }
            $recommendations[$studentId] = $qualified;
        }

        return $recommendations;
    }

    public static function getQualifiedRecommendationsForStudent(int $studentId, int $limit = 0): array
    {
        $results = self::getQualifiedRecommendationsForStudents([$studentId], $limit);
        return $results[$studentId] ?? [];
    }

    public static function getCourseRequirementGuide(string $courseCode): array
    {
        return self::courseRequirements($courseCode);
    }

    public static function syncStudentOutcome(int $studentId): void
    {
        $scoreMap = self::getStudentScoresByName($studentId);
        $examResult = self::determineExamResultFromScoresMap($scoreMap);
        $screeningStatus = 'pending';

        if ($examResult === 'failed') {
            $screeningStatus = 'not_qualified';
        } elseif ($examResult === 'passed') {
            $qualifiedPrograms = self::getQualifiedRecommendationsForStudent($studentId);
            $screeningStatus = empty($qualifiedPrograms) ? 'not_qualified' : 'qualified';
        }

        Database::pdo()->prepare("
            UPDATE students
            SET status = :status,
                screening_status = :screening_status
            WHERE id = :id AND is_deleted = 0
        ")->execute([
            ':status' => $examResult,
            ':screening_status' => $screeningStatus,
            ':id' => $studentId,
        ]);
    }

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

        self::syncStudentOutcome($studentId);
        Logger::log(currentUserId(), 'EVALUATE_STUDENT', 'students', $studentId, 'System generated recommendations for student');
    }

    private static function getStudentScoresByName(int $studentId): array
    {
        $st = Database::pdo()->prepare("
            SELECT ep.name, ses.score, ep.max_score
            FROM student_exam_scores ses
            INNER JOIN exam_parts ep
                ON ep.id = ses.exam_part_id
               AND ep.is_deleted = 0
            WHERE ses.student_id = :student_id
              AND ses.is_deleted = 0
        ");
        $st->execute([':student_id' => $studentId]);

        $map = [];
        foreach ($st->fetchAll() as $row) {
            $map[(string)$row['name']] = [
                'score' => (float)$row['score'],
                'max_score' => (float)$row['max_score'],
            ];
        }

        return $map;
    }

    private static function determineExamResultFromScoresMap(array $scoreMap): string
    {
        $hasAnyPart1Score = false;
        foreach (self::PART1_NAMES as $name) {
            if (isset($scoreMap[$name])) {
                $hasAnyPart1Score = true;
                break;
            }
        }

        if (!$hasAnyPart1Score) {
            return 'pending';
        }

        foreach (self::PART1_NAMES as $name) {
            if (!isset($scoreMap[$name])) {
                return 'failed';
            }
            if ((float)$scoreMap[$name]['score'] < 12.0) {
                return 'failed';
            }
        }

        return 'passed';
    }

    private static function calculateCompositeScores(array $scoreMap): array
    {
        $part1Raw = self::sumNamedScores($scoreMap, self::PART1_NAMES);
        $part1Max = self::sumNamedMaxScores($scoreMap, self::PART1_NAMES, 210.0);
        $part2aRaw = self::sumNamedScores($scoreMap, self::PART2A_NAMES);
        $part2aMax = self::sumNamedMaxScores($scoreMap, self::PART2A_NAMES, 100.0);
        $part2bRaw = self::sumNamedScores($scoreMap, self::PART2B_NAMES);
        $part2bMax = self::sumNamedMaxScores($scoreMap, self::PART2B_NAMES, 30.0);
        $part4Raw = self::calculatePersonalityRaw($scoreMap);
        $part4Max = 50.0;

        return [
            'achievement' => $part1Max > 0 ? ($part1Raw / $part1Max) * 50.0 : 0.0,
            'aptitude_set_a' => $part2aMax > 0 ? ($part2aRaw / $part2aMax) * 20.0 : 0.0,
            'part2b_raw' => $part2bRaw,
            'part2b_max' => $part2bMax,
            'personality' => $part4Max > 0 ? ($part4Raw / $part4Max) * 20.0 : 0.0,
        ];
    }

    private static function calculateQualificationDisplayScores(
        float $matrixScore,
        float $matrixRequiredScore,
        array $requirements,
        string $courseCode,
        array $scoreMap
    ): array {
        $achievementRequired = (float)($requirements['achievement_min'] ?? 0.0);
        $aptitudeRequired = (float)($requirements['aptitude_min'] ?? 0.0);
        $personalityRequired = (float)($requirements['personality_min'] ?? 0.0);
        $achievementMax = 50.0;
        $aptitudeMax = 30.0;
        $personalityMax = 20.0;

        $achievementScore = self::calculateAchievementDisplayScore(
            $courseCode,
            $scoreMap,
            $achievementMax,
            $achievementRequired
        );
        $aptitudeScore = self::calculateThresholdScaledScore(
            $courseCode,
            self::PART2A_NAMES,
            $scoreMap,
            $aptitudeMax,
            $aptitudeRequired
        );
        $personalityScore = self::calculateThresholdScaledScore(
            $courseCode,
            self::PART4_NAMES,
            $scoreMap,
            $personalityMax,
            $personalityRequired,
            true
        );

        return [
            'achievement' => $achievementScore,
            'aptitude' => $aptitudeScore,
            'personality' => $personalityScore,
            'core' => (($achievementScore + $aptitudeScore + $personalityScore) / 100.0) * (float)($requirements['core_weight'] ?? 80.0),
            'aptitude_profile_bonus' => 0.0,
        ];
    }

    private static function calculateAchievementDisplayScore(
        string $courseCode,
        array $scoreMap,
        float $achievementMax,
        float $achievementThreshold
    ): float
    {
        if ($achievementMax <= 0) {
            return 0.0;
        }

        $requiredByPart = self::getCourseExamPartWeightsByName($courseCode, self::PART1_NAMES);
        $requiredTotal = 0.0;
        $matchedMinimumTotal = 0.0;
        $extraEarnedTotal = 0.0;
        $extraPossibleTotal = 0.0;

        foreach (self::PART1_NAMES as $partName) {
            $required = (float)($requiredByPart[$partName] ?? 0.0);
            if ($required <= 0) {
                continue;
            }

            $requiredTotal += $required;
            $actualScore = isset($scoreMap[$partName]) ? (float)$scoreMap[$partName]['score'] : 0.0;
            $partMax = isset($scoreMap[$partName]) ? (float)$scoreMap[$partName]['max_score'] : 30.0;
            $boundedActual = max(0.0, min($actualScore, $partMax));

            $matchedMinimumTotal += min($boundedActual, $required);

            $extraPossible = max(0.0, $partMax - $required);
            $extraPossibleTotal += $extraPossible;
            if ($boundedActual > $required) {
                $extraEarnedTotal += min($boundedActual - $required, $extraPossible);
            }
        }

        if ($requiredTotal <= 0) {
            return 0.0;
        }

        $thresholdScore = ($matchedMinimumTotal / $requiredTotal) * $achievementThreshold;
        if ($matchedMinimumTotal < $requiredTotal || $extraPossibleTotal <= 0) {
            return min($achievementMax, $thresholdScore);
        }

        $bonusHeadroom = max(0.0, $achievementMax - $achievementThreshold);
        $bonusScore = ($extraEarnedTotal / $extraPossibleTotal) * $bonusHeadroom;

        return min($achievementMax, $achievementThreshold + $bonusScore);
    }

    private static function calculateThresholdScaledScore(
        string $courseCode,
        array $partNames,
        array $scoreMap,
        float $displayMax,
        float $threshold,
        bool $invertNeuroticism = false
    ): float {
        if ($displayMax <= 0) {
            return 0.0;
        }

        $requiredByPart = self::getCourseExamPartWeightsByName($courseCode, $partNames);
        $requiredTotal = 0.0;
        $matchedMinimumTotal = 0.0;
        $extraEarnedTotal = 0.0;
        $extraPossibleTotal = 0.0;

        foreach ($partNames as $partName) {
            $required = (float)($requiredByPart[$partName] ?? 0.0);
            if ($required <= 0) {
                continue;
            }

            $requiredTotal += $required;
            $partMax = isset($scoreMap[$partName]) ? (float)$scoreMap[$partName]['max_score'] : (($invertNeuroticism || in_array($partName, self::PART2A_NAMES, true)) ? 10.0 : 30.0);
            $actualScore = isset($scoreMap[$partName]) ? (float)$scoreMap[$partName]['score'] : 0.0;

            if ($invertNeuroticism && $partName === 'Neuroticism') {
                $actualScore = max(0.0, $partMax - $actualScore);
            }

            $boundedActual = max(0.0, min($actualScore, $partMax));
            $matchedMinimumTotal += min($boundedActual, $required);

            $extraPossible = max(0.0, $partMax - $required);
            $extraPossibleTotal += $extraPossible;
            if ($boundedActual > $required) {
                $extraEarnedTotal += min($boundedActual - $required, $extraPossible);
            }
        }

        if ($requiredTotal <= 0) {
            return 0.0;
        }

        $thresholdScore = ($matchedMinimumTotal / $requiredTotal) * $threshold;
        if ($matchedMinimumTotal < $requiredTotal || $extraPossibleTotal <= 0) {
            return min($displayMax, $thresholdScore);
        }

        $bonusHeadroom = max(0.0, $displayMax - $threshold);
        $bonusScore = ($extraEarnedTotal / $extraPossibleTotal) * $bonusHeadroom;

        return min($displayMax, $threshold + $bonusScore);
    }

    private static function calculatePart2BProfile(array $scoreMap): array
    {
        $lettersByName = [
            'Realistic' => 'R',
            'Investigative' => 'I',
            'Artistic' => 'A',
            'Social' => 'S',
            'Enterprising' => 'E',
            'Conventional' => 'C',
        ];

        $scores = [];
        foreach (self::PART2B_NAMES as $name) {
            $letter = $lettersByName[$name] ?? strtoupper(substr($name, 0, 1));
            $scores[$letter] = isset($scoreMap[$name]) ? (float)$scoreMap[$name]['score'] : 0.0;
        }

        arsort($scores, SORT_NUMERIC);
        $topThree = array_slice(array_keys($scores), 0, 3);

        return [
            'profile' => implode('-', $topThree),
            'letters' => $topThree,
            'scores' => $scores,
        ];
    }

    private static function calculatePersonalityRaw(array $scoreMap): float
    {
        $total = 0.0;
        foreach (self::PART4_NAMES as $name) {
            if (!isset($scoreMap[$name])) {
                continue;
            }
            $score = (float)$scoreMap[$name]['score'];
            $max = (float)$scoreMap[$name]['max_score'];
            if ($name === 'Neuroticism') {
                $score = max(0.0, $max - $score);
            }
            $total += $score;
        }

        return $total;
    }

    private static function getCourseExamPartWeightsByName(string $courseCode, array $partNames): array
    {
        $map = self::getCourseExamPartWeightMap();
        $courseWeights = $map[strtoupper(trim($courseCode))] ?? [];
        $result = [];

        foreach ($partNames as $partName) {
            $result[$partName] = (float)($courseWeights[$partName] ?? 0.0);
        }

        return $result;
    }

    private static function getCourseExamPartWeightMap(): array
    {
        if (self::$courseExamPartWeightMap !== null) {
            return self::$courseExamPartWeightMap;
        }

        $sql = "
            SELECT c.course_code, ep.name AS exam_part_name, w.weight
            FROM weights w
            INNER JOIN courses c
                ON c.id = w.course_id
               AND c.is_deleted = 0
            INNER JOIN exam_parts ep
                ON ep.id = w.exam_part_id
               AND ep.is_deleted = 0
            WHERE w.is_deleted = 0
        ";

        $rows = Database::pdo()->query($sql)->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $courseCode = strtoupper(trim((string)($row['course_code'] ?? '')));
            $partName = (string)($row['exam_part_name'] ?? '');
            if ($courseCode === '' || $partName === '') {
                continue;
            }
            $map[$courseCode][$partName] = (float)($row['weight'] ?? 0.0);
        }

        self::$courseExamPartWeightMap = $map;
        return self::$courseExamPartWeightMap;
    }

    private static function sumNamedScores(array $scoreMap, array $names): float
    {
        $total = 0.0;
        foreach ($names as $name) {
            if (isset($scoreMap[$name])) {
                $total += (float)$scoreMap[$name]['score'];
            }
        }
        return $total;
    }

    private static function sumNamedMaxScores(array $scoreMap, array $names, float $fallback): float
    {
        $total = 0.0;
        foreach ($names as $name) {
            if (isset($scoreMap[$name])) {
                $total += (float)$scoreMap[$name]['max_score'];
            }
        }
        return $total > 0 ? $total : $fallback;
    }

    private static function courseRequirements(string $courseCode): array
    {
        $code = strtoupper(trim($courseCode));
        $defaults = [
            'achievement_min' => 50.0,
            'aptitude_min' => 30.0,
            'personality_min' => 20.0,
            'core_weight' => 80.0,
            'cct_choice_weight' => 5.0,
            'degree_choice_weight' => 5.0,
            'other_weights' => [],
            'cct_choice_rule' => 'none',
            'choice_rule' => 'none',
            'riasec_match' => [],
            'allowed_strands' => [],
            'strand_display' => 'Any strand',
            'minimum_gpa' => null,
            'physical_rule' => 'N/A',
            'requires_physical' => false,
        ];

        return match ($code) {
            'BSED-ENG', 'BSED-FIL', 'BSED-MATH', 'BSED-SS', 'BSED-SCI' => [
                'achievement_min' => 40.0,
                'aptitude_min' => 24.0,
                'personality_min' => 16.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['gpa' => 10.0],
                'cct_choice_rule' => 'first',
                'choice_rule' => 'first',
                'riasec_match' => match ($code) {
                    'BSED-ENG', 'BSED-FIL' => ['R', 'A', 'S'],
                    'BSED-MATH' => ['R', 'I', 'S', 'C'],
                    default => ['R', 'I', 'S'],
                },
                'allowed_strands' => [],
                'strand_display' => 'Any strand',
                'minimum_gpa' => 86.0,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            'BSHRDM', 'BSMM' => [
                'achievement_min' => 25.0,
                'aptitude_min' => 15.0,
                'personality_min' => 10.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['strand' => 5.0, 'other' => 5.0],
                'cct_choice_rule' => 'first_or_second',
                'choice_rule' => 'first_or_second',
                'riasec_match' => $code === 'BSHRDM' ? ['R', 'S', 'C'] : ['A', 'E'],
                'allowed_strands' => [],
                'strand_display' => 'Any or GAS, ABM',
                'minimum_gpa' => null,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            'BSOA' => [
                'achievement_min' => 20.0,
                'aptitude_min' => 12.0,
                'personality_min' => 8.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['strand' => 5.0, 'other' => 5.0],
                'cct_choice_rule' => 'first_or_second',
                'choice_rule' => 'first_or_second',
                'riasec_match' => ['R', 'C'],
                'allowed_strands' => [],
                'strand_display' => 'Any or GAS, ABM',
                'minimum_gpa' => null,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            'BSTM' => [
                'achievement_min' => 30.0,
                'aptitude_min' => 18.0,
                'personality_min' => 10.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['physical' => 10.0],
                'cct_choice_rule' => 'first_or_second',
                'choice_rule' => 'first_or_second',
                'riasec_match' => ['R', 'S', 'C'],
                'allowed_strands' => [],
                'strand_display' => 'Any',
                'minimum_gpa' => null,
                'physical_rule' => 'Height/PA',
                'requires_physical' => true,
            ],
            'BSHM' => [
                'achievement_min' => 30.0,
                'aptitude_min' => 18.0,
                'personality_min' => 10.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['strand' => 10.0],
                'cct_choice_rule' => 'first_or_second',
                'choice_rule' => 'first_or_second',
                'riasec_match' => ['R', 'S', 'C'],
                'allowed_strands' => [],
                'strand_display' => 'Any, TVL',
                'minimum_gpa' => null,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            'BSIT', 'BSCS' => [
                'achievement_min' => 50.0,
                'aptitude_min' => 30.0,
                'personality_min' => 20.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['strand' => 10.0],
                'cct_choice_rule' => 'first',
                'choice_rule' => 'first',
                'riasec_match' => ['R', 'I', 'C'],
                'allowed_strands' => ['ICT', 'TVL-ICT', 'STEM'],
                'strand_display' => 'TVL-ICT, STEM',
                'minimum_gpa' => null,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            'ABPSY' => [
                'achievement_min' => 50.0,
                'aptitude_min' => 30.0,
                'personality_min' => 20.0,
                'core_weight' => 80.0,
                'cct_choice_weight' => 5.0,
                'degree_choice_weight' => 5.0,
                'other_weights' => ['strand' => 5.0, 'gpa' => 5.0],
                'cct_choice_rule' => 'first_or_second',
                'choice_rule' => 'first',
                'riasec_match' => ['S', 'I'],
                'allowed_strands' => ['HUMSS', 'STEM'],
                'strand_display' => 'HUMSS, STEM',
                'minimum_gpa' => 86.0,
                'physical_rule' => 'N/A',
                'requires_physical' => false,
            ],
            default => $defaults,
        };
    }

    private static function evaluateChoiceRequirement(int $courseId, string $courseCode, string $firstChoice, string $secondChoice, string $rule): bool
    {
        $matchesFirst = self::courseChoiceMatches($courseId, $courseCode, $firstChoice);
        $matchesSecond = self::courseChoiceMatches($courseId, $courseCode, $secondChoice);

        return match ($rule) {
            'first' => $matchesFirst,
            'first_or_second' => $matchesFirst || $matchesSecond,
            default => true,
        };
    }

    private static function courseChoiceMatches(int $courseId, string $courseCode, string $choiceValue): bool
    {
        $choiceValue = trim($choiceValue);
        if ($choiceValue === '') {
            return false;
        }

        if (ctype_digit($choiceValue) && (int)$choiceValue === $courseId) {
            return true;
        }

        return strtoupper(trim($courseCode)) === strtoupper($choiceValue);
    }

    private static function evaluateCctChoiceRequirement(string $cctChoice, string $rule): bool
    {
        $cctChoice = strtolower(trim($cctChoice));

        return match ($rule) {
            'first' => $cctChoice === 'first',
            'first_or_second' => in_array($cctChoice, ['first', 'second'], true),
            default => true,
        };
    }

    private static function evaluateStrandRequirement(string $studentStrand, array $allowedStrands): bool
    {
        if (empty($allowedStrands)) {
            return true;
        }

        $studentStrand = self::normalizeStrand($studentStrand);
        if ($studentStrand === '') {
            return false;
        }

        foreach ($allowedStrands as $allowedStrand) {
            $allowedStrand = self::normalizeStrand($allowedStrand);
            if ($allowedStrand !== '' && (str_contains($studentStrand, $allowedStrand) || str_contains($allowedStrand, $studentStrand))) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeStrand(string $strand): string
    {
        $strand = strtoupper(trim($strand));
        return match ($strand) {
            'TVL ICT', 'TVL-ICT' => 'ICT',
            default => $strand,
        };
    }

    private static function evaluateGpaRequirement(?float $gpa, ?float $minimumGpa): bool
    {
        if ($minimumGpa === null) {
            return true;
        }

        if ($gpa === null) {
            return false;
        }

        return $gpa >= $minimumGpa;
    }

    private static function evaluatePhysicalRequirement(string $status, bool $required): bool
    {
        if (!$required) {
            return true;
        }

        return $status === 'met';
    }

    private static function evaluateRiasecRequirement(array $profileLetters, array $requiredLetters): bool
    {
        if (empty($requiredLetters)) {
            return true;
        }

        $profileLetters = array_map(
            static fn(string $letter): string => strtoupper(trim($letter)),
            $profileLetters
        );
        $requiredLetters = array_map(
            static fn(string $letter): string => strtoupper(trim($letter)),
            $requiredLetters
        );

        foreach ($requiredLetters as $requiredLetter) {
            if ($requiredLetter !== '' && !in_array($requiredLetter, $profileLetters, true)) {
                return false;
            }
        }

        return true;
    }

    private static function calculateBonusScore(?float $honors, ?float $residence, ?float $others): float
    {
        $bonus = 0.0;
        foreach ([$honors, $residence, $others] as $value) {
            if ($value !== null) {
                $bonus += max(0.0, min(5.0, $value));
            }
        }

        return $bonus;
    }

    private static function calculateOtherCriteriaContribution(
        array $weights,
        bool $strandMet,
        bool $gpaMet,
        bool $physicalMet,
        ?float $honorsPoints,
        ?float $residencePoints,
        ?float $otherPoints
    ): array {
        $breakdown = [
            'strand' => 0.0,
            'gpa' => 0.0,
            'physical' => 0.0,
            'honors' => 0.0,
            'residence' => 0.0,
            'other' => 0.0,
        ];

        foreach ($weights as $key => $weight) {
            $weight = (float)$weight;
            if ($weight <= 0) {
                continue;
            }

            switch ($key) {
                case 'strand':
                    $breakdown['strand'] = $strandMet ? $weight : 0.0;
                    break;
                case 'gpa':
                    $breakdown['gpa'] = $gpaMet ? $weight : 0.0;
                    break;
                case 'physical':
                    $breakdown['physical'] = $physicalMet ? $weight : 0.0;
                    break;
                case 'honors':
                    $breakdown['honors'] = self::scaleBonusPoints($honorsPoints, $weight);
                    break;
                case 'residence':
                    $breakdown['residence'] = self::scaleBonusPoints($residencePoints, $weight);
                    break;
                case 'other':
                    $breakdown['other'] = self::scaleBonusPoints($otherPoints, $weight);
                    break;
            }
        }

        return [
            'total' => array_sum($breakdown),
            'breakdown' => $breakdown,
        ];
    }

    private static function scaleBonusPoints(?float $points, float $weight): float
    {
        if ($points === null || $weight <= 0) {
            return 0.0;
        }

        $bounded = max(0.0, min(5.0, $points));
        return ($bounded / 5.0) * $weight;
    }

    private static function hasCctChoiceColumn(): bool
    {
        if (self::$cctChoiceColumnExists !== null) {
            return self::$cctChoiceColumnExists;
        }

        try {
            $st = Database::pdo()->query("SHOW COLUMNS FROM students LIKE 'cct_choice'");
            self::$cctChoiceColumnExists = (bool)$st->fetch();
        } catch (Throwable) {
            self::$cctChoiceColumnExists = false;
        }

        return self::$cctChoiceColumnExists;
    }
}
