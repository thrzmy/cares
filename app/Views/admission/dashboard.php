<?php
declare(strict_types=1);
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-1">Admission Dashboard</h5>
    <p class="text-muted mb-3">Manage student records, result storage, and course recommendations.</p>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Student Management</h6>
          <p class="text-muted small mb-2">View student details and admission status.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Encode Test Results</h6>
          <p class="text-muted small mb-2">Encode scores for pending students without records.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Result Storage</h6>
          <p class="text-muted small mb-2">View and edit students with recorded scores.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/storage">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Course Recommendation</h6>
          <p class="text-muted small mb-2">Review ranked recommendations based on test results.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>
