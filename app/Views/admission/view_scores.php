<?php
declare(strict_types=1);
$parts = $parts ?? [];
$scoresMap = $scoresMap ?? [];
$sortedParts = $parts;
usort($sortedParts, static function (array $a, array $b) use ($scoresMap): int {
    $aValue = $scoresMap[(int)$a['id']] ?? null;
    $bValue = $scoresMap[(int)$b['id']] ?? null;
    if ($aValue === null && $bValue === null) {
        return strcmp((string)$a['name'], (string)$b['name']);
    }
    if ($aValue === null) {
        return 1;
    }
    if ($bValue === null) {
        return -1;
    }
    return ((float)$bValue <=> (float)$aValue);
});
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Result Summary</h4>
    <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results">Back to Results & Recommendations</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <?php $courseSummaries = $courseSummaries ?? []; ?>
    <div class="d-flex flex-nowrap justify-content-between align-items-start gap-2 mb-2">
      <div class="me-2" style="min-width: 0;">
        <h6 class="fw-bold mb-0">Course Recommendation Summary</h6>
      </div>
      <span class="badge text-bg-light border flex-shrink-0"><?= e((string)count($courseSummaries)) ?> course(s)</span>
    </div>
    <?php if (!empty($courseSummaries)): ?>
      <?php $topCourse = $courseSummaries[0]; ?>
      <div class="rounded-3 p-3 mb-3" style="background: rgba(111,17,25,0.04); border: 1px solid rgba(111,17,25,0.14); border-left: 4px solid var(--cares-maroon);">
        <div class="small fw-semibold mb-1" style="color: var(--cares-maroon);">Top Course Recommendation</div>
        <div class="d-flex justify-content-between align-items-start gap-3">
          <div>
            <div class="fw-bold"><?= e((string)$topCourse['course_code']) ?></div>
            <div class="text-muted small"><?= e((string)$topCourse['course_name']) ?></div>
          </div>
          <div class="text-end">
            <div class="small text-muted">Overall %</div>
            <div class="h5 fw-bold mb-0"><?= e(number_format((float)$topCourse['total_score'], 2)) ?></div>
          </div>
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-7">
          <div class="border rounded-3 p-2 p-md-3 bg-white h-100">
            <div class="text-muted small mb-2">Course Recommendations (Highest First)</div>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($courseSummaries as $index => $course): ?>
                <?php $score = (float)$course['total_score']; ?>
                <div class="border rounded-3 px-3 py-2">
                  <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge text-bg-secondary"><?= e((string)($index + 1)) ?></span>
                      <div>
                        <div class="fw-semibold"><?= e((string)$course['course_code']) ?></div>
                      </div>
                    </div>
                    <span class="badge badge-score-pill">
                      <?= e(number_format($score, 2)) ?>%
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-5">
          <div class="border rounded-3 p-2 p-md-3 bg-white h-100">
            <div class="text-muted small mb-2">Exam Part Scores</div>
            <?php if (!empty($sortedParts)): ?>
              <div class="d-flex flex-column gap-2">
                <?php foreach ($sortedParts as $part): ?>
                  <?php
                    $partId = (int)$part['id'];
                    $maxScore = (float)$part['max_score'];
                    $value = $scoresMap[$partId] ?? null;
                  ?>
                  <div class="border rounded-3 px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <div class="fw-semibold"><?= e($part['name']) ?></div>
                        <div class="text-muted small">Max: <?= e(number_format($maxScore, 2)) ?></div>
                      </div>
                      <div class="text-end">
                        <div class="small text-muted">Score</div>
                        <div class="fw-semibold"><?= e($value === null ? '-' : number_format((float)$value, 2)) ?></div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-muted">No exam parts configured yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="text-muted mb-3">No course recommendations available yet.</div>
    <?php endif; ?>
  </div>
</div>
