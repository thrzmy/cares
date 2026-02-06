<?php

declare(strict_types=1);

// $title is available from View::render data
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?= e(APP_NAME . (isset($title) ? " - $title" : "")) ?></title>

    <!-- Bootstrap (capstone friendly) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container">

            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="<?= e(BASE_PATH) ?>/">
                <?= e(APP_NAME) ?>
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible nav -->
            <div class="collapse navbar-collapse" id="mainNav">

                <!-- LEFT: role-based navigation -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (!empty($_SESSION['user_id'])): ?>

                        <?php if (currentRole() === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admin')) ?>" href="<?= e(BASE_PATH) ?>/admin">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admin/scores')) ?>" href="<?= e(BASE_PATH) ?>/admin/scores">
                                    Scores
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admin/results')) ?>" href="<?= e(BASE_PATH) ?>/admin/results">
                                    Results
                                </a>
                            </li>

                        <?php elseif (currentRole() === 'guidance'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/guidance')) ?>" href="<?= e(BASE_PATH) ?>/guidance">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/guidance/weights')) ?>" href="<?= e(BASE_PATH) ?>/guidance/weights">
                                    Weights
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>

                <!-- RIGHT: user info / auth -->
                <div class="d-flex gap-2 align-items-center">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <span class="badge text-bg-light border">
                            <?= e((string)($_SESSION['role'] ?? '')) ?>
                        </span>
                        <span class="text-muted small d-none d-md-inline">
                            <?= e((string)($_SESSION['name'] ?? '')) ?>
                        </span>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/logout">
                            Logout
                        </a>
                    <?php else: ?>
                        <a class="btn btn-primary btn-sm" href="<?= e(BASE_PATH) ?>/login">
                            Login
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>


    <main class="container py-3 py-md-4">
        <?php require $viewFile; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>