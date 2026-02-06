<?php
declare(strict_types=1);
?>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
          <div>
            <h5 class="fw-bold mb-1">My Profile</h5>
            <p class="text-muted mb-3">Update your account details.</p>
          </div>
          <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator">Back to Dashboard</a>
        </div>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(BASE_PATH) ?>/administrator/profile">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" type="text" name="name" required value="<?= e((string)($user['name'] ?? '')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required value="<?= e((string)($user['email'] ?? '')) ?>">
          </div>
          <button class="btn btn-primary" type="submit">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold mb-1">Change Password</h5>
        <p class="text-muted mb-3">Use a strong password and keep it secure.</p>

        <form method="post" action="<?= e(BASE_PATH) ?>/administrator/profile/password">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input class="form-control" type="password" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input class="form-control" type="password" name="new_password" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input class="form-control" type="password" name="confirm_password" required minlength="8">
          </div>
          <button class="btn btn-outline-primary" type="submit">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>
