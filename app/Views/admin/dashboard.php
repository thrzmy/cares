<?php
declare(strict_types=1);
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Administrator Dashboard</h4>
    <p class="page-subtitle">Manage users, results, recommendations, logs, and reports.</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Account Management</h6>
      <p class="text-muted small mb-2">Manage System Users (Administrators & Admission Personnel).</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Student Management</h6>
      <p class="text-muted small mb-2">Manage student records.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/students">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Results & Recommendations</h6>
      <p class="text-muted small mb-2">View encoded scores and auto-generated course recommendations.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/scores">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Matrix Configuration</h6>
      <p class="text-muted small mb-2">Manage the course weight matrix for recommendations.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/matrix">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">System Reports</h6>
      <p class="text-muted small mb-2">Compile system usage, exam, and scan reports across departments or periods.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/reports">Open</a>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="admin-tile">
      <h6 class="fw-bold mb-1 tile-title">Monitor Logs</h6>
      <p class="text-muted small mb-2">System activities.</p>
      <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/logs">Open</a>
    </div>
  </div>
</div>
