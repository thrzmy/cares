<?php
declare(strict_types=1);
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-1">Guidance Dashboard</h5>
    <p class="text-muted mb-3">Update the access / weighted matrix used by the recommendation engine.</p>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Weights Matrix</h6>
          <p class="text-muted small mb-2">Edit course × exam part weights.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/guidance/weights">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>
