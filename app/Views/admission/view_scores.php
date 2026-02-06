<?php
declare(strict_types=1);
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Exam Scores</h4>
    <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results">Back to Results & Recommendations</a>
  </div>
</div>

<div class="card shadow-sm content-card">
  <div class="card-body">
    <?php if (!empty($parts)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Exam Part</th>
              <th class="text-end">Score Earned</th>
              <th class="text-end">Maximum</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($parts as $part): ?>
              <?php
                $partId = (int)$part['id'];
                $maxScore = (float)$part['max_score'];
                $value = $scoresMap[$partId] ?? null;
              ?>
              <tr>
                <td class="fw-semibold"><?= e($part['name']) ?></td>
                <td class="text-end"><?= e($value === null ? '-' : number_format((float)$value, 2)) ?></td>
                <td class="text-end text-muted"><?= e(number_format($maxScore, 2)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted">No exam parts configured yet.</div>
    <?php endif; ?>
  </div>
</div>
