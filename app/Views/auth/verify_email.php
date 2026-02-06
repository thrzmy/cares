<?php

declare(strict_types=1);
$success = flash('success');
?>
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-1">Verify Your Email</h4>
        <p class="text-muted mb-4">Enter the code sent to your email.</p>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/verify-email">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input
              class="form-control"
              type="email"
              name="email"
              required
              value="<?= e((string)($email ?? '')) ?>"
              autocomplete="email">
          </div>

          <div class="mb-3">
            <label class="form-label">Verification Code</label>
            <input
              class="form-control"
              type="text"
              name="code"
              required
              inputmode="numeric"
              autocomplete="one-time-code">
          </div>

          <button class="btn btn-primary w-100" type="submit">Verify Email</button>
          <button class="btn btn-outline-secondary w-100 mt-2" type="submit" form="resend-form">Resend Code</button>
          <div class="mt-3 small">
            <a href="<?= e(BASE_PATH) ?>/login">Back to login</a>
          </div>
        </form>

        <form id="resend-form" method="post" action="<?= e(BASE_PATH) ?>/verify-email/resend">
          <?= csrfField() ?>
          <input type="hidden" name="email" value="<?= e((string)($email ?? '')) ?>">
        </form>
      </div>
    </div>
  </div>
</div>
