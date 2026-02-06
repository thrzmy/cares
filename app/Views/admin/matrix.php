<?php

declare(strict_types=1);
$success = flash('success');
?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <h5 class="fw-bold mb-1">Matrix Management</h5>
                <p class="text-muted mb-0">Edit course x exam part weights. (0-100)</p>
            </div>
            <div class="text-muted small">
                Tip: blank cells won't overwrite existing values.
            </div>
        </div>

        <hr>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e((string)$error) ?></div>
        <?php endif; ?>

        <?php if (empty($courses) || empty($parts)): ?>
            <div class="alert alert-warning mb-0">
                Missing data. Make sure you have courses and exam parts seeded.
            </div>
        <?php else: ?>
            <div class="d-block d-md-none">
                <form method="post" action="<?= e(BASE_PATH) ?>/administrator/matrix">
                    <?= csrfField() ?>
                    <?php foreach ($courses as $c): ?>
                        <?php $courseId = (int)$c['id']; ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="fw-bold"><?= e($c['course_code']) ?></div>
                                <div class="text-muted small mb-3"><?= e($c['course_name']) ?></div>

                                <?php foreach ($parts as $p): ?>
                                    <?php
                                    $partId = (int)$p['id'];
                                    $val = $weightsMap[$courseId][$partId] ?? '';
                                    ?>
                                    <div class="mb-2">
                                        <label class="form-label small mb-1">
                                            <?= e($p['name']) ?> <span class="text-muted">(Max <?= e((string)$p['max_score']) ?>)</span>
                                        </label>
                                        <input
                                            class="form-control"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            name="weights[<?= e((string)$courseId) ?>][<?= e((string)$partId) ?>]"
                                            value="<?= e($val === '' ? '' : (string)$val) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button class="btn btn-primary w-100">Save Matrix</button>
                </form>
            </div>
            <div class="d-none d-md-block">
                <form method="post" action="<?= e(BASE_PATH) ?>/administrator/matrix">
                    <?= csrfField() ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:220px;">Course</th>
                                    <?php foreach ($parts as $p): ?>
                                        <th class="text-center" style="min-width:160px;">
                                            <?= e($p['name']) ?><br>
                                            <span class="text-muted small">Max: <?= e((string)$p['max_score']) ?></span>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($c['course_code']) ?></div>
                                            <div class="text-muted small"><?= e($c['course_name']) ?></div>
                                        </td>

                                        <?php foreach ($parts as $p): ?>
                                            <?php
                                            $courseId = (int)$c['id'];
                                            $partId = (int)$p['id'];
                                            $val = $weightsMap[$courseId][$partId] ?? '';
                                            ?>
                                            <td class="text-center">
                                                <input
                                                    class="form-control form-control-sm text-center"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    name="weights[<?= e((string)$courseId) ?>][<?= e((string)$partId) ?>]"
                                                    value="<?= e($val === '' ? '' : (string)$val) ?>">
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <button class="btn btn-primary">Save Matrix</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
