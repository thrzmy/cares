<?php
declare(strict_types=1);
$error = $error ?? null;
$success = $success ?? null;
$mode = (string)($mode ?? 'encode');
$recommendations = $recommendations ?? [];
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
      <h4 class="fw-bold mb-1">Encode Test Results</h4>
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
          <?php foreach ($parts as $part): ?>
            <?php
              $partId = (int)$part['id'];
              $maxScore = (float)$part['max_score'];
              $value = $scoresMap[$partId] ?? '';
            ?>
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold"><?= e($part['name']) ?></label>
              <div class="input-group">
                <input
                  class="form-control"
                  type="number"
                  name="scores[<?= $partId ?>]"
                  min="0"
                  max="<?= e((string)$maxScore) ?>"
                  step="0.01"
                  value="<?= e((string)$value) ?>"
                  <?= $inputAttrs ?>
                  <?= $requiredAttr ?>
                >
                <span class="input-group-text">Max <?= e((string)$maxScore) ?></span>
              </div>
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
