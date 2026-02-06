<?php
declare(strict_types=1);
?>
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-1">Administrator Dashboard</h5>
    <p class="text-muted mb-3">Manage users, matrix settings, results, logs, and reports.</p>

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
          <h6 class="fw-bold mb-1">Matrix Management</h6>
          <p class="text-muted small mb-2">Edit course x exam part weights.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/matrix">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Encode and View Results</h6>
          <p class="text-muted small mb-2">Manual input (fixed exam parts).</p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/scores">Encode</a>
            <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/results">View Results</a>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">Monitor Logs</h6>
          <p class="text-muted small mb-2">Audit system activity.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/logs">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">System Reports</h6>
          <p class="text-muted small mb-2">Generate printable summaries.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/reports">Open</a>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="border rounded p-3 bg-white">
          <h6 class="fw-bold mb-1">My Profile</h6>
          <p class="text-muted small mb-2">Update account details and password.</p>
          <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/profile">Open</a>
        </div>
      </div>
    </div>
  </div>
</div>
