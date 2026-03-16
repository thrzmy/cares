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
    'test_results' => 'Admission Test Result Report',
    'course_recommendation' => 'Results & Recommendation Report',
];

$displayStudentName = static function (array $row): string {
    $fullName = trim((string)($row['name'] ?? ''));
    if ($fullName !== '') {
        return $fullName;
    }

    $last = trim((string)($row['last_name'] ?? ''));
    $first = trim((string)($row['first_name'] ?? ''));
    $middle = trim((string)($row['middle_name'] ?? ''));
    return trim($last . ($last !== '' ? ', ' : '') . $first . ($middle !== '' ? ' ' . $middle : ''));
};

$displayAcademicLabel = static function (array $row): string {
    $schoolYear = trim((string)($row['school_year_name'] ?? ''));
    $semester = trim((string)($row['semester_name'] ?? ''));
    if ($schoolYear !== '' && $semester !== '') {
        return $schoolYear . ' - ' . $semester;
    }
    if ($schoolYear !== '') {
        return $schoolYear;
    }
    if ($semester !== '') {
        return $semester;
    }
    return 'Not set';
};

$testedCount = max(0, (int)($summary['students_total'] ?? 0) - (int)($summary['students_without_scores'] ?? 0));
$reportTitle = $reportTitles[$reportType] ?? 'Print Report';
$pageSize = in_array($reportType, ['test_results', 'course_recommendation'], true) ? 'A4 landscape' : 'A4 portrait';

$examStatusTotals = [
    'pending' => 0,
    'passed' => 0,
    'failed' => 0,
];
foreach ($studentStatusCounts as $row) {
    $statusKey = (string)($row['status'] ?? '');
    if (isset($examStatusTotals[$statusKey])) {
        $examStatusTotals[$statusKey] = (int)($row['total'] ?? 0);
    }
}

$averageTotalExamScore = 0.0;
if ($reportType === 'test_results' && !empty($reportData)) {
    $sum = 0.0;
    foreach ($reportData as $row) {
        $sum += (float)($row['total_exam_score'] ?? 0);
    }
    $averageTotalExamScore = $sum / count($reportData);
}

$recommendationProgramCounts = [];
if ($reportType === 'course_recommendation') {
    foreach ($reportData as $row) {
        $courseCode = (string)($row['recommendation']['course_code'] ?? '');
        if ($courseCode === '') {
            continue;
        }
        if (!isset($recommendationProgramCounts[$courseCode])) {
            $recommendationProgramCounts[$courseCode] = [
                'course_code' => $courseCode,
                'course_name' => (string)($row['recommendation']['course_name'] ?? ''),
                'count' => 0,
                'total_score' => 0.0,
            ];
        }
        $recommendationProgramCounts[$courseCode]['count']++;
        $recommendationProgramCounts[$courseCode]['total_score'] += (float)($row['recommendation']['final_score'] ?? 0);
    }
    uasort($recommendationProgramCounts, static function (array $a, array $b): int {
        if ($a['count'] === $b['count']) {
            return $b['total_score'] <=> $a['total_score'];
        }
        return $b['count'] <=> $a['count'];
    });
}

$sortedExamParts = $examParts;
usort($sortedExamParts, static function (array $a, array $b): int {
    $aAvg = $a['avg_score'] !== null ? (float)$a['avg_score'] : -1;
    $bAvg = $b['avg_score'] !== null ? (float)$b['avg_score'] : -1;
    return $bAvg <=> $aAvg;
});

$reportSubtitleMap = [
    'applicant_list' => 'Student directory and score-encoding readiness for the selected period.',
    'test_results' => 'Recorded admission test performance and completion status for the selected period.',
    'course_recommendation' => 'Recommended-program snapshot based on the latest available student scores.',
];

$distributionTitleMap = [
    'applicant_list' => 'Applicant Exam Status Snapshot',
    'test_results' => 'Result Distribution',
    'course_recommendation' => 'Exam Qualification Snapshot',
];

