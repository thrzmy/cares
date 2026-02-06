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
      <p class="page-subtitle">Scores are recorded. Review the top course recommendations.</p>
    <?php else: ?>
      <h4 class="fw-bold mb-1">Encode Test Results</h4>
      <p class="page-subtitle">Record scores per exam part for this student.</p>
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

<div class="card shadow-sm content-card mb-3">
  <div class="card-body">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
      <div>
        <div class="fw-semibold"><?= e($student['name']) ?></div>
        <div class="text-muted small"><?= e($student['email']) ?></div>
      </div>
      <div class="text-muted small">
        ID Number: <?= e((string)($student['id_number'] ?? 'Not set')) ?>
      </div>
    </div>
  </div>
</div>

<?php if ($isView): ?>
  <div class="card shadow-sm content-card mb-3">
    <div class="card-body">
      <h6 class="fw-bold mb-2">Top Course Recommendations</h6>
      <?php if (!empty($recommendations)): ?>
        <?php foreach ($recommendations as $rec): ?>
          <div class="d-flex justify-content-between small">
            <span><?= e((string)$rec['course_code']) ?> - <?= e((string)$rec['course_name']) ?></span>
            <span class="text-muted"><?= e(number_format((float)$rec['total_score'], 2)) ?></span>
          </div>
        <?php endforeach; ?>
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

    <div class="card shadow-sm content-card">
      <div class="card-body">
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
