<?php
declare(strict_types=1);

$student = $student ?? [];
$courseSummaries = $courseSummaries ?? [];
$groupedParts = $groupedParts ?? [];
$sortedParts = $sortedParts ?? [];
$scoresMap = $scoresMap ?? [];

$courseGroupOrder = [
    'BS Secondary Education',
    'School of Business and Management',
    'School of Hospitality and Tourism Management',
    'School of Computer Studies',
    'School of Arts and Sciences',
];

$courseCategoryMap = [
    'BSED-ENG' => 'BS Secondary Education',
    'BSED-FIL' => 'BS Secondary Education',
    'BSED-MATH' => 'BS Secondary Education',
    'BSED-SS' => 'BS Secondary Education',
    'BSED-SCI' => 'BS Secondary Education',
    'BSHRDM' => 'School of Business and Management',
    'BSMM' => 'School of Business and Management',
    'BSOA' => 'School of Business and Management',
    'BSTM' => 'School of Hospitality and Tourism Management',
    'BSHM' => 'School of Hospitality and Tourism Management',
    'BSIT' => 'School of Computer Studies',
    'BSCS' => 'School of Computer Studies',
    'ABPSY' => 'School of Arts and Sciences',
];

$courseDisplayOrder = [
    'BSED-ENG' => 10,
    'BSED-FIL' => 20,
    'BSED-MATH' => 30,
    'BSED-SS' => 40,
    'BSED-SCI' => 50,
    'BSHRDM' => 60,
    'BSMM' => 70,
    'BSOA' => 80,
    'BSTM' => 90,
    'BSHM' => 100,
    'BSIT' => 110,
    'BSCS' => 120,
    'ABPSY' => 130,
];

$categoryToneMap = [
    'BS Secondary Education' => 'tone-education',
    'School of Business and Management' => 'tone-business',
    'School of Hospitality and Tourism Management' => 'tone-hospitality',
    'School of Computer Studies' => 'tone-computing',
    'School of Arts and Sciences' => 'tone-arts',
    'Other Programs' => 'tone-other',
];

usort($courseSummaries, static function (array $a, array $b) use ($courseDisplayOrder): int {
    $aOrder = $courseDisplayOrder[(string)($a['course_code'] ?? '')] ?? 9999;
    $bOrder = $courseDisplayOrder[(string)($b['course_code'] ?? '')] ?? 9999;
    if ($aOrder === $bOrder) {
        return strcmp((string)($a['course_code'] ?? ''), (string)($b['course_code'] ?? ''));
    }
    return $aOrder <=> $bOrder;
});

$groupedCourseSummaries = [];
foreach ($courseGroupOrder as $categoryName) {
    $groupedCourseSummaries[$categoryName] = [];
}
foreach ($courseSummaries as $courseSummary) {
    $categoryName = $courseCategoryMap[(string)($courseSummary['course_code'] ?? '')] ?? 'Other Programs';
    if (!isset($groupedCourseSummaries[$categoryName])) {
        $groupedCourseSummaries[$categoryName] = [];
    }
    $groupedCourseSummaries[$categoryName][] = $courseSummary;
}

$qualifiedPrograms = array_values(array_filter($courseSummaries, static fn(array $course): bool => !empty($course['qualified'])));
$choiceQualifiedPrograms = array_values(array_filter(
    $qualifiedPrograms,
    static fn(array $course): bool => !empty($course['is_first_choice']) || !empty($course['is_second_choice'])
));

$examStatus = (string)($student['status'] ?? 'pending');
$screeningStatus = $examStatus === 'failed'
    ? 'not_qualified'
    : (!empty($qualifiedPrograms) ? 'qualified' : ($examStatus === 'passed' ? 'not_qualified' : 'pending'));

$recommendationMessage = 'No qualified program recommendation yet.';
if (!empty($choiceQualifiedPrograms)) {
    $recommendationMessage = 'Qualified in selected choice program(s).';
} elseif (!empty($qualifiedPrograms)) {
    $recommendationMessage = 'Qualified in other program(s), but not in the selected choices.';
}

$profile = !empty($courseSummaries[0]['part2b_profile']) ? (string)$courseSummaries[0]['part2b_profile'] : 'Not available';
$screeningBadgeClass = $screeningStatus === 'qualified'
    ? 'text-bg-success'
    : ($screeningStatus === 'not_qualified' ? 'text-bg-danger' : 'text-bg-warning');
?>

