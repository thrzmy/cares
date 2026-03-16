<?php
declare(strict_types=1);

$courses = $courses ?? [];
$courseCategories = $courseCategories ?? [];
$groupedParts = $groupedParts ?? [];
$weightsMap = $weightsMap ?? [];
$criteriaByCourse = $criteriaByCourse ?? [];
$matrixOverview = $matrixOverview ?? ['course_count' => 0, 'category_count' => 0, 'part_count' => 0];
$q = (string)($q ?? '');
$courseCategoryFilter = (string)($courseCategoryFilter ?? '');
$success = $success ?? null;
$error = $error ?? null;

$courseDisplayNames = [
    'BSED-ENG' => 'Major in English',
    'BSED-FIL' => 'Major in Filipino',
    'BSED-MATH' => 'Major in Mathematics',
    'BSED-SS' => 'Major in Social Studies',
    'BSED-SCI' => 'Major in Science',
    'BSHRDM' => 'Major in Human Resources Development Management',
    'BSMM' => 'Major in Marketing Management',
    'BSOA' => 'Major in Office Administration',
    'BSTM' => 'Major in Tourism',
    'BSHM' => 'Major in Hospitality Management',
    'BSIT' => 'Information and Technology',
    'BSCS' => 'Computer Science',
    'ABPSY' => 'Psychology',
];

$choiceRuleLabel = static function (string $rule): string {
    return match ($rule) {
        'first' => '1st choice',
        'first_or_second' => '1st or 2nd choice',
        default => 'N/A',
    };
};

$strandLabel = static function (array $criteria): string {
    $strandDisplay = trim((string)($criteria['strand_display'] ?? ''));
    if ($strandDisplay !== '') {
        return $strandDisplay;
    }

    $allowedStrands = (array)($criteria['allowed_strands'] ?? []);
    if (empty($allowedStrands)) {
        return 'Any strand';
    }

    return implode(', ', $allowedStrands);
};

$physicalLabel = static function (array $criteria): string {
    $physicalRule = trim((string)($criteria['physical_rule'] ?? ''));
    if ($physicalRule !== '') {
        return $physicalRule;
    }

    return !empty($criteria['requires_physical']) ? 'Required' : 'N/A';
};

$cctChoiceLabel = static function (array $criteria) use ($choiceRuleLabel): string {
    return $choiceRuleLabel((string)($criteria['cct_choice_rule'] ?? 'none'));
};

$criteriaWeightText = static function (float $weight): string {
    return rtrim(rtrim(number_format($weight, 2), '0'), '.') . '%';
};

$groupCoreSummary = static function (array $coursesInCategory, array $criteriaByCourse): string {
    $values = [];
    foreach ($coursesInCategory as $course) {
        $criteria = $criteriaByCourse[(int)$course['id']] ?? [];
        $values[] = (float)($criteria['core_weight'] ?? 80.0);
    }
    $values = array_values(array_unique($values));
    return count($values) === 1 ? (rtrim(rtrim(number_format($values[0], 2), '0'), '.') . '%') : 'Mixed';
};

$groupChoiceSummary = static function (array $coursesInCategory, array $criteriaByCourse, string $key): string {
    $values = [];
    foreach ($coursesInCategory as $course) {
        $criteria = $criteriaByCourse[(int)$course['id']] ?? [];
        $weight = $key === 'cct'
            ? (float)($criteria['cct_choice_weight'] ?? 5.0)
            : (float)($criteria['degree_choice_weight'] ?? 5.0);
        $values[] = rtrim(rtrim(number_format($weight, 2), '0'), '.') . '%';
    }
    $values = array_values(array_unique($values));
    return count($values) === 1 ? $values[0] : 'Mixed';
};

$groupOtherSummary = static function (array $coursesInCategory, array $criteriaByCourse): string {
    $values = [];
    foreach ($coursesInCategory as $course) {
        $criteria = $criteriaByCourse[(int)$course['id']] ?? [];
        $values[] = rtrim(rtrim(number_format((float)array_sum((array)($criteria['other_weights'] ?? [])), 2), '0'), '.') . '%';
    }
    $values = array_values(array_unique($values));
    return count($values) === 1 ? $values[0] : 'Mixed';
};

