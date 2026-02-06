<?php

declare(strict_types=1);
$success = flash('success');
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-1">Welcome to <?= e(APP_NAME) ?></h4>
        <p class="text-muted mb-4">Sign in to continue.</p>
        
        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/login">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required autocomplete="username">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required autocomplete="current-password">
          </div>

          <button class="btn btn-primary w-100" type="submit">Login</button>
          <div class="mt-3 small d-flex justify-content-between">
            <a href="<?= e(BASE_PATH) ?>/forgot-password">Forgot password?</a>
          </div>

        </form>

        <div class="text-muted small mt-3">
          Admin: encode scores & view results<br>
          Guidance: edit weights matrix
        </div>
      </div>
    </div>
  </div>
</div>