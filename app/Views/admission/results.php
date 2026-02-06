<?php
declare(strict_types=1);
$success = $success ?? null;
$error = $error ?? null;
$recommendations = $recommendations ?? [];
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Results & Recommendations</h4>
    <p class="page-subtitle">Review encoded exam scores and course recommendations.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission">Back to Dashboard</a>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/results">
  <div class="col-12 col-md-7">
    <label class="form-label small">Search Students</label>
    <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or ID number">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">Admission Status</label>
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="admitted" <?= ($statusFilter ?? '') === 'admitted' ? 'selected' : '' ?>>Admitted</option>
      <option value="rejected" <?= ($statusFilter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="waitlisted" <?= ($statusFilter ?? '') === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
    </select>
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-primary" type="submit">Apply Filters</button>
  </div>
</form>

<?php if (!empty($students)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($students as $s): ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
            </div>
            <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>">
              <?= e(ucfirst((string)($s['status'] ?? 'pending'))) ?>
            </span>
          </div>
          <?php $recs = $recommendations[(int)$s['id']] ?? []; ?>
          <?php if (!empty($recs)): ?>
            <div class="mt-3">
              <div class="text-muted small mb-1">Top Course Recommendations</div>
              <?php foreach ($recs as $rec): ?>
                <div class="d-flex justify-content-between small">
                  <span><?= e($rec['course_code']) ?></span>
                  <span class="text-muted"><?= e(number_format((float)$rec['total_score'], 2)) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-muted small mt-2">No recommendations available yet.</div>
          <?php endif; ?>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/results/view?id=<?= (int)$s['id'] ?>">View Exam Scores</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Top Course Recommendations</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e($s['email']) ?></td>
              <td>
                <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>">
                  <?= e(ucfirst((string)($s['status'] ?? 'pending'))) ?>
                </span>
              </td>
              <td>
                <?php $recs = $recommendations[(int)$s['id']] ?? []; ?>
                <?php if (!empty($recs)): ?>
                  <?php foreach ($recs as $rec): ?>
                    <div class="d-flex justify-content-between small">
                      <span><?= e($rec['course_code']) ?></span>
                      <span class="text-muted"><?= e(number_format((float)$rec['total_score'], 2)) ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted small">No recommendations</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results/view?id=<?= (int)$s['id'] ?>">View Exam Scores</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No matching students found.</div>
  </div>
<?php endif; ?>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../partials/pagination.php';
?>
