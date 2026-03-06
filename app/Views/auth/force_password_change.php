<?php

declare(strict_types=1);
$success = flash('success');
?>
<div class="row justify-content-center align-items-center auth-shell">
  <div class="col-12 col-md-6 col-lg-5">
    <div class="card shadow-sm auth-card">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-2 auth-brand">
          <div class="d-flex align-items-center gap-2">
            <img class="auth-logo" src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="City College of Tagaytay logo">
            <div class="text-uppercase small text-muted">Academic Portal</div>
          </div>
          <img class="auth-logo auth-logo--sub" src="<?= e(BASE_PATH) ?>/assets/img/scs_logo.png" alt="School of Computer Science logo">
        </div>
        <h4 class="fw-bold mb-1 auth-title">Change Your Password</h4>
        <p class="text-muted mb-4">You must update your temporary password before continuing.</p>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/force-password-change">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="new-password">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input class="form-control" type="password" name="confirm_password" required autocomplete="new-password">
          </div>

          <button class="btn btn-primary w-100" type="submit">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>




