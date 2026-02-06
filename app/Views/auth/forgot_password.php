<?php

declare(strict_types=1); ?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Forgot Password</h5>
                <p class="text-muted mb-3">We'll send a reset link to your email.</p>

                <?php $success = flash('success'); ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= e((string)$error) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(BASE_PATH) ?>/forgot-password">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <button class="btn btn-primary w-100">Send Reset Link</button>
                </form>

                <div class="mt-3 small">
                    <a href="<?= e(BASE_PATH) ?>/login">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
