<?php

declare(strict_types=1);
$success = flash('success');
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h5 class="fw-bold mb-1">Edit Account</h5>
    <p class="page-subtitle">Update account details and access.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts">Back</a>
  </div>
</div>

<div class="card shadow-sm content-card">
  <div class="card-body">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e((string)$error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/edit">
      <?= csrfField() ?>
      <input type="hidden" name="id" value="<?= e((string)($user['id'] ?? '')) ?>">

      <div class="row g-3">
        <div class="col-12">
          <?php $status = (string)($user['account_status'] ?? 'verified'); ?>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge <?= $status === 'verified' ? 'text-bg-success' : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') ?>">
              <?= e(ucfirst($status)) ?>
            </span>
            <?php if ($status === 'rejected' && !empty($user['rejection_reason'])): ?>
              <span class="text-muted small">Reason: <?= e((string)$user['rejection_reason']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($status === 'verified'): ?>
            <div class="mt-2 text-muted small">
              <div>Verified by: <?= e((string)($user['verified_by_name'] ?? '-')) ?></div>
              <div>Verified at: <?= e(!empty($user['verified_at']) ? date('M j, Y H:i', strtotime((string)$user['verified_at'])) : '-') ?></div>
            </div>
          <?php elseif ($status === 'rejected'): ?>
            <div class="mt-2 text-muted small">
              <div>Rejected by: <?= e((string)($user['rejected_by_name'] ?? '-')) ?></div>
              <div>Rejected at: <?= e(!empty($user['rejected_at']) ? date('M j, Y H:i', strtotime((string)$user['rejected_at'])) : '-') ?></div>
            </div>
          <?php endif; ?>
        </div>
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
          <?php $role = (string)($user['role'] ?? 'admission'); ?>
          <select class="form-select" name="role" required>
            <option value="administrator" <?= $role === 'administrator' ? 'selected' : '' ?>>Administrator</option>
            <option value="admission" <?= $role === 'admission' ? 'selected' : '' ?>>Admission</option>
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
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/accounts">Cancel</a>
      </div>
    </form>

    <hr>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <h6 class="fw-bold mb-1">Security</h6>
        <p class="text-muted small mb-0">Reset the account password and force a change on next login.</p>
      </div>
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/reset-password">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= e((string)($user['id'] ?? '')) ?>">
        <button class="btn btn-outline-danger" type="submit">Reset Password</button>
      </form>
    </div>
  </div>
</div>
