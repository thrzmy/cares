<?php
declare(strict_types=1);
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Admission Dashboard</h4>
    <p class="page-subtitle">Manage student records, result storage, and course recommendations.</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Student Management</h6>
      <p class="text-muted small mb-2">View student details and admission status.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Encode Test Results</h6>
      <p class="text-muted small mb-2">Encode scores for pending students without records.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Result Storage</h6>
      <p class="text-muted small mb-2">View and edit students with recorded scores.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/storage">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Course Recommendation</h6>
      <p class="text-muted small mb-2">Review ranked recommendations based on test results.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results">Open</a>
    </div>
  </div>
</div>
