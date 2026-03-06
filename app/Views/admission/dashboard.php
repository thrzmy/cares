<?php
declare(strict_types=1);
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Admission Dashboard</h4>
    <p class="page-subtitle">Manage students, results, recommendations, logs, and reports.</p>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-md-6">
    <a class="admin-tile admin-tile-link" href="<?= e(BASE_PATH) ?>/admission/students">
      <div class="d-flex align-items-start gap-3">
        <div class="tile-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 4 2 9l10 5 10-5-10-5Zm0 8.7L5.6 10 12 6.8 18.4 10 12 12.7Zm8 2.3v2l-8 4-8-4v-2l8 4 8-4Z"/>
          </svg>
        </div>
        <div>
          <h6 class="fw-bold mb-1 tile-title">Student Management</h6>
          <p class="text-muted small mb-2">Manage student records, IDs, and admission status.</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12 col-md-6">
    <a class="admin-tile admin-tile-link" href="<?= e(BASE_PATH) ?>/admission/encode">
      <div class="d-flex align-items-start gap-3">
        <div class="tile-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 4h4v16H4V4Zm6 6h4v10h-4V10Zm6-4h4v14h-4V6Z"/>
          </svg>
        </div>
        <div>
          <h6 class="fw-bold mb-1 tile-title">Encode Test Results</h6>
          <p class="text-muted small mb-2">Encode scores for pending students without records.</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12 col-md-6">
    <a class="admin-tile admin-tile-link" href="<?= e(BASE_PATH) ?>/admission/results">
      <div class="d-flex align-items-start gap-3">
        <div class="tile-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 4h4v16H4V4Zm6 6h4v10h-4V10Zm6-4h4v14h-4V6Z"/>
          </svg>
        </div>
        <div>
          <h6 class="fw-bold mb-1 tile-title">Results & Recommendations</h6>
          <p class="text-muted small mb-2">Review encoded exam scores and course recommendations.</p>
        </div>
      </div>
    </a>
  </div>
  
  <div class="col-12 col-md-6">
    <a class="admin-tile admin-tile-link" href="<?= e(BASE_PATH) ?>/admission/reports">
      <div class="d-flex align-items-start gap-3">
        <div class="tile-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm8 1.5V8h4.5L14 3.5ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/>
          </svg>
        </div>
        <div>
          <h6 class="fw-bold mb-1 tile-title">System Reports</h6>
          <p class="text-muted small mb-2">Generate student-only summaries by period.</p>
        </div>
      </div>
    </a>
  </div>

  <div class="col-12 col-md-6">
    <a class="admin-tile admin-tile-link" href="<?= e(BASE_PATH) ?>/admission/logs">
      <div class="d-flex align-items-start gap-3">
        <div class="tile-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 4h16v4H4V4Zm0 6h16v10H4V10Zm4 2v6h8v-6H8Z"/>
          </svg>
        </div>
        <div>
          <h6 class="fw-bold mb-1 tile-title">Activity Logs</h6>
          <p class="text-muted small mb-2">Track your actions and recent changes.</p>
        </div>
      </div>
    </a>
  </div>
</div>
