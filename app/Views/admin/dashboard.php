<?php
declare(strict_types=1);
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-1">Admin Dashboard</h5>
    <p class="text-muted mb-3">Encode student scores and view recommendations.</p>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Encode Scores</h6>
          <p class="text-muted small mb-2">Manual input (fixed exam parts).</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admin/scores">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">View Results</h6>
          <p class="text-muted small mb-2">Ranked course recommendations.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admin/results">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>
