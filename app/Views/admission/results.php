<?php
declare(strict_types=1);
$success = $success ?? null;
$error = $error ?? null;
$recommendations = $recommendations ?? [];
$recordScopeFilter = 'active';
$activeSemester = $activeSemester ?? null;

$recommendationDisplay = static function (array $recs): array {
  $choiceMatches = array_values(array_filter($recs, static fn(array $rec): bool => !empty($rec['is_first_choice']) || !empty($rec['is_second_choice'])));

  if (!empty($choiceMatches)) {
    return [
      'state' => 'choice_match',
      'items' => $choiceMatches,
      'message' => null,
    ];
  }

  if (!empty($recs)) {
    return [
      'state' => 'other_match',
      'items' => [],
      'message' => 'Qualified in other program(s).',
    ];
  }

  return [
    'state' => 'none',
    'items' => [],
    'message' => 'No qualified program.',
  ];
};
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Results & Recommendation</h4>
    <p class="page-subtitle">Review encoded exam scores and course recommendations.</p>
  </div>
</div>

<?php if (!empty($activeSemester)): ?>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-4">
    <div>
      <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Current Academic Year</h6>
      <div class="fw-semibold"><?= e((string)($activeSemester['school_year_name'] ?? '')) ?></div>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/results">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-8">
      <label class="form-label small">Search Students</label>
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or application number">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label small">Status</label>
      <select class="form-select" name="status">
        <option value="">All statuses</option>
        <option value="passed" <?= ($statusFilter ?? '') === 'passed' ? 'selected' : '' ?>>Passed</option>
        <option value="failed" <?= ($statusFilter ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
      </select>
    </div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-end align-items-stretch align-items-md-center gap-2">
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/results">Clear Filters</a>
          <button class="btn btn-primary" type="submit">Apply Filters</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if (!empty($students)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($students as $s): ?>
      <?php $recDisplay = $recommendationDisplay($recommendations[(int)$s['id']] ?? []); ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
              <div class="text-muted small">Application No.: <?= e((string)($s['application_number'] ?? 'Not provided')) ?></div>
              <div class="text-muted small mt-1">App: <?= e(studentApplicationStatusLabel((string)($s['application_status'] ?? 'new_student'))) ?></div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
              <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>"><?= e(studentStatusLabel((string)($s['status'] ?? 'pending'))) ?></span>
              <span class="badge <?= e((string)($s['screening_status'] ?? 'pending') === 'qualified' ? 'text-bg-success' : 'text-bg-danger') ?>"><?= e(studentScreeningStatusLabel((string)($s['screening_status'] ?? 'pending'))) ?></span>
            </div>
          </div>
          <?php if (!empty($recDisplay['items'])): ?>
            <div class="mt-3">
              <div class="text-muted small mb-1">Programs</div>
              <?php foreach ($recDisplay['items'] as $rec): ?>
                <div class="d-flex justify-content-between small">
                  <span>
                    <?= e($rec['course_code']) ?>
                    <?php if (!empty($rec['is_first_choice'])): ?>
                      <span class="text-muted">- 1st Choice</span>
                    <?php elseif (!empty($rec['is_second_choice'])): ?>
                      <span class="text-muted">- 2nd Choice</span>
                    <?php endif; ?>
                  </span>
                  <span class="text-muted"><?= e(number_format((float)$rec['total_score'], 2)) ?>%</span>
                </div>
              <?php endforeach; ?>
              <?php if (!empty($recDisplay['message'])): ?>
                <div class="text-muted small mt-2"><?= e($recDisplay['message']) ?></div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="text-muted small mt-2"><?= e((string)$recDisplay['message']) ?></div>
          <?php endif; ?>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/results/view?id=<?= (int)$s['id'] ?>">View Summary</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
          <tr>
            <th>Application Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>Exam</th>
            <th>Qualified</th>
            <th>Qualified Program(s)</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <?php $recDisplay = $recommendationDisplay($recommendations[(int)$s['id']] ?? []); ?>
            <tr>
              <td class="fw-semibold"><?= e((string)($s['application_number'] ?? 'Not provided')) ?></td>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e($s['email']) ?></td>
              <td><span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>"><?= e(studentStatusLabel((string)($s['status'] ?? 'pending'))) ?></span></td>
              <td><span class="badge <?= e((string)($s['screening_status'] ?? 'pending') === 'qualified' ? 'text-bg-success' : 'text-bg-danger') ?>"><?= e(studentScreeningStatusLabel((string)($s['screening_status'] ?? 'pending'))) ?></span></td>
              <td>
                <?php if (!empty($recDisplay['items'])): ?>
                  <?php foreach ($recDisplay['items'] as $rec): ?>
                    <div class="d-flex justify-content-between small">
                      <span>
                        <?= e($rec['course_code']) ?>
                        <?php if (!empty($rec['is_first_choice'])): ?>
                          <span class="text-muted">- 1st Choice</span>
                        <?php elseif (!empty($rec['is_second_choice'])): ?>
                          <span class="text-muted">- 2nd Choice</span>
                        <?php endif; ?>
                      </span>
                      <span class="text-muted"><?= e(number_format((float)$rec['total_score'], 2)) ?>%</span>
                    </div>
                  <?php endforeach; ?>
                  <?php if (!empty($recDisplay['message'])): ?>
                    <div class="text-muted small mt-1"><?= e($recDisplay['message']) ?></div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted small"><?= e((string)$recDisplay['message']) ?></span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results/view?id=<?= (int)$s['id'] ?>">View Summary</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No matching students found.</div>
  </div>
<?php endif; ?>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../partials/pagination.php';
?>
