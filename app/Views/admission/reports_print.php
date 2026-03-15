<?php
declare(strict_types=1);

$reportType = $reportType ?? 'applicant_list';
$reportData = $reportData ?? [];
$summary = $summary ?? [];
$studentStatusCounts = $studentStatusCounts ?? [];
$examParts = $examParts ?? [];
$periodLabel = $periodLabel ?? 'All time';
$semesterName = $semesterName ?? 'All Semesters';

$reportTitles = [
    'applicant_list' => 'Applicant List Report',
    'test_results' => 'Admission Test Results Report',
    'course_recommendation' => 'Intelligent Course Recommendation Report',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($reportTitles[$reportType] ?? 'Print Report') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --dark: #212529;
            --muted: #6c757d;
            --border: #dee2e6;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 0;
            background-color: #fff;
            font-size: 10pt;
            line-height: 1.4;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }

        .print-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            position: relative;
        }

        .report-header {
            text-align: center;
            border-bottom: 2px solid var(--maroon);
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .header-logo {
            width: 60px;
            height: auto;
        }
        .header-text h1 {
            font-size: 14pt;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: var(--maroon);
            text-transform: uppercase;
        }
        .header-text h2 {
            font-size: 11pt;
            font-weight: 600;
            margin: 0 0 2px 0;
        }
        .header-text p {
            font-size: 9pt;
            margin: 0;
            color: var(--muted);
        }

        .metadata-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 9pt;
        }
        .metadata-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .metadata-label {
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: var(--maroon);
            border-bottom: 1px solid var(--border);
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid var(--border);
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-muted { color: var(--muted); }

        .report-footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 9pt;
        }
        .signature-block {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid var(--dark);
            margin-bottom: 5px;
            height: 30px;
        }

        .chart-bar-container {
            width: 100px;
            height: 12px;
            background: #eee;
            border-radius: 6px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
        .chart-bar {
            height: 100%;
            background: var(--maroon);
        }

        @media print {
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="no-print" style="text-align: right; margin-bottom: 10px;">
            <button onclick="window.print()" style="padding: 8px 16px; background: var(--maroon); color: #fff; border: none; border-radius: 4px; cursor: pointer;">Print to PDF</button>
        </div>

        <div class="report-header">
            <img src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="CCT Logo" class="header-logo">
            <div class="header-text">
                <h1>City College of Tagaytay</h1>
                <h2>Admission and Recommendation System</h2>
                <p><?= e($reportTitles[$reportType]) ?></p>
            </div>
            <img src="<?= e(BASE_PATH) ?>/assets/img/scs_logo.png" alt="SCS Logo" class="header-logo">
        </div>

        <div class="metadata-section">
            <div class="metadata-group">
                <span class="metadata-label">Reporting Period</span>
                <span class="metadata-value"><?= e((string)$periodLabel) ?></span>
            </div>
            <div class="metadata-group">
                <span class="metadata-label">Academic Filter</span>
                <span class="metadata-value"><?= e((string)$semesterName) ?></span>
            </div>
            <div class="metadata-group" style="text-align: right;">
                <span class="metadata-label">Date Generated</span>
                <span class="metadata-value"><?= e(date('F j, Y')) ?></span>
            </div>
        </div>

        <div class="section-title">Summary Statistics</div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; text-align: center;">
                <div style="font-size: 7.5pt; text-transform: uppercase; color: #666;">Applicants</div>
                <div style="font-size: 14pt; font-weight: 700;"><?= e((string)($summary['students_total'] ?? 0)) ?></div>
            </div>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; text-align: center;">
                <div style="font-size: 7.5pt; text-transform: uppercase; color: #666;">Tested</div>
                <div style="font-size: 14pt; font-weight: 700;"><?= e((string)(($summary['students_total'] ?? 0) - ($summary['students_without_scores'] ?? 0))) ?></div>
            </div>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; text-align: center;">
                <div style="font-size: 7.5pt; text-transform: uppercase; color: #666;">Recommended</div>
                <div style="font-size: 14pt; font-weight: 700;"><?= e((string)($summary['students_with_recommendations'] ?? 0)) ?></div>
            </div>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; text-align: center;">
                <div style="font-size: 7.5pt; text-transform: uppercase; color: #666;">Score Count</div>
                <div style="font-size: 14pt; font-weight: 700;"><?= e((string)($summary['score_entries'] ?? 0)) ?></div>
            </div>
        </div>

        <div class="section-title">Report Data Content</div>
        <table>
            <thead>
                <?php if ($reportType === 'applicant_list'): ?>
                    <tr>
                        <th style="width: 20%;">Student Information</th>
                        <th style="width: 15%;">App Number</th>
                        <th>Email</th>
                        <th style="width: 20%;">App Status</th>
                        <th style="width: 25%;">Scoring Data</th>
                    </tr>
                <?php elseif ($reportType === 'test_results'): ?>
                    <tr>
                        <th style="width: 15%;">Student ID</th>
                        <th>Name</th>
                        <th class="text-center" style="width: 15%;">Score</th>
                        <th class="text-center" style="width: 15%;">Status</th>
                        <th class="text-center" style="width: 20%;">Exam Date</th>
                    </tr>
                <?php elseif ($reportType === 'course_recommendation'): ?>
                    <tr>
                        <th style="width: 15%;">Student ID</th>
                        <th>Full Name</th>
                        <th style="width: 20%;">SHS Strand / Aligned</th>
                        <th>Recommended Program</th>
                        <th class="text-center" style="width: 15%;">Overall %</th>
                    </tr>
                <?php elseif ($reportType === 'enrollment_summary'): ?>
                    <tr>
                        <th style="width: 15%;">Code</th>
                        <th>Program/Course Name</th>
                        <th class="text-center" style="width: 15%;">Rec.</th>
                        <th class="text-center" style="width: 15%;">Adm.</th>
                        <th class="text-center" style="width: 25%;">Visualization (Rec vs Adm)</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php if (!empty($reportData)): ?>
                    <?php foreach ($reportData as $row): ?>
                        <tr>
                            <?php if ($reportType === 'applicant_list'): ?>
                                <td class="fw-bold" style="text-transform: uppercase;"><?= e($row['last_name'] . ', ' . $row['first_name'] . (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '')) ?></td>
                                <td class="fw-bold"><?= e((string)($row['id_number'] ?? '-')) ?></td>
                                <td><?= e($row['email']) ?></td>
                                <td><?= e(ucwords((string)($row['application_type'] ?? 'New Student'))) ?></td>
                                <td style="font-size: 8pt;">
                                    <strong>1st:</strong> <?= e($row['first_choice_code'] ?? 'N/A') ?>
                                    <strong>2nd:</strong> <?= e($row['second_choice_code'] ?? 'N/A') ?><br>
                                    <strong>Strand:</strong> <?= e($row['shs_strand'] ?? 'N/A') ?>
                                    <strong>GPA:</strong> <?= e($row['gpa'] ?? 'N/A') ?>
                                </td>

                            <?php elseif ($reportType === 'test_results'): ?>
                                <td class="fw-bold"><?= e($row['id_number']) ?></td>
                                <td style="text-transform: uppercase;"><?= e($row['last_name'] . ', ' . $row['first_name'] . (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '')) ?></td>
                                <td class="text-center fw-bold"><?= e((string)$row['total_exam_score']) ?></td>
                                <td class="text-center"><?= e(ucfirst($row['status'])) ?></td>
                                <td class="text-center"><?= $row['exam_date'] ? date('M j, Y', strtotime($row['exam_date'])) : '---' ?></td>

                            <?php elseif ($reportType === 'course_recommendation'): ?>
                                <td class="fw-bold"><?= e($row['id_number']) ?></td>
                                <td style="text-transform: uppercase;"><?= e($row['last_name'] . ', ' . $row['first_name'] . (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '')) ?></td>
                                <td>
                                    <?= e($row['shs_strand']) ?>
                                    <?php if (isset($row['recommendation']['is_aligned_strand'])): ?>
                                        <span style="margin-left: 5px; color: <?= $row['recommendation']['is_aligned_strand'] ? '#198754' : '#dc3545' ?>;">
                                            <?= $row['recommendation']['is_aligned_strand'] ? 'âœ“' : 'âœ—' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= e($row['recommendation']['course_code'] ?? 'N/A') ?></strong><br>
                                    <span class="text-muted" style="font-size: 8pt;"><?= e($row['recommendation']['course_name'] ?? 'No match') ?></span>
                                </td>
                                <td class="text-center fw-bold"><?= number_format((float)($row['recommendation']['final_score'] ?? 0), 1) ?>%</td>

                            <?php elseif ($reportType === 'enrollment_summary'): ?>
                                <td class="fw-bold"><?= e($row['course_code']) ?></td>
                                <td><?= e($row['course_name']) ?></td>
                                <td class="text-center fw-bold"><?= e((string)$row['recommended']) ?></td>
                                <td class="text-center fw-bold"><?= e((string)$row['accepted']) ?></td>
                                <td class="text-center">
                                    <?php
                                    $percent = $row['recommended'] > 0 ? ($row['accepted'] / $row['recommended']) * 100 : 0;
                                    ?>
                                    <div class="chart-bar-container">
                                        <div class="chart-bar" style="width: <?= $percent ?>%;"></div>
                                    </div>
                                    <span style="font-size: 8pt;"><?= round($percent) ?>%</span>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center text-muted py-5">No records found for the selected period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="report-footer">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div style="font-weight: 600;">
                    <?php
                         $sigStmt = Database::pdo()->prepare("SELECT name FROM users WHERE id = :id");
                         $sigStmt->execute([':id' => currentUserId()]);
                         $currentUser = $sigStmt->fetchColumn() ?: 'Admission Personnel';
                         echo e($currentUser);
                    ?>
                </div>
                <div class="text-muted" style="font-size: 8pt;">Admission Personnel / Prepared By</div>
            </div>
            <div class="text-muted" style="text-align: right; font-size: 8pt;">
                City College of Tagaytay Admission System<br>
                Generated on <?= date('Y-m-d H:i:s') ?><br>
                Page 1 of 1
            </div>
        </div>
    </div>
<script>
  window.addEventListener('load', function() {
    setTimeout(function() { window.print(); }, 500);
  });
</script>
</body>
</html>