$groupedCourses = [];
foreach ($courseCategories as $courseCategory) {
    $groupedCourses[$courseCategory] = [];
}
foreach ($courses as $course) {
    $courseCategory = trim((string)($course['course_category'] ?? 'Other Programs'));
    if (!isset($groupedCourses[$courseCategory])) {
        $groupedCourses[$courseCategory] = [];
    }
    $groupedCourses[$courseCategory][] = $course;
}

$categoryToneMap = [
    'BS Secondary Education' => 'tone-education',
    'School of Business and Management' => 'tone-business',
    'School of Hospitality and Tourism Management' => 'tone-hospitality',
    'School of Computer Studies' => 'tone-computing',
    'School of Arts and Sciences' => 'tone-arts',
    'Other Programs' => 'tone-other',
];
?>

<div id="matrixConfigPage">
<div class="page-header mb-3">
    <div>
        <div class="page-kicker">Administrator</div>
        <h4 class="fw-bold mb-1">Matrix Configuration</h4>
        <p class="page-subtitle">Manage program requirements and scoring details.</p>
    </div>
    <div class="page-actions d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRawScoresModal">Edit Raw Scores</button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addExamPartModal">Add Exam Part</button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">Add Course</button>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/administrator/matrix">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label small">Search Programs</label>
            <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Search by program name, course code, or category">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small">Program Group</label>
            <select class="form-select" name="course_category">
                <option value="">All program groups</option>
                <?php foreach ($courseCategories as $courseCategory): ?>
                    <option value="<?= e((string)$courseCategory) ?>" <?= $courseCategoryFilter === (string)$courseCategory ? 'selected' : '' ?>>
                        <?= e((string)$courseCategory) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row g-2 mt-1">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
                <div class="text-muted small"><?= e((string)count($courses)) ?> matching program<?= count($courses) === 1 ? '' : 's' ?></div>
                <div class="d-grid d-md-flex gap-2 justify-content-md-end">
                    <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/matrix">Clear Filters</a>
                    <button class="btn btn-primary" type="submit">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="matrix-simple-shell">
    <?php if (empty($courses)): ?>
        <div class="matrix-empty-state">
            <h6 class="mb-2">No programs yet in the matrix.</h6>
            <p class="text-muted mb-3">Add a program to start building its matrix and criteria.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">Add Course</button>
        </div>
    <?php else: ?>
        <div class="matrix-mini-legend pb-2">
            <span class="matrix-mini-legend-item"><strong>A</strong> Achievement</span>
            <span class="matrix-mini-legend-item"><strong>B</strong> Aptitude</span>
            <span class="matrix-mini-legend-item"><strong>C</strong> Personality</span>
            <span class="matrix-mini-legend-item"><strong>D</strong> CCT as a Choice</span>
            <span class="matrix-mini-legend-item"><strong>E</strong> Degree Program</span>
            <span class="matrix-mini-legend-item"><strong>O</strong> Others</span>
        </div>
        <div class="table-responsive matrix-sticky-table-wrap">
            <table class="table matrix-simple-table matrix-grouped-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>A</th>
                        <th>B</th>
                        <th>C</th>
                        <th>D</th>
                        <th>E</th>
                        <th>O</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedCourses as $courseCategory => $coursesInCategory): ?>
                        <?php if (empty($coursesInCategory)) continue; ?>
                        <tr class="matrix-group-row">
                            <td class="matrix-group-banner-cell <?= e($categoryToneMap[$courseCategory] ?? 'tone-other') ?>">
                                <div class="matrix-group-banner-text"><?= e($courseCategory) ?></div>
                            </td>
                            <td colspan="3" class="matrix-group-meta-cell"><?= e($groupCoreSummary($coursesInCategory, $criteriaByCourse)) ?></td>
                            <td class="matrix-group-meta-cell"><?= e($groupChoiceSummary($coursesInCategory, $criteriaByCourse, 'cct')) ?></td>
                            <td class="matrix-group-meta-cell"><?= e($groupChoiceSummary($coursesInCategory, $criteriaByCourse, 'degree')) ?></td>
                            <td class="matrix-group-meta-cell"><?= e($groupOtherSummary($coursesInCategory, $criteriaByCourse)) ?></td>
                            <td class="matrix-group-meta-cell matrix-group-meta-cell--actions"></td>
                        </tr>
                        <?php foreach ($coursesInCategory as $course): ?>
                            <?php
                            $courseId = (int)$course['id'];
                            $criteria = $criteriaByCourse[$courseId] ?? [];
                            $displayName = $courseDisplayNames[(string)$course['course_code']] ?? (string)$course['course_name'];
                            $displayLabel = trim(((string)($course['course_category'] ?? '')) . ' - ' . $displayName, ' -');
                            ?>
                            <tr>
                                <td>
                                    <div class="matrix-program-cell-block">
                                        <div class="matrix-program-title-row">
                                            <span class="matrix-program-title"><?= e($displayName) ?></span>
                                            <span class="matrix-program-code"><?= e((string)$course['course_code']) ?></span>
                                            <button
                                                type="button"
                                                class="btn btn-link matrix-program-edit p-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCourseModal<?= $courseId ?>"
                                                title="Edit Course">
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                                <span class="visually-hidden">Edit Course</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="matrix-cell-pill"><?= e(number_format((float)($criteria['achievement_min'] ?? 0), 0)) ?></span></td>
                                <td><span class="matrix-cell-pill"><?= e(number_format((float)($criteria['aptitude_min'] ?? 0), 0)) ?></span></td>
                                <td><span class="matrix-cell-pill"><?= e(number_format((float)($criteria['personality_min'] ?? 0), 0)) ?></span></td>
                                <td><span class="matrix-cell-copy"><?= e($cctChoiceLabel($criteria)) ?></span></td>
                                <td><span class="matrix-cell-copy"><?= e($choiceRuleLabel((string)($criteria['choice_rule'] ?? 'none'))) ?></span></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-link matrix-other-trigger p-0"
                                        data-bs-toggle="modal"
                                        data-bs-target="#courseCriteriaModal<?= $courseId ?>"
                                        title="Additional Criteria">
                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                        <span class="visually-hidden">Additional Criteria</span>
                                    </button>
                                </td>
                                <td class="text-end">
                                    <div class="matrix-row-actions">
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCourseMatrixModal<?= $courseId ?>">
                                            Edit Scores
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            data-id="<?= $courseId ?>"
                                            data-code="<?= e((string)$course['course_code']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteCourseModal">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php foreach ($courses as $course): ?>
    <?php
    $courseId = (int)$course['id'];
    $criteria = $criteriaByCourse[$courseId] ?? [];
    $displayName = $courseDisplayNames[(string)$course['course_code']] ?? (string)$course['course_name'];
    $displayLabel = trim(((string)($course['course_category'] ?? '')) . ' - ' . $displayName, ' -');
    ?>
    <div class="modal fade" id="editCourseModal<?= $courseId ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= e(BASE_PATH) ?>/administrator/matrix/update-course" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                    <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="edit-course-code-<?= $courseId ?>">Course Code</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edit-course-code-<?= $courseId ?>"
                                name="course_code"
                                value="<?= e((string)$course['course_code']) ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="edit-course-name-<?= $courseId ?>">Course Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="edit-course-name-<?= $courseId ?>"
                                name="course_name"
                                value="<?= e((string)$course['course_name']) ?>"
                                required>
                        </div>
                        <div>
                            <label class="form-label" for="edit-course-category-<?= $courseId ?>">Program Category</label>
                            <select class="form-select" id="edit-course-category-<?= $courseId ?>" name="course_category" required>
                                <?php foreach ($courseCategories as $courseCategory): ?>
                                    <option value="<?= e((string)$courseCategory) ?>" <?= (string)($course['course_category'] ?? '') === (string)$courseCategory ? 'selected' : '' ?>>
                                        <?= e((string)$courseCategory) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="courseCriteriaModal<?= $courseId ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Additional Criteria - <?= e((string)$course['course_code']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="small text-muted mb-3"><?= e($displayLabel) ?></div>
                    <div class="matrix-criteria-grid">
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">SHS Strands</div>
                            <div class="matrix-criteria-value"><?= e($strandLabel($criteria)) ?><?= !empty(($criteria['other_weights']['strand'] ?? null)) ? ' (' . e($criteriaWeightText((float)$criteria['other_weights']['strand'])) . ')' : '' ?></div>
                        </div>
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">GPA</div>
                            <div class="matrix-criteria-value"><?= ($criteria['minimum_gpa'] ?? null) === null ? 'Not required' : e(number_format((float)$criteria['minimum_gpa'], 0)) . ' and above' ?><?= !empty(($criteria['other_weights']['gpa'] ?? null)) ? ' (' . e($criteriaWeightText((float)$criteria['other_weights']['gpa'])) . ')' : '' ?></div>
                        </div>
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">Physical Requirement</div>
                            <div class="matrix-criteria-value"><?= e($physicalLabel($criteria)) ?><?= !empty(($criteria['other_weights']['physical'] ?? null)) ? ' (' . e($criteriaWeightText((float)$criteria['other_weights']['physical'])) . ')' : '' ?></div>
                        </div>
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">Honors / Awards</div>
                            <div class="matrix-criteria-value"><?= !empty(($criteria['other_weights']['honors'] ?? null)) ? 'Up to 5 points (' . e($criteriaWeightText((float)$criteria['other_weights']['honors'])) . ')' : 'Not used' ?></div>
                        </div>
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">Residence</div>
                            <div class="matrix-criteria-value"><?= !empty(($criteria['other_weights']['residence'] ?? null)) ? 'Up to 5 points (' . e($criteriaWeightText((float)$criteria['other_weights']['residence'])) . ')' : 'Not used' ?></div>
                        </div>
                        <div class="matrix-criteria-row">
                            <div class="matrix-criteria-label">Others</div>
                            <div class="matrix-criteria-value"><?= !empty(($criteria['other_weights']['other'] ?? null)) ? 'Up to 5 points (' . e($criteriaWeightText((float)$criteria['other_weights']['other'])) . ')' : 'Not used' ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCourseMatrixModal<?= $courseId ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable matrix-compact-modal">
            <div class="modal-content">
                <form action="<?= e(BASE_PATH) ?>/administrator/matrix" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                    <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">Edit Score Requirements</h5>
                            <div class="small text-muted"><?= e($displayLabel) ?> (<?= e((string)$course['course_code']) ?>)</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <?php foreach ($groupedParts as $group): ?>
                            <section class="encode-part-card matrix-modal-part-card">
                                <header class="encode-part-card__header">
                                    <h6 class="encode-part-card__title mb-0"><?= e((string)$group['category_name']) ?></h6>
                                </header>
                                <div class="encode-part-card__body">
                                    <div class="row g-3">
                                    <?php foreach ($group['parts'] as $part): ?>
                                        <?php $weight = (float)($weightsMap[$courseId][(int)$part['id']] ?? 0.0); ?>
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="encode-score-label" for="course-<?= $courseId ?>-part-<?= (int)$part['id'] ?>">
                                                <span><?= e((string)$part['name']) ?></span>
                                                <span class="encode-score-max">Max: <?= e(number_format((float)$part['max_score'], 0)) ?></span>
                                            </label>
                                            <div class="encode-score-input-wrap matrix-modal-input-wrap">
                                                <input
                                                    type="number"
                                                    class="form-control encode-score-input matrix-modal-score-input"
                                                    id="course-<?= $courseId ?>-part-<?= (int)$part['id'] ?>"
                                                    name="weights[<?= $courseId ?>][<?= (int)$part['id'] ?>]"
                                                    value="<?= e(number_format($weight, 0, '.', '')) ?>"
                                                    min="0"
                                                    max="200"
                                                    step="1"
                                                    inputmode="numeric"
                                                    pattern="[0-9]*">
                                                <span class="encode-score-suffix">pts</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Matrix</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endforeach; ?>

