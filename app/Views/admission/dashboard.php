<?php
declare(strict_types=1);

RoleMiddleware::requireRole('admission');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 text-gray-800 font-cinzel text-maroon fw-bold">Dashboard</h2>
        <p class="text-muted small mb-0 mt-1">Welcome back, <?= e($_SESSION['name'] ?? 'Admission Officer') ?>.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= e(BASE_PATH) ?>/admission/students" class="text-decoration-none">
            <div class="card stat-card border-0 h-100 p-4 d-flex flex-column align-items-center text-center gold">
                <div class="icon-wrapper mx-auto mb-3 shadow-sm text-success bg-success bg-opacity-10">
                    <i class="fa-solid fa-user-graduate fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Student Management</h5>
                <span class="text-muted small fw-medium mt-auto">Manage student information by semester</span>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= e(BASE_PATH) ?>/admission/encode" class="text-decoration-none">
            <div class="card stat-card border-0 h-100 p-4 d-flex flex-column align-items-center text-center maroon">
                <div class="icon-wrapper mx-auto mb-3 shadow-sm text-primary bg-primary bg-opacity-10">
                    <i class="fa-solid fa-keyboard fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Encode Test Results</h5>
                <span class="text-muted small fw-medium mt-auto">Encode and manage test scores</span>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <a href="<?= e(BASE_PATH) ?>/admission/results" class="text-decoration-none">
            <div class="card stat-card border-0 h-100 p-4 d-flex flex-column align-items-center text-center success">
                <div class="icon-wrapper mx-auto mb-3 shadow-sm text-warning bg-warning bg-opacity-10">
                    <i class="fa-solid fa-square-poll-vertical fs-4 text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Results & Recommendation</h5>
                <span class="text-muted small fw-medium mt-auto">Review results, recommendations, and edit saved scores</span>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
        <a href="<?= e(BASE_PATH) ?>/admission/reports" class="text-decoration-none">
            <div class="card stat-card border-0 h-100 p-4 d-flex flex-column align-items-center text-center maroon">
                <div class="icon-wrapper mx-auto mb-3 shadow-sm text-secondary bg-secondary bg-opacity-10">
                    <i class="fa-solid fa-file-contract fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Report Management</h5>
                <span class="text-muted small fw-medium mt-auto">Generate summaries and printed reports</span>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
        <a href="<?= e(BASE_PATH) ?>/admission/logs" class="text-decoration-none">
            <div class="card stat-card border-0 h-100 p-4 d-flex flex-column align-items-center text-center info">
                <div class="icon-wrapper mx-auto mb-3 shadow-sm text-dark bg-dark bg-opacity-10">
                    <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Activity Logs</h5>
                <span class="text-muted small fw-medium mt-auto">Review logged admission activities</span>
            </div>
        </a>
    </div>
</div>
