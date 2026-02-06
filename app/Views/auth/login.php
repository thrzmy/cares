<?php

declare(strict_types=1);
$success = flash('success');
?>
<div class="row justify-content-center align-items-center auth-shell">
  <div class="col-12 col-md-6 col-lg-5">
    <div class="card shadow-sm auth-card">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <img class="auth-logo" src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="City College of Tagaytay logo">
          <div class="text-uppercase small text-muted">Academic Portal</div>
        </div>
        <h4 class="fw-bold mb-1 auth-title">Welcome to <?= e(APP_NAME) ?></h4>
        <div class="text-muted small mb-2">
          Course Admission and Recommendation System (CAReS)
        </div>
        <p class="text-muted mb-4">Sign in to continue.</p>
        
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/login">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required autocomplete="username">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="current-password">
          </div>

          <button class="btn btn-primary w-100" type="submit">Login</button>
          <div class="mt-3 small d-flex flex-column flex-sm-row gap-2">
            <a href="<?= e(BASE_PATH) ?>/forgot-password">Forgot password?</a>
            <a href="<?= e(BASE_PATH) ?>/register">Create account</a>
          </div>

        </form>

        <div class="text-muted small mt-3">
          Web-Based Admission Test Assessment with Intelligent Course Recommendation
        </div>
      </div>
    </div>
  </div>
</div>
