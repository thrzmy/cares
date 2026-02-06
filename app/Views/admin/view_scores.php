<?php
declare(strict_types=1);
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div>
    <h4 class="fw-bold mb-1">View Scores</h4>
    <p class="text-muted mb-0"><?= e($student['name']) ?> · <?= e($student['email']) ?></p>
  </div>
  <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/scores">Back to Results</a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <?php if (!empty($parts)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Exam Part</th>
              <th class="text-end">Score</th>
              <th class="text-end">Max</th>
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
                <td class="text-end"><?= e($value === null ? '—' : number_format((float)$value, 2)) ?></td>
                <td class="text-end text-muted"><?= e(number_format($maxScore, 2)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted">No exam parts configured.</div>
    <?php endif; ?>
  </div>
</div>
