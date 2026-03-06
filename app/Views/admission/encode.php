<?php
declare(strict_types=1);
$success = $success ?? null;
$error = $error ?? null;
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Encode Test Results</h4>
    <p class="page-subtitle">Encode scores for pending students without records.</p>
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

<form class="row g-2 align-items-end mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/encode">
  <div class="col-12 col-lg-7">
    <label class="form-label small">Search Students</label>
    <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name or email">
  </div>
  <div class="col-6 col-sm-4 col-md-3 col-lg-2 d-grid">
    <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/encode">Clear Filters</a>
  </div>
  <div class="col-6 col-sm-4 col-md-3 col-lg-2 d-grid">
    <button class="btn btn-primary" type="submit">Search</button>
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
          </div>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/encode/edit?id=<?= (int)$s['id'] ?>">Input Scores</a>
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
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e($s['email']) ?></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode/edit?id=<?= (int)$s['id'] ?>">Input Scores</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No pending students without scores found.</div>
  </div>
<?php endif; ?>
