<?php
declare(strict_types=1);
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h5 class="fw-bold mb-1">My Profile</h5>
    <p class="page-subtitle">Manage your account details and security settings.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator">Back to Dashboard</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm content-card">
      <div class="card-body">
        <div>
          <h5 class="fw-bold mb-1">Account Details</h5>
          <p class="text-muted mb-3">Update your name and email address.</p>
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
          <button class="btn btn-primary" type="submit">Save Profile</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card shadow-sm content-card">
      <div class="card-body">
        <h5 class="fw-bold mb-1">Change Password</h5>
        <p class="text-muted mb-3">Use a strong password (at least 8 characters).</p>

        <form method="post" action="<?= e(BASE_PATH) ?>/administrator/profile/password">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input class="form-control" type="password" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password (min 8 characters)</label>
            <input class="form-control" type="password" name="new_password" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input class="form-control" type="password" name="confirm_password" required minlength="8">
          </div>
          <button class="btn btn-outline-primary" type="submit">Save New Password</button>
        </form>
      </div>
    </div>
  </div>
</div>
