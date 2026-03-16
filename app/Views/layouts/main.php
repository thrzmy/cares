<?php
declare(strict_types=1);

$appCssPath = __DIR__ . '/../../../public/assets/app.css';
$appCssVersion = is_file($appCssPath) ? (string)filemtime($appCssPath) : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME . (isset($title) ? " - $title" : "")) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= e(BASE_PATH) ?>/assets/app.css?v=<?= e($appCssVersion) ?>">
</head>
<body class="app-body">
    <?php if (isset($_SESSION['user_id'])): ?>

    <div class="app-wrapper d-flex">
        <!-- Sidebar -->
        <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
            <div class="sidebar-header d-flex justify-content-between align-items-center">
                <div class="brand-wrapper d-flex align-items-center gap-3">
                    <img src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="CCT Logo" class="brand-logo">
                    <div class="brand-text d-flex flex-column">
                        <span class="brand-title fw-bold text-white font-cinzel" style="font-size: 1.05rem; line-height: 1.15;">Admission Test Assessment</span>
                        <span class="brand-subtitle text-white-50" style="font-size: 0.68rem; line-height: 1.2;">with Intelligent Course Recommendation</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white d-lg-none py-3 pe-3 ps-0 m-0" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
            </div>

            <div class="sidebar-user mt-4 mb-4 px-3">
                <div class="sidebar-user-card">
                    <div class="user-info d-flex flex-column overflow-hidden">
                        <div class="user-name text-truncate text-white fw-semibold" title="<?= e($_SESSION['name'] ?? 'User') ?>">
                            <?= e($_SESSION['name'] ?? 'User') ?>
                        </div>
                        <div class="user-role mt-1">
                            <span class="cares-role-badge">
                                <?= ucfirst(e($_SESSION['role'] ?? 'user')) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-body flex-grow-1 px-2">
                <ul class="sidebar-nav list-unstyled">
                    <?php if ($_SESSION['role'] === 'administrator'): ?>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/dashboard') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/dashboard">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-file-contract fs-5"></i></div>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/semesters') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/semesters">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-calendar-check fs-5"></i></div>
                                <span>S.Y. & Semester</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/students') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/students">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-user-graduate fs-5"></i></div>
                                <span>Student Management</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/scores') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/scores">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-square-poll-vertical fs-5"></i></div>
                                <span>Results & Recommendation</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/matrix') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/matrix">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-table-list fs-5"></i></div>
                                <span>Matrix Configuration</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/accounts') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/accounts">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-users-gear fs-5"></i></div>
                                <span>Account Management</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/reports') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/reports">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-chart-column fs-5"></i></div>
                                <span>Report Management</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/administrator/logs') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/logs">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-clock-rotate-left fs-5"></i></div>
                                <span>Activity Logs</span>
                            </a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'admission'): ?>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/dashboard') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/dashboard">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-file-contract fs-5"></i></div>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/students') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/students">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-user-graduate fs-5"></i></div>
                                <span>Student Management</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/encode') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/encode">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-keyboard fs-5"></i></div>
                                <span>Encode Test Results</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/results') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/results">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-square-poll-vertical fs-5"></i></div>
                                <span>Results & Recommendation</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/reports') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/reports">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-chart-column fs-5"></i></div>
                                <span>Report Management</span>
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/admission/logs') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/admission/logs">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-clock-rotate-left fs-5"></i></div>
                                <span>Activity Logs</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-footer mt-auto px-2 pb-3 pt-3 border-top border-white border-opacity-10">
                <ul class="sidebar-nav list-unstyled m-0">
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white-50 px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 transition-all <?= isActive('/' . $_SESSION['role'] . '/profile') ? 'bg-white bg-opacity-10 text-white fw-semibold' : '' ?>" href="<?= e(BASE_PATH) ?>/<?= e($_SESSION['role']) ?>/profile">
                            <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-gear fs-5"></i></div>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form action="<?= e(BASE_PATH) ?>/logout" method="POST" class="m-0">
                            <?= csrfField() ?>
                            <button type="submit" class="nav-link btn text-start text-danger px-3 py-2 rounded-3 text-decoration-none d-flex align-items-center gap-3 w-100 bg-transparent border-0 transition-all hover-bg-danger-light">
                                <div class="nav-icon-wrap w-20 d-flex justify-content-center"><i class="fa-solid fa-right-from-bracket fs-5"></i></div>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main flex-grow-1 d-flex flex-column" style="background-color: var(--cares-bg);">
            <!-- Mobile Header with Hamburger -->
            <header class="mobile-header d-lg-none bg-white p-3 d-flex align-items-center justify-content-between shadow-sm sticky-top z-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-link text-dark p-0 border-0 text-maroon" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="fa-solid fa-bars fs-4"></i>
                    </button>
                    <span class="fs-6 fw-bold font-cinzel text-maroon m-0">Admission Test Assessment</span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content-wrapper p-3 p-md-4 p-lg-5 flex-grow-1 overflow-x-hidden overflow-y-auto">
                <?php require_once $viewFile; ?>
            </main>
        </div>
    </div>

    <?php else: ?>
        <!-- Non-authenticated overlay (Login screen) -->
        <main class="container-fluid p-0 min-vh-100 d-flex flex-column justify-content-center bg-cares-auth">
            <?php require_once $viewFile; ?>
        </main>
    <?php endif; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Password toggle logic
            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i') || this.querySelector('svg');

                    if (input.type === 'password') {
                        input.type = 'text';
                        if (icon.tagName.toLowerCase() === 'i') {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        }
                    } else {
                        input.type = 'password';
                        if (icon.tagName.toLowerCase() === 'i') {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                });
            });

            // Heartbeat exactly every 60s
            setInterval(() => {
                fetch('<?= e(BASE_PATH) ?>/session/ping')
                    .then(res => res.json())
                    .catch(() => {});
            }, 60000);
        });
    </script>
</body>
</html>
