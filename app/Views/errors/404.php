<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> - 404 Not Found</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(180deg, #4b3e5f 0%, #70121a 55%, #8a3a1f 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
        }
        .error-card {
            width: min(100%, 520px);
            background: rgba(255,255,255,0.98);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }
        .brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }
        .kicker {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        h1 {
            margin: 0 0 8px;
            color: #6f1119;
            font: 700 36px/1.1 Georgia, "Times New Roman", serif;
        }
        p {
            margin: 0;
            color: #4b5563;
            line-height: 1.6;
        }
        .actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-primary {
            background: #6f1119;
            color: #fff;
        }
        .btn-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="brand">
            <img src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="City College of Tagaytay logo">
            <div>
                <div class="kicker">Academic Portal</div>
                <div style="font-weight: 700; color: #6f1119;">Admission Test Assessment</div>
            </div>
        </div>
        <div class="kicker">404 Not Found</div>
        <h1>Page not found</h1>
        <p>The page you tried to open does not exist or may have been moved. Please return to login or go back to the previous page.</p>
        <div class="actions">
            <a class="btn btn-primary" href="<?= e(BASE_PATH) ?>/login">Go to Login</a>
            <a class="btn btn-secondary" href="javascript:history.back()">Go Back</a>
        </div>
    </div>
</body>
</html>
