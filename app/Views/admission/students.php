<?php
declare(strict_types=1);
$success = flash('success');
$error = flash('error');
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div>
    <h4 class="fw-bold mb-1">Students</h4>
    <p class="text-muted mb-0">View student details and admission status.</p>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/students">
  <div class="col-12 col-md-7">
    <label class="form-label small">Search</label>
    <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or ID number">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">Status</label>
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="admitted" <?= ($statusFilter ?? '') === 'admitted' ? 'selected' : '' ?>>Admitted</option>
      <option value="rejected" <?= ($statusFilter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="waitlisted" <?= ($statusFilter ?? '') === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
    </select>
  </div>
  <div class="col-12 col-md-2 d-grid">
    <button class="btn btn-outline-primary" type="submit">Filter</button>
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
              <div class="text-muted small">ID: <?= e((string)($s['id_number'] ?? 'Not set')) ?></div>
            </div>
            <span class="badge text-bg-light border text-uppercase"><?= e((string)($s['status'] ?? 'pending')) ?></span>
          </div>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit Student</a>
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
            <th>ID Number</th>
            <th>Email</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e((string)($s['id_number'] ?? 'Not set')) ?></td>
              <td><?= e($s['email']) ?></td>
              <td><span class="badge text-bg-light border text-uppercase"><?= e((string)($s['status'] ?? 'pending')) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No students found.</div>
  </div>
<?php endif; ?>
