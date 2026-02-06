<?php
declare(strict_types=1);

$summary = $summary ?? [];
$userRoleCounts = $userRoleCounts ?? [];
$userStatusCounts = $userStatusCounts ?? [];
$studentStatusCounts = $studentStatusCounts ?? [];
$examParts = $examParts ?? [];
$topRecommendations = $topRecommendations ?? [];
$topActions = $topActions ?? [];

$now = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
$today = $now->format('Y-m-d');
$weekStart = $now->modify('monday this week')->format('Y-m-d');
$monthStart = $now->modify('first day of this month')->format('Y-m-d');
$yearStart = $now->setDate((int)$now->format('Y'), 1, 1)->format('Y-m-d');

$activePreset = '';
if (($startDate ?? '') === $weekStart && ($endDate ?? '') === $today) {
    $activePreset = 'week';
} elseif (($startDate ?? '') === $monthStart && ($endDate ?? '') === $today) {
    $activePreset = 'month';
} elseif (($startDate ?? '') === $yearStart && ($endDate ?? '') === $today) {
    $activePreset = 'year';
}
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">System Reports</h4>
    <p class="page-subtitle">Generate summaries for accounts, results, and recommendations.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator">Back to Dashboard</a>
  </div>
</div>

<form class="row g-3 align-items-end mb-3" method="get" action="<?= e(BASE_PATH) ?>/administrator/reports">
  <div class="col-12 col-md-4 col-lg-3">
    <label class="form-label small">From</label>
    <input class="form-control" type="date" name="start_date" value="<?= e((string)($startDate ?? '')) ?>">
  </div>
  <div class="col-12 col-md-4 col-lg-3">
    <label class="form-label small">To</label>
    <input class="form-control" type="date" name="end_date" value="<?= e((string)($endDate ?? '')) ?>">
  </div>
  <div class="col-12 col-md-4 col-lg-6">
    <label class="form-label small d-none d-md-block">&nbsp;</label>
    <div class="d-grid d-md-flex justify-content-md-end gap-2">
      <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/reports">Clear Filters</a>
      <button class="btn btn-outline-primary" type="submit">Apply</button>
    </div>
  </div>
</form>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="text-muted small">
    Reporting Period: <span class="fw-semibold"><?= e((string)($periodLabel ?? 'All time')) ?></span>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm <?= $activePreset === 'week' ? 'active' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/reports?start_date=<?= e($weekStart) ?>&end_date=<?= e($today) ?>">This Week</a>
    <a class="btn btn-outline-secondary btn-sm <?= $activePreset === 'month' ? 'active' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/reports?start_date=<?= e($monthStart) ?>&end_date=<?= e($today) ?>">This Month</a>
    <a class="btn btn-outline-secondary btn-sm <?= $activePreset === 'year' ? 'active' : '' ?>" href="<?= e(BASE_PATH) ?>/administrator/reports?start_date=<?= e($yearStart) ?>&end_date=<?= e($today) ?>">This Year</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Total Users</div>
        <div class="h4 fw-bold mb-0"><?= e((string)($summary['users_total'] ?? 0)) ?></div>
        <div class="text-muted small mt-2">Active: <?= e((string)($summary['users_active'] ?? 0)) ?></div>
        <div class="text-muted small">Pending: <?= e((string)($summary['users_pending'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Total Students</div>
        <div class="h4 fw-bold mb-0"><?= e((string)($summary['students_total'] ?? 0)) ?></div>
        <div class="text-muted small mt-2">Pending: <?= e((string)($summary['students_pending'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Score Entries</div>
        <div class="h4 fw-bold mb-0"><?= e((string)($summary['score_entries'] ?? 0)) ?></div>
        <div class="text-muted small mt-2">Students With Scores: <?= e((string)($summary['students_with_scores'] ?? 0)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Log Entries</div>
        <div class="h4 fw-bold mb-0"><?= e((string)($summary['log_entries'] ?? 0)) ?></div>
        <div class="text-muted small mt-2">Tracked Activity Events</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Account Breakdown</h6>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="text-muted small mb-2">Roles</div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Role</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($userRoleCounts)): ?>
                    <?php foreach ($userRoleCounts as $row): ?>
                      <tr>
                        <td><?= e(ucfirst((string)$row['role'])) ?></td>
                        <td class="text-end"><?= e((string)$row['total']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="2" class="text-muted small">No data available.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small mb-2">Account Status</div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Status</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($userStatusCounts)): ?>
                    <?php foreach ($userStatusCounts as $row): ?>
                      <tr>
                        <td><?= e(ucfirst((string)$row['account_status'])) ?></td>
                        <td class="text-end"><?= e((string)$row['total']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="2" class="text-muted small">No data available.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Student Status</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Status</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($studentStatusCounts)): ?>
                <?php foreach ($studentStatusCounts as $row): ?>
                  <tr>
                    <td><?= e(ucfirst((string)$row['status'])) ?></td>
                    <td class="text-end"><?= e((string)$row['total']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="2" class="text-muted small">No data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-12 col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Exam Part Performance</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Exam Part</th>
                <th class="text-end">Max</th>
                <th class="text-end">Entries</th>
                <th class="text-end">Avg Score</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($examParts)): ?>
                <?php foreach ($examParts as $row): ?>
                  <tr>
                    <td><?= e((string)$row['name']) ?></td>
                    <td class="text-end"><?= e(number_format((float)$row['max_score'], 0)) ?></td>
                    <td class="text-end"><?= e((string)$row['entries']) ?></td>
                    <td class="text-end">
                      <?= $row['avg_score'] !== null ? e(number_format((float)$row['avg_score'], 2)) : '-' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-muted small">No data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Top Recommendations</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Course</th>
                <th class="text-end">Students</th>
                <th class="text-end">Avg Score</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($topRecommendations)): ?>
                <?php foreach ($topRecommendations as $row): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= e((string)$row['course_code']) ?></div>
                      <div class="text-muted small"><?= e((string)$row['course_name']) ?></div>
                    </td>
                    <td class="text-end"><?= e((string)$row['student_count']) ?></td>
                    <td class="text-end">
                      <?= $row['avg_score'] !== null ? e(number_format((float)$row['avg_score'], 2)) : '-' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="text-muted small">No recommendation data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-0">
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-2">Top Activity Actions</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Action</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($topActions)): ?>
                <?php foreach ($topActions as $row): ?>
                  <tr>
                    <td><?= e(ucwords(strtolower(str_replace('_', ' ', (string)$row['action'])))) ?></td>
                    <td class="text-end"><?= e((string)$row['total']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="2" class="text-muted small">No activity data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
