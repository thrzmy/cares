<?php declare(strict_types=1); ?>
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
        <h5 class="fw-bold mb-1 auth-title">Reset Password</h5>
        <p class="text-muted mb-3">Enter a new password.</p>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/reset-password">
          <?= csrfField() ?>
          <input type="hidden" name="token" value="<?= e((string)$token) ?>">

          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input class="form-control" type="password" name="password" required minlength="8">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input class="form-control" type="password" name="confirm_password" required minlength="8">
          </div>

          <button class="btn btn-primary w-100">Update Password</button>
        </form>

        <div class="mt-3 small">
          <a href="<?= e(BASE_PATH) ?>/login">Back to Login</a>
        </div>
      </div>
    </div>
  </div>
</div>




