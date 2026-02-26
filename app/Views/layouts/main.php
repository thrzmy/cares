<?php

declare(strict_types=1);

// $title is available from View::render data
$appCssPath = __DIR__ . '/../../../public/assets/app.css';
$appCssVersion = is_file($appCssPath) ? (string)filemtime($appCssPath) : '1';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?= e(APP_NAME . (isset($title) ? " - $title" : "")) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap (capstone friendly) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_PATH) ?>/assets/app.css?v=<?= e($appCssVersion) ?>" rel="stylesheet">
</head>

<body class="bg-light app-body<?= !empty($_SESSION['user_id']) ? ' has-top-nav' : '' ?>">

    <?php if (!empty($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom app-nav">
        <div class="container">

            <!-- Brand -->
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= e(BASE_PATH) ?>/">
                <img class="brand-logo" src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="City College of Tagaytay logo">
                <span><?= e(APP_NAME) ?></span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <!-- RIGHT: user info / auth -->
                <div class="d-flex gap-2 align-items-center nav-auth">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <?= e(ucfirst(strtolower((string)($_SESSION['name'] ?? 'Account')))) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (empty($_SESSION['force_password_change'])): ?>
                                <?php if (currentRole() === 'administrator'): ?>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator')) ?>" href="<?= e(BASE_PATH) ?>/administrator">Dashboard</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/accounts')) ?>" href="<?= e(BASE_PATH) ?>/administrator/accounts">Account Management</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/students')) ?>" href="<?= e(BASE_PATH) ?>/administrator/students">Student Management</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/scores') ?: isActive('/administrator/results')) ?>" href="<?= e(BASE_PATH) ?>/administrator/scores">Results & Recommendations</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/matrix')) ?>" href="<?= e(BASE_PATH) ?>/administrator/matrix">Matrix Configuration</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/reports')) ?>" href="<?= e(BASE_PATH) ?>/administrator/reports">System Reports</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/logs')) ?>" href="<?= e(BASE_PATH) ?>/administrator/logs">Activity Logs</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/administrator/profile')) ?>" href="<?= e(BASE_PATH) ?>/administrator/profile">My Profile</a>
                                    </li>
                                <?php elseif (currentRole() === 'admission'): ?>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission')) ?>" href="<?= e(BASE_PATH) ?>/admission">Dashboard</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/students')) ?>" href="<?= e(BASE_PATH) ?>/admission/students">Student Management</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/encode')) ?>" href="<?= e(BASE_PATH) ?>/admission/encode">Encode Test Results</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/results')) ?>" href="<?= e(BASE_PATH) ?>/admission/results">Results & Recommendations</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/reports')) ?>" href="<?= e(BASE_PATH) ?>/admission/reports">System Reports</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/logs')) ?>" href="<?= e(BASE_PATH) ?>/admission/logs">Activity Logs</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= e(isActive('/admission/profile')) ?>" href="<?= e(BASE_PATH) ?>/admission/profile">My Profile</a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= e(BASE_PATH) ?>/logout">Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>


    <main class="container py-3 py-md-4">
        <?php require $viewFile; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.querySelectorAll('input[type="password"]').forEach((input, index) => {
        if (input.dataset.passwordToggleInitialized === '1') {
          return;
        }
        input.dataset.passwordToggleInitialized = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'form-check mt-1';

        const toggle = document.createElement('input');
        toggle.className = 'form-check-input';
        toggle.type = 'checkbox';
        toggle.id = `pw-toggle-${index}`;

        const label = document.createElement('label');
        label.className = 'form-check-label small';
        label.setAttribute('for', toggle.id);
        label.textContent = 'Show password';

        toggle.addEventListener('change', () => {
          input.type = toggle.checked ? 'text' : 'password';
        });

        wrapper.appendChild(toggle);
        wrapper.appendChild(label);
        input.insertAdjacentElement('afterend', wrapper);
      });
    </script>
</body>

</html>
