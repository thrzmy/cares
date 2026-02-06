<?php

declare(strict_types=1);
$success = flash('success');
?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h5 class="fw-bold mb-1">Create Account</h5>
        <p class="text-muted mb-0">Add a new administrator or admission account.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts">Back</a>
    </div>

    <hr>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e((string)$error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/create">
      <?= csrfField() ?>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input
            class="form-control"
            type="text"
            name="name"
            required
            value="<?= e((string)($old['name'] ?? '')) ?>"
            autocomplete="name">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input
            class="form-control"
            type="email"
            name="email"
            required
            value="<?= e((string)($old['email'] ?? '')) ?>"
            autocomplete="email">
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Role</label>
          <?php $role = (string)($old['role'] ?? 'admission'); ?>
          <select class="form-select" name="role" required>
            <option value="administrator" <?= $role === 'administrator' ? 'selected' : '' ?>>Administrator</option>
            <option value="admission" <?= $role === 'admission' ? 'selected' : '' ?>>Admission</option>
          </select>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Status</label>
          <?php $isActive = (int)($old['is_active'] ?? 1); ?>
          <select class="form-select" name="is_active" required>
            <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>Disabled</option>
          </select>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Create Account</button>
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/accounts">Cancel</a>
      </div>

      <p class="text-muted small mt-3 mb-0">
        New accounts are auto-verified, receive a random temporary password via email, and must reset on first login.
      </p>
    </form>
  </div>
</div>
