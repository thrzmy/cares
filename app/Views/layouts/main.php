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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap (capstone friendly) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_PATH) ?>/assets/app.css" rel="stylesheet">
</head>

<body class="bg-light app-body">

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom app-nav">
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

                        <?php if (currentRole() === 'administrator'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator')) ?>" href="<?= e(BASE_PATH) ?>/administrator">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/accounts')) ?>" href="<?= e(BASE_PATH) ?>/administrator/accounts">
                                    Account Management
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/matrix')) ?>" href="<?= e(BASE_PATH) ?>/administrator/matrix">
                                    Matrix Management
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/scores')) ?>" href="<?= e(BASE_PATH) ?>/administrator/scores">
                                    Encode Results
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/results')) ?>" href="<?= e(BASE_PATH) ?>/administrator/results">
                                    View Results
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/logs')) ?>" href="<?= e(BASE_PATH) ?>/administrator/logs">
                                    Monitor Logs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/reports')) ?>" href="<?= e(BASE_PATH) ?>/administrator/reports">
                                    Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/students')) ?>" href="<?= e(BASE_PATH) ?>/administrator/students">
                                    Student Management
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/administrator/profile')) ?>" href="<?= e(BASE_PATH) ?>/administrator/profile">
                                    My Profile
                                </a>
                            </li>

                        <?php elseif (currentRole() === 'admission'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admission')) ?>" href="<?= e(BASE_PATH) ?>/admission">
                                    Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admission/encode')) ?>" href="<?= e(BASE_PATH) ?>/admission/encode">
                                    Encode Results
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admission/results')) ?>" href="<?= e(BASE_PATH) ?>/admission/results">
                                    View Results
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admission/storage')) ?>" href="<?= e(BASE_PATH) ?>/admission/storage">
                                    Result Storage
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= e(isActive('/admission/students')) ?>" href="<?= e(BASE_PATH) ?>/admission/students">
                                    Account Management
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>

                <!-- RIGHT: user info / auth -->
                <div class="d-flex gap-2 align-items-center nav-auth">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <?php $roleLabel = ucfirst((string)($_SESSION['role'] ?? '')); ?>
                        <span class="badge text-bg-light border">
                            <?= e($roleLabel) ?>
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
