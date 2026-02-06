<?php
declare(strict_types=1);
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-1">Administrator Dashboard</h5>
    <p class="text-muted mb-3">Manage users, results, recommendations, logs, and reports.</p>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Account Management</h6>
          <p class="text-muted small mb-2">Manage System Users (Administrators & Admission Personnel).</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Student Management</h6>
          <p class="text-muted small mb-2">Manage student records.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/students">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Results & Recommendations</h6>
          <p class="text-muted small mb-2">View encoded scores and auto-generated course recommendations.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/scores">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Matrix Configuration</h6>
          <p class="text-muted small mb-2">Manage the course weight matrix for recommendations.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/matrix">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">System Reports</h6>
          <p class="text-muted small mb-2">Compile system usage, exam, and scan reports across departments or periods.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/reports">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Monitor Logs</h6>
          <p class="text-muted small mb-2">System activities.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/logs">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>
