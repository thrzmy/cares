<?php

declare(strict_types=1);
$success = flash('success');
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h5 class="fw-bold mb-1">Edit Account</h5>
        <p class="text-muted mb-0">Update account details and access.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admin/accounts">Back to list</a>
    </div>

    <hr>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e((string)$error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_PATH) ?>/admin/accounts/edit">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= e((string)($user['id'] ?? '')) ?>">

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input
            class="form-control"
            type="text"
            name="name"
            required
            value="<?= e((string)($user['name'] ?? '')) ?>"
            autocomplete="name">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input
            class="form-control"
            type="email"
            name="email"
            required
            value="<?= e((string)($user['email'] ?? '')) ?>"
            autocomplete="email">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Role</label>
          <?php $role = (string)($user['role'] ?? 'guidance'); ?>
          <select class="form-select" name="role" required>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="guidance" <?= $role === 'guidance' ? 'selected' : '' ?>>Guidance</option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Status</label>
          <?php $isActive = (int)($user['is_active'] ?? 1); ?>
          <select class="form-select" name="is_active" required>
            <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>Disabled</option>
          </select>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admin/accounts">Cancel</a>
      </div>
    </form>

    <hr>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <h6 class="fw-bold mb-1">Security</h6>
        <p class="text-muted small mb-0">Reset the account password and force a change on next login.</p>
      </div>
      <form method="post" action="<?= e(BASE_PATH) ?>/admin/accounts/reset-password">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= e((string)($user['id'] ?? '')) ?>">
        <button class="btn btn-outline-danger" type="submit">Reset Password</button>
      </form>
    </div>
  </div>
</div>
