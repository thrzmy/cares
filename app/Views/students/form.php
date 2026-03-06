<?php
declare(strict_types=1);
$mode = (string)($mode ?? 'edit');
$student = $student ?? [];
?>
<div class="card shadow-sm">
  <div class="card-body">
      <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
      <div>
        <h5 class="fw-bold mb-1"><?= e((string)($title ?? 'Student')) ?></h5>
        <p class="text-muted mb-0">ID number is required only when status is set to admitted.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/<?= e(str_starts_with((string)$action, '/admission') ? 'admission' : 'administrator') ?>/students">Back</a>
    </div>

    <hr>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e((string)$error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_PATH) ?><?= e((string)$action) ?>">
      <?= csrfField() ?>
      <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="id" value="<?= e((string)($student['id'] ?? '')) ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input class="form-control" type="text" name="name" required value="<?= e((string)($student['name'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required value="<?= e((string)($student['email'] ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Status</label>
          <?php $status = (string)($student['status'] ?? 'pending'); ?>
          <select class="form-select" name="status" required>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="admitted" <?= $status === 'admitted' ? 'selected' : '' ?>>Admitted</option>
            <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="waitlisted" <?= $status === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
          </select>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">ID Number</label>
          <input class="form-control" type="text" name="id_number" value="<?= e((string)($student['id_number'] ?? '')) ?>" placeholder="Required if admitted">
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit"><?= $mode === 'create' ? 'Create Student' : 'Save Changes' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/<?= e(str_starts_with((string)$action, '/admission') ? 'admission' : 'administrator') ?>/students">Cancel</a>
      </div>
    </form>
  </div>
</div>