<div class="modal fade" id="editRawScoresModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable matrix-compact-modal">
        <div class="modal-content">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit Maximum Raw Scores</h5>
                        <div class="small text-muted">Update the official maximum score for each exam part.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php foreach ($groupedParts as $group): ?>
                        <section class="encode-part-card matrix-modal-part-card">
                            <header class="encode-part-card__header">
                                <h6 class="encode-part-card__title mb-0"><?= e((string)$group['category_name']) ?></h6>
                            </header>
                            <div class="encode-part-card__body">
                                <div class="row g-3">
                                <?php foreach ($group['parts'] as $part): ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <label class="encode-score-label" for="raw-part-<?= (int)$part['id'] ?>">
                                            <span><?= e((string)$part['name']) ?></span>
                                            <span class="encode-score-max">Official max</span>
                                        </label>
                                        <div class="encode-score-input-wrap matrix-modal-input-wrap">
                                            <input
                                                type="number"
                                                class="form-control encode-score-input matrix-modal-score-input"
                                                id="raw-part-<?= (int)$part['id'] ?>"
                                                name="max_scores[<?= (int)$part['id'] ?>]"
                                                value="<?= e(number_format((float)$part['max_score'], 0, '.', '')) ?>"
                                                min="0"
                                                step="1"
                                                inputmode="numeric"
                                                pattern="[0-9]*"
                                                required>
                                            <span class="encode-score-suffix">pts</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Raw Scores</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix/add-course" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="course_code" name="course_code" placeholder="Example: BSIT" required>
                    </div>
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" placeholder="Example: Bachelor of Science in Information Technology" required>
                    </div>
                    <div>
                        <label for="course_category" class="form-label">Program Category</label>
                        <select class="form-select" id="course_category" name="course_category" required>
                            <?php foreach ($courseCategories as $courseCategory): ?>
                                <option value="<?= e((string)$courseCategory) ?>" <?= ($courseCategoryFilter !== '' ? $courseCategoryFilter : 'BS Secondary Education') === (string)$courseCategory ? 'selected' : '' ?>>
                                    <?= e((string)$courseCategory) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addExamPartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix/add-part" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Exam Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="part_name" class="form-label">Exam Part Name</label>
                        <input type="text" class="form-control" id="part_name" name="name" placeholder="Example: Logical Reasoning" required>
                    </div>
                    <div class="mb-3">
                        <label for="max_score" class="form-label">Maximum Raw Score</label>
                        <input type="number" class="form-control" id="max_score" name="max_score" value="100" min="1" step="0.01" required>
                    </div>
                    <div>
                        <label for="category_id" class="form-label">Category Group</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="0">Uncategorized</option>
                            <?php foreach ($groupedParts as $group): ?>
                                <?php if (($group['category_name'] ?? '') !== 'Uncategorized' && ($group['category_name'] ?? '') !== 'Exam Parts'): ?>
                                    <?php $catId = $group['parts'][0]['category_id'] ?? 0; ?>
                                    <?php if ($catId): ?>
                                        <option value="<?= e((string)$catId) ?>"><?= e((string)$group['category_name']) ?></option>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Exam Part</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteCourseForm" action="<?= e(BASE_PATH) ?>/administrator/matrix/delete-course" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="course_id" id="delete_course_id">
                <input type="hidden" name="filter_q" value="<?= e($q) ?>">
                <input type="hidden" name="filter_course_category" value="<?= e($courseCategoryFilter) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Remove <strong id="delete_course_code">this course</strong> from the matrix?</p>
                    <p class="text-muted small mt-2 mb-0">This will also remove its saved point requirements from the scoring matrix.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline-danger">Confirm Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const matrixPage = document.getElementById('matrixConfigPage');
    if (matrixPage && window.bootstrap && window.bootstrap.Modal) {
        matrixPage.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-bs-target]');
            if (!trigger) {
                return;
            }

            const selector = trigger.getAttribute('data-bs-target');
            if (!selector || selector.charAt(0) !== '#') {
                return;
            }

            const modalEl = document.querySelector(selector);
            if (!modalEl || !modalEl.classList.contains('modal')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    }

    const deleteCourseModal = document.getElementById('deleteCourseModal');
    if (deleteCourseModal) {
        deleteCourseModal.addEventListener('show.bs.modal', (event) => {
            const btn = event.relatedTarget;
            document.getElementById('delete_course_id').value = btn.getAttribute('data-id') || '';
            document.getElementById('delete_course_code').textContent = btn.getAttribute('data-code') || 'this course';
        });
    }
});
</script>
</div>
