<?php
declare(strict_types=1);
$error = $error ?? null;
$success = $success ?? null;
$mode = (string)($mode ?? 'encode');
$recommendations = $recommendations ?? [];
$groupedParts = $groupedParts ?? [];
$activeSemester = $activeSemester ?? null;
$isView = $mode === 'view';
$inputAttrs = $isView ? 'readonly disabled' : '';
$requiredAttr = $isView ? '' : 'required';
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <?php if ($isView): ?>
      <h4 class="fw-bold mb-1">Results Preview</h4>
      <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
    <?php else: ?>
      <h4 class="fw-bold mb-1">Encode Results</h4>
      <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
    <?php endif; ?>
  </div>
  <div class="page-actions">
    <?php if ($mode === 'edit'): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/storage">Back to List</a>
    <?php else: ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode">Back to List</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($activeSemester): ?>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-4">
    <div>
      <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Current Academic Year</h6>
      <div class="fw-semibold"><?= e((string)($activeSemester['school_year_name'] ?? '')) ?></div>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($isView): ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <h6 class="fw-bold mb-0">Top Course Recommendations</h6>
        <span class="badge text-bg-light border"><?= e((string)count($recommendations)) ?> item(s)</span>
      </div>
      <?php if (!empty($recommendations)): ?>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($recommendations as $index => $rec): ?>
            <div class="border rounded-3 px-3 py-2">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge text-bg-secondary"><?= e((string)($index + 1)) ?></span>
                  <div>
                    <div class="fw-semibold"><?= e((string)$rec['course_code']) ?></div>
                    <div class="text-muted small"><?= e((string)$rec['course_name']) ?></div>
                  </div>
                </div>
                <span class="badge badge-score-pill">
                  <?= e(number_format((float)$rec['total_score'], 2)) ?>%
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted small">No recommendations available yet.</div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($parts)): ?>
  <div class="alert alert-warning">No exam parts configured.</div>
<?php else: ?>
  <form method="post" action="<?= e(BASE_PATH) ?>/admission/encode/edit">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
    <input type="hidden" name="mode" value="<?= e($mode) ?>">

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
          <h6 class="fw-bold mb-0"><?= $isView ? 'Recorded Scores' : 'Enter Scores by Exam Part' ?></h6>
        </div>
        <div class="row g-3">
          <?php foreach (($groupedParts ?: [['category_name' => 'Exam Parts', 'parts' => $parts]]) as $group): ?>
            <div class="col-12 col-xl-6">
              <section class="encode-part-card">
                <header class="encode-part-card__header">
                  <h6 class="encode-part-card__title mb-0"><?= e((string)($group['category_name'] ?? 'Exam Parts')) ?></h6>
                </header>
                <div class="encode-part-card__body">
                  <div class="row g-3">
                    <?php foreach (($group['parts'] ?? []) as $part): ?>
                      <?php
                        $partId = (int)$part['id'];
                        $maxScore = (float)$part['max_score'];
                        $value = $scoresMap[$partId] ?? '';
                      ?>
                      <div class="col-12 col-md-6">
                        <label class="encode-score-label" for="score-<?= $partId ?>">
                          <span><?= e((string)$part['name']) ?></span>
                          <span class="encode-score-max">Max: <?= e(number_format($maxScore, 0)) ?></span>
                        </label>
                        <div class="encode-score-input-wrap">
                          <input
                            id="score-<?= $partId ?>"
                            class="form-control encode-score-input"
                            type="number"
                            name="scores[<?= $partId ?>]"
                            min="0"
                            max="<?= e((string)$maxScore) ?>"
                            step="0.01"
                            value="<?= e($value === '' ? '0.00' : number_format((float)$value, 2, '.', '')) ?>"
                            <?= $inputAttrs ?>
                            <?= $requiredAttr ?>
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

        <?php if (!$isView): ?>
          <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-primary" type="submit">Save Scores</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </form>
<?php endif; ?>
