<?php

declare(strict_types=1);
$success = flash('success');
?>
<div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 2rem);">
  <div class="col-12 col-md-7 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-1">Create Your Account</h4>
        <p class="text-muted mb-4">Register to request access. Administrator accounts are provisioned by system admins.</p>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/register">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input
              class="form-control"
              type="text"
              name="name"
              required
              value="<?= e((string)($old['name'] ?? '')) ?>"
              autocomplete="name">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input
              class="form-control"
              type="email"
              name="email"
              required
              value="<?= e((string)($old['email'] ?? '')) ?>"
              autocomplete="email">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="new-password">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input class="form-control" type="password" name="confirm_password" required autocomplete="new-password">
          </div>

          <button class="btn btn-primary w-100" type="submit">Register</button>
          <div class="mt-3 small">
            <a href="<?= e(BASE_PATH) ?>/login">Back to login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
