<?php
declare(strict_types=1);
$parts = $parts ?? [];
$groupedParts = $groupedParts ?? [];
$scoresMap = $scoresMap ?? [];
$success = $success ?? null;
$error = $error ?? null;
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
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Result Summary</h4>
    <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/scores">Back to Results & Recommendation</a>
  </div>
</div>
<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e((string)$success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e((string)$error) ?></div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/result_summary_content.php'; ?>

<?php if (!empty($parts)): ?>
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <div>
          <h6 class="fw-bold mb-1">Exam Scores</h6>
          <p class="text-muted small mb-0">Recorded scores grouped by exam section.</p>
        </div>
      </div>
      <div class="row g-3">
        <?php foreach (($groupedParts ?: [['category_name' => 'Exam Parts', 'parts' => $parts]]) as $group): ?>
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
                      $value = $scoresMap[$partId] ?? '';
                      $displayValue = $value === '' ? '0' : number_format((float)$value, 0, '.', '');
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
                          value="<?= e($displayValue) ?>"
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
    </div>
  </div>
<?php endif; ?>