<div class="result-summary-card">
  <div class="result-summary-card__body">
    <section class="summary-topline">
      <div class="summary-topline__grid">
        <div><span class="summary-topline__label">Application No.</span><span class="summary-topline__value"><?= e((string)($student['application_number'] ?? 'Not provided')) ?></span></div>
        <div><span class="summary-topline__label">Name</span><span class="summary-topline__value"><?= e((string)($student['name'] ?? '')) ?: 'Not provided' ?></span></div>
        <div><span class="summary-topline__label">Email</span><span class="summary-topline__value"><?= e((string)($student['email'] ?? '')) ?: 'Not provided' ?></span></div>
        <div><span class="summary-topline__label">1st Choice</span><span class="summary-topline__value"><?= e((string)($student['first_choice_label'] ?? 'Not selected')) ?></span></div>
        <div><span class="summary-topline__label">2nd Choice</span><span class="summary-topline__value"><?= e((string)($student['second_choice_label'] ?? 'Not selected')) ?></span></div>
        <div><span class="summary-topline__label">Qualified Programs</span><span class="summary-topline__value"><?= e((string)count($qualifiedPrograms)) ?></span></div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-3">
        <span class="badge <?= e(studentStatusBadgeClass((string)($student['status'] ?? 'pending'))) ?>">
          <?= e(studentStatusLabel((string)($student['status'] ?? 'pending'))) ?>
        </span>
        <span class="badge <?= e($screeningBadgeClass) ?>">
          <?= e(studentScreeningStatusLabel($screeningStatus)) ?>
        </span>
        <span class="badge text-bg-light border text-dark">
          <?= e(studentApplicationStatusLabel((string)($student['application_status'] ?? 'new_student'))) ?>
        </span>
      </div>
      <div class="summary-topline__note"><?= e($recommendationMessage) ?></div>
    </section>

    <section class="result-summary-section mt-3">
      <div class="summary-section-head">
        <div>
          <h6 class="summary-section-head__title">Course Evaluation</h6>
          <p class="summary-section-head__subtitle mb-0">All programs are shown using the same matrix breakdown used in the configuration page.</p>
        </div>
      </div>

      <?php if (!empty($courseSummaries)): ?>
        <div class="matrix-simple-shell summary-matrix-shell">
          <div class="matrix-mini-legend pb-2">
            <span class="matrix-mini-legend-item"><strong>A</strong> Achievement</span>
            <span class="matrix-mini-legend-item"><strong>B</strong> Aptitude</span>
            <span class="matrix-mini-legend-item"><strong>C</strong> Personality</span>
            <span class="matrix-mini-legend-item"><strong>D</strong> CCT as a Choice</span>
            <span class="matrix-mini-legend-item"><strong>E</strong> Degree Program</span>
            <span class="matrix-mini-legend-item"><strong>O</strong> Others</span>
          </div>
          <div class="table-responsive matrix-sticky-table-wrap summary-table-wrap">
          <table class="table matrix-simple-table matrix-grouped-table summary-matrix-table mb-0">
            <thead>
              <tr>
                <th>Program</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>E</th>
                <th>O</th>
                <th>Matrix</th>
                <th>Total %</th>
                <th>Result</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($groupedCourseSummaries as $categoryName => $coursesInCategory): ?>
                <?php if (empty($coursesInCategory)) continue; ?>
                <tr class="matrix-group-row">
                  <td class="matrix-group-banner-cell <?= e($categoryToneMap[$categoryName] ?? 'tone-other') ?>">
                    <div class="matrix-group-banner-text"><?= e($categoryName) ?></div>
                  </td>
                  <td colspan="3" class="matrix-group-meta-cell"><?= e('80%') ?></td>
                  <td class="matrix-group-meta-cell"><?= e('5%') ?></td>
                  <td class="matrix-group-meta-cell"><?= e('5%') ?></td>
                  <td class="matrix-group-meta-cell"><?= e('10%') ?></td>
                  <td class="matrix-group-meta-cell"></td>
                  <td class="matrix-group-meta-cell"></td>
                  <td class="matrix-group-meta-cell"></td>
                </tr>
                <?php foreach ($coursesInCategory as $course): ?>
                  <?php
                  $isQualified = !empty($course['qualified']);
                  $matrixText = number_format((float)($course['achieved_score'] ?? 0), 0) . ' / ' . number_format((float)($course['required_score'] ?? 0), 0);
                  $setBFit = !empty($course['meets_riasec']) ? 'Match' : 'No match';
                  $otherShort = [];
                  if (!empty($course['meets_strand'])) {
                      $otherShort[] = 'Strand';
                  }
                  if (!empty($course['meets_gpa'])) {
                      $otherShort[] = 'GPA';
                  }
                  if (!empty($course['meets_physical'])) {
                      $otherShort[] = 'Physical';
                  }
                  ?>
                  <tr>
                    <td>
                      <div class="summary-course-title"><?= e((string)$course['course_name']) ?></div>
                      <div class="summary-course-meta">
                        <?= e((string)$course['course_code']) ?>
                        <?php if (!empty($course['is_first_choice'])): ?>
                          <span class="badge text-bg-primary ms-1">1st Choice</span>
                        <?php elseif (!empty($course['is_second_choice'])): ?>
                          <span class="badge text-bg-secondary ms-1">2nd Choice</span>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td><span class="matrix-cell-pill"><?= e(number_format((float)($course['achievement_score'] ?? 0), 0)) ?></span></td>
                    <td><span class="matrix-cell-pill"><?= e(number_format((float)($course['aptitude_set_a_score'] ?? 0), 0)) ?></span></td>
                    <td><span class="matrix-cell-pill"><?= e(number_format((float)($course['personality_score'] ?? 0), 0)) ?></span></td>
                    <td><span class="matrix-cell-copy"><?= !empty($course['meets_cct_choice']) ? 'Matched' : 'Not met' ?></span></td>
                    <td><span class="matrix-cell-copy"><?= !empty($course['meets_choice']) ? 'Matched' : 'Not met' ?></span></td>
                    <td><span class="matrix-cell-copy"><?= e(!empty($otherShort) ? implode(', ', $otherShort) : 'None') ?></span></td>
                    <td>
                      <div class="summary-score-main"><?= e($matrixText) ?></div>
                      <div class="summary-score-sub">
                        <?= e(number_format((float)($course['matrix_percent'] ?? 0), 2)) ?>%
                        &middot;
                        <?= e($setBFit) ?>
                      </div>
                    </td>
                    <td>
                      <div class="summary-score-main"><?= e(number_format((float)($course['total_score'] ?? 0), 2)) ?>%</div>
                      <div class="summary-score-sub">
                        Core <?= e(number_format((float)($course['core_percentage'] ?? 0), 0)) ?>/80
                      </div>
                    </td>
                    <td>
                      <span class="badge <?= $isQualified ? 'text-bg-success' : 'text-bg-danger' ?>">
                        <?= $isQualified ? 'Qualified' : 'Not Qualified' ?>
                      </span>
                      <div class="summary-score-sub mt-1"><?= e((string)($course['result_label'] ?? 'Failed')) ?></div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>
      <?php else: ?>
        <div class="text-muted">No course recommendations available yet.</div>
      <?php endif; ?>
    </section>

    <section class="result-summary-section mt-3">
      <div class="summary-section-head">
        <div>
          <h6 class="summary-section-head__title">Exam Part Scores</h6>
          <p class="summary-section-head__subtitle mb-0">Recorded scores are grouped by exam section for easier checking.</p>
        </div>
      </div>

      <?php if (!empty($sortedParts)): ?>
        <div class="row g-3">
          <?php foreach (($groupedParts ?: [['category_name' => 'Exam Parts', 'parts' => $sortedParts]]) as $group): ?>
            <div class="col-12 col-xl-6">
              <section class="encode-part-card h-100">
                <header class="encode-part-card__header">
                  <h6 class="encode-part-card__title mb-0"><?= e((string)($group['category_name'] ?? 'Exam Parts')) ?></h6>
                </header>
                <div class="encode-part-card__body">
                  <div class="row g-3">
                    <?php foreach (($group['parts'] ?? []) as $part): ?>
                      <?php
                      $partId = (int)$part['id'];
                      $maxScore = (float)$part['max_score'];
                      $value = $scoresMap[$partId] ?? null;
                      ?>
                      <div class="col-12 col-md-6">
                        <label class="encode-score-label">
                          <span><?= e((string)$part['name']) ?></span>
                          <span class="encode-score-max">Max: <?= e(number_format($maxScore, 0)) ?></span>
                        </label>
                        <div class="encode-score-input-wrap">
                          <input
                            class="form-control encode-score-input"
                            type="text"
                            value="<?= e($value === null ? '0' : number_format((float)$value, 0, '.', '')) ?>"
                            readonly
                            disabled
                          >
                          <span class="encode-score-suffix">pts</span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted">No exam parts configured yet.</div>
      <?php endif; ?>
    </section>
  </div>
</div>