$detailSectionTitleMap = [
    'applicant_list' => 'Applicant Directory',
    'test_results' => 'Test Result Listing',
    'course_recommendation' => 'Recommendation Listing',
];

$typeSummaryCards = [];
if ($reportType === 'applicant_list') {
    $typeSummaryCards = [
        ['label' => 'Applicants', 'value' => (string)($summary['students_total'] ?? 0), 'hint' => 'Students included in this print'],
        ['label' => 'With Scores', 'value' => (string)$testedCount, 'hint' => 'Students with recorded exam entries'],
        ['label' => 'Pending Eval', 'value' => (string)$examStatusTotals['pending'], 'hint' => 'Still incomplete or not yet decided'],
        ['label' => 'Score Entries', 'value' => (string)($summary['score_entries'] ?? 0), 'hint' => 'All recorded exam-part entries'],
    ];
} elseif ($reportType === 'test_results') {
    $typeSummaryCards = [
        ['label' => 'Tested Students', 'value' => (string)$testedCount, 'hint' => 'Students with saved exam records'],
        ['label' => 'Passed', 'value' => (string)$examStatusTotals['passed'], 'hint' => 'Live exam result'],
        ['label' => 'Failed', 'value' => (string)$examStatusTotals['failed'], 'hint' => 'Live exam result'],
        ['label' => 'Avg Total Score', 'value' => number_format($averageTotalExamScore, 2), 'hint' => 'Average total across printed rows'],
    ];
} else {
    $topProgram = reset($recommendationProgramCounts) ?: null;
    $typeSummaryCards = [
        ['label' => 'Applicants', 'value' => (string)($summary['students_total'] ?? 0), 'hint' => 'Students in the selected period'],
        ['label' => 'With Recommendation', 'value' => (string)($summary['students_with_recommendations'] ?? 0), 'hint' => 'Students with a top recommendation'],
        ['label' => 'No Recommendation', 'value' => (string)max(0, (int)($summary['students_total'] ?? 0) - (int)($summary['students_with_recommendations'] ?? 0)), 'hint' => 'Students still without a recommendation'],
        ['label' => 'Top Program', 'value' => $topProgram['course_code'] ?? 'N/A', 'hint' => $topProgram ? ((string)$topProgram['count'] . ' student(s)') : 'No ranked program yet'],
    ];
}

$sigStmt = Database::pdo()->prepare("SELECT name FROM users WHERE id = :id");
$sigStmt->execute([':id' => currentUserId()]);
$currentUser = (string)($sigStmt->fetchColumn() ?: 'Admission Personnel');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($reportTitle) ?></title>
    <style>
        :root {
            --cares-maroon: #6f1119;
            --cares-maroon-soft: #f7ecee;
            --cares-gold: #c79a3b;
            --cares-ink: #1f2937;
            --cares-muted: #667085;
            --cares-line: #d9dee7;
            --cares-surface: #f8fafc;
        }

        @page {
            size: <?= $pageSize ?>;
            margin: 14mm 14mm 18mm 14mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            color: var(--cares-ink);
            font: 10pt/1.45 "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
        }

        .print-shell {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }

        .no-print {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .print-button {
            border: 0;
            border-radius: 999px;
            background: var(--cares-maroon);
            color: #fff;
            padding: 10px 18px;
            font: 600 9pt/1 "Segoe UI", sans-serif;
            cursor: pointer;
        }

        .report-header {
            border: 1px solid var(--cares-line);
            border-top: 6px solid var(--cares-maroon);
            border-radius: 18px;
            padding: 18px 20px;
            background:
                linear-gradient(135deg, rgba(111, 17, 25, 0.05), rgba(199, 154, 59, 0.05)),
                #fff;
            margin-bottom: 16px;
        }

        .report-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex: 0 0 auto;
        }

        .brand-copy {
            flex: 1 1 auto;
            text-align: center;
        }

        .brand-copy h1 {
            margin: 0;
            font: 700 18pt/1.15 Georgia, "Times New Roman", serif;
            color: var(--cares-maroon);
            letter-spacing: 0.2px;
        }

        .brand-copy p {
            margin: 4px 0 0;
            color: var(--cares-muted);
            font-size: 9pt;
        }

        .report-kicker {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--cares-line);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-end;
        }

        .report-kicker-title {
            margin: 0;
            font: 700 13pt/1.2 Georgia, "Times New Roman", serif;
            color: var(--cares-ink);
        }

        .report-kicker-subtitle {
            margin: 4px 0 0;
            color: var(--cares-muted);
            font-size: 8.5pt;
        }

        .meta-grid,
        .summary-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 14px;
        }

        .meta-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .meta-card,
        .summary-card {
            border: 1px solid var(--cares-line);
            border-radius: 14px;
            background: #fff;
            padding: 10px 12px;
        }

        .meta-label,
        .summary-label {
            color: var(--cares-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-size: 7.2pt;
            font-weight: 700;
        }

        .meta-value {
            margin-top: 4px;
            font-size: 9.5pt;
            font-weight: 600;
        }

        .summary-value {
            margin-top: 6px;
            font-size: 17pt;
            line-height: 1;
            font-weight: 700;
            color: var(--cares-maroon);
        }

        .summary-hint {
            margin-top: 6px;
            color: var(--cares-muted);
            font-size: 7.8pt;
        }

        .section {
            margin-bottom: 14px;
        }

        .section-title {
            margin: 0 0 8px;
            font: 700 11pt/1.2 Georgia, "Times New Roman", serif;
            color: var(--cares-maroon);
        }

        .section-panel {
            border: 1px solid var(--cares-line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
        }

        .badge-list {
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--cares-line);
            border-radius: 999px;
            padding: 6px 10px;
            background: var(--cares-surface);
            font-size: 8.5pt;
        }

        .status-chip strong {
            color: var(--cares-maroon);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 9px 10px;
            border-bottom: 1px solid var(--cares-line);
            vertical-align: top;
        }

        th {
            background: var(--cares-surface);
            color: #475467;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-size: 7.6pt;
            font-weight: 700;
            text-align: left;
        }

        tbody tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .cell-title {
            font-weight: 700;
            color: var(--cares-ink);
        }

        .cell-subtitle {
            margin-top: 3px;
            color: var(--cares-muted);
            font-size: 8.2pt;
        }

        .pill-score {
            display: inline-block;
            min-width: 74px;
            text-align: center;
            border-radius: 999px;
            background: var(--cares-maroon-soft);
            border: 1px solid rgba(111, 17, 25, 0.14);
            color: var(--cares-maroon);
            padding: 6px 10px;
            font-weight: 700;
        }

        .text-center {
            text-align: center;
        }

        .empty-state {
            padding: 20px;
            text-align: center;
            color: var(--cares-muted);
        }

        .report-footer {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
        }

        .prepared-by {
            min-width: 220px;
        }

        .signature-line {
            height: 26px;
            border-bottom: 1px solid var(--cares-ink);
            margin-bottom: 6px;
        }

        .footer-note {
            color: var(--cares-muted);
            font-size: 8pt;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-shell">
        <div class="no-print">
            <button class="print-button" onclick="window.print()">Print to PDF</button>
        </div>

        <header class="report-header">
            <div class="report-brand">
                <img src="<?= e(BASE_PATH) ?>/assets/img/cct_logo.png" alt="CCT Logo" class="brand-logo">
                <div class="brand-copy">
                    <h1>City College of Tagaytay</h1>
                    <p>Web-Based Admission Test Assessment with Intelligent Course Recommendation</p>
                </div>
                <img src="<?= e(BASE_PATH) ?>/assets/img/scs_logo.png" alt="SCS Logo" class="brand-logo">
            </div>
            <div class="report-kicker">
                <div>
                    <h2 class="report-kicker-title"><?= e($reportTitle) ?></h2>
                    <p class="report-kicker-subtitle"><?= e($reportSubtitleMap[$reportType] ?? 'Generated from the admission reporting module.') ?></p>
                </div>
                <div class="meta-label">Print Output</div>
            </div>
        </header>

        <section class="meta-grid">
            <div class="meta-card">
                <div class="meta-label">Reporting Period</div>
                <div class="meta-value"><?= e((string)$periodLabel) ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Active Term</div>
                <div class="meta-value"><?= e((string)$semesterName) ?></div>
            </div>
            <div class="meta-card">
                <div class="meta-label">Date Generated</div>
                <div class="meta-value"><?= e(date('F j, Y g:i A')) ?></div>
            </div>
        </section>

        <section class="summary-grid">
            <?php foreach ($typeSummaryCards as $card): ?>
                <div class="summary-card">
                    <div class="summary-label"><?= e((string)$card['label']) ?></div>
                    <div class="summary-value"><?= e((string)$card['value']) ?></div>
                    <div class="summary-hint"><?= e((string)$card['hint']) ?></div>
                </div>
            <?php endforeach; ?>
        </section>

        <?php if (!empty($studentStatusCounts)): ?>
            <section class="section">
                <h3 class="section-title"><?= e($distributionTitleMap[$reportType] ?? 'Exam Result Snapshot') ?></h3>
                <div class="section-panel">
                    <div class="badge-list">
                        <?php foreach ($studentStatusCounts as $row): ?>
                            <span class="status-chip">
                                <?= e(studentStatusLabel((string)$row['status'])) ?>
                                <strong><?= e((string)$row['total']) ?></strong>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="section">
            <h3 class="section-title"><?= e($detailSectionTitleMap[$reportType] ?? 'Report Details') ?></h3>
            <div class="section-panel">
                <table>
                    <thead>
                        <?php if ($reportType === 'applicant_list'): ?>
                            <tr>
                                <th style="width: 28%;">Student</th>
                                <th>Email</th>
                                <th style="width: 14%;">Exam Result</th>
                                <th style="width: 28%;">Academic Term / Scores</th>
                            </tr>
                        <?php elseif ($reportType === 'test_results'): ?>
                            <tr>
                                <th>Student</th>
                                <th style="width: 16%;">Application Number</th>
                                <th style="width: 16%;" class="text-center">Total Score</th>
                                <th style="width: 14%;" class="text-center">Exam Result</th>
                                <th style="width: 20%;" class="text-center">Latest Exam Date</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Student</th>
                                <th style="width: 15%;">Application Number</th>
                                <th style="width: 22%;">Academic Term</th>
                                <th style="width: 22%;">Recommended Program</th>
                                <th style="width: 14%;" class="text-center">Overall Score</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if (empty($reportData)): ?>
                            <tr>
                                <td colspan="10" class="empty-state">No records found for the selected period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <?php if ($reportType === 'applicant_list'): ?>
                                        <td>
                                            <div class="cell-title"><?= e($displayStudentName($row)) ?></div>
                                        </td>
                                        <td><?= e((string)($row['email'] ?? '')) ?></td>
                                        <td><?= e(studentStatusLabel((string)($row['status'] ?? 'pending'))) ?></td>
                                        <td>
                                            <div class="cell-title"><?= e($displayAcademicLabel($row)) ?></div>
                                            <div class="cell-subtitle">Score entries: <?= e((string)($row['score_entries'] ?? 0)) ?></div>
                                        </td>
                                    <?php elseif ($reportType === 'test_results'): ?>
                                        <td><div class="cell-title"><?= e($displayStudentName($row)) ?></div></td>
                                        <td><?= e((string)($row['application_number'] ?? 'Not provided')) ?></td>
                                        <td class="text-center"><span class="pill-score"><?= e(number_format((float)($row['total_exam_score'] ?? 0), 2)) ?></span></td>
                                        <td class="text-center"><?= e(studentStatusLabel((string)($row['status'] ?? 'pending'))) ?></td>
                                        <td class="text-center"><?= !empty($row['exam_date']) ? e(date('M j, Y', strtotime((string)$row['exam_date']))) : 'Not available' ?></td>
                                    <?php else: ?>
                                        <td><div class="cell-title"><?= e($displayStudentName($row)) ?></div></td>
                                        <td><?= e((string)($row['application_number'] ?? 'Not provided')) ?></td>
                                        <td><?= e($displayAcademicLabel($row)) ?></td>
                                        <td>
                                            <div class="cell-title"><?= e((string)($row['recommendation']['course_code'] ?? 'N/A')) ?></div>
                                            <div class="cell-subtitle"><?= e((string)($row['recommendation']['course_name'] ?? 'No recommendation')) ?></div>
                                        </td>
                                        <td class="text-center"><span class="pill-score"><?= e(number_format((float)($row['recommendation']['final_score'] ?? 0), 2)) ?>%</span></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($reportType === 'applicant_list'): ?>
            <section class="section">
                <h3 class="section-title">Applicant Readiness</h3>
                <div class="section-panel">
                    <div class="badge-list">
                        <span class="status-chip">With Scores <strong><?= e((string)$testedCount) ?></strong></span>
                        <span class="status-chip">Without Scores <strong><?= e((string)($summary['students_without_scores'] ?? 0)) ?></strong></span>
                        <span class="status-chip">Pending Evaluation <strong><?= e((string)$examStatusTotals['pending']) ?></strong></span>
                    </div>
                </div>
            </section>
        <?php elseif ($reportType === 'test_results' && !empty($sortedExamParts)): ?>
            <section class="section">
                <h3 class="section-title">Exam Part Averages</h3>
                <div class="section-panel">
                    <table>
                        <thead>
                            <tr>
                                <th>Exam Part</th>
                                <th style="width: 14%;" class="text-center">Max</th>
                                <th style="width: 16%;" class="text-center">Entries</th>
                                <th style="width: 18%;" class="text-center">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($sortedExamParts, 0, 8) as $row): ?>
                                <tr>
                                    <td><div class="cell-title"><?= e((string)$row['name']) ?></div></td>
                                    <td class="text-center"><?= e(number_format((float)$row['max_score'], 0)) ?></td>
                                    <td class="text-center"><?= e((string)$row['entries']) ?></td>
                                    <td class="text-center"><span class="pill-score"><?= $row['avg_score'] !== null ? e(number_format((float)$row['avg_score'], 2)) : '-' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php elseif ($reportType === 'course_recommendation' && !empty($recommendationProgramCounts)): ?>
            <section class="section">
                <h3 class="section-title">Top Recommended Programs</h3>
                <div class="section-panel">
                    <table>
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th style="width: 16%;" class="text-center">Students</th>
                                <th style="width: 18%;" class="text-center">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice(array_values($recommendationProgramCounts), 0, 6) as $row): ?>
                                <tr>
                                    <td>
                                        <div class="cell-title"><?= e((string)$row['course_code']) ?></div>
                                        <div class="cell-subtitle"><?= e((string)$row['course_name']) ?></div>
                                    </td>
                                    <td class="text-center"><?= e((string)$row['count']) ?></td>
                                    <td class="text-center">
                                        <span class="pill-score"><?= e(number_format(((float)$row['total_score'] / max(1, (int)$row['count'])), 2)) ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <footer class="report-footer">
            <div class="prepared-by">
                <div class="signature-line"></div>
                <div class="cell-title"><?= e($currentUser) ?></div>
                <div class="cell-subtitle">Admission Personnel / Prepared By</div>
            </div>
            <div class="footer-note">
                City College of Tagaytay - Admission Test Assessment<br>
                <?= e($reportTitle) ?>
            </div>
        </footer>
    </div>
    <script>
      window.addEventListener('afterprint', function () {
        window.close();
      });

      window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
      });
    </script>
</body>
</html>
