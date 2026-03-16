<?php
declare(strict_types=1);
$success = $success ?? null;
$error = $error ?? null;
$activeSemester = $activeSemester ?? null;
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Encode Test Results</h4>
    <p class="page-subtitle">Continue encoding students whose exam result is still pending.</p>
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

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-3">
      <div>
        <h6 class="fw-bold mb-1">Bulk Upload</h6>
        <div class="text-muted small">Upload a CSV file using the college admission test format to import student data and exam scores.</div>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode/template">Download CSV Template</a>
    </div>

    <form method="post" action="<?= e(BASE_PATH) ?>/admission/encode/bulk" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-8">
          <label class="form-label small">Spreadsheet File</label>
          <input class="form-control" type="file" name="bulk_file" accept=".csv,.xlsx" required>
        </div>
        <div class="col-12 col-lg-4 d-grid">
          <button class="btn btn-primary" type="submit">Upload and Import</button>
        </div>
      </div>
      <div class="form-text mt-2">Supported formats: `.csv`. The importer uses `Application Number` as the main student key.</div>
    </form>
  </div>
</div>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/encode">
  <div class="row g-2 align-items-end">
    <div class="col-12">
      <label class="form-label small">Search Students</label>
    </div>
    <div class="col-12 col-xl-8">
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or application number">
    </div>
    <div class="col-6 col-md-3 col-xl-2 d-grid">
      <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/encode">Clear Filters</a>
    </div>
    <div class="col-6 col-md-3 col-xl-2 d-grid">
      <button class="btn btn-primary" type="submit">Apply Search</button>
    </div>
  </div>
</form>

<?php if (!empty($students)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($students as $s): ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
              <div class="text-muted small">Application No.: <?= e((string)($s['application_number'] ?? 'Not provided')) ?></div>
            </div>
          </div>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/encode/edit?id=<?= (int)$s['id'] ?>"><?= !empty($s['has_scores']) ? 'Continue Encoding' : 'Input Scores' ?></a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Application Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td class="fw-semibold"><?= e((string)($s['application_number'] ?? 'Not provided')) ?></td>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e($s['email']) ?></td>
              <td><span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>"><?= e(studentStatusLabel((string)($s['status'] ?? 'pending'))) ?></span></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/encode/edit?id=<?= (int)$s['id'] ?>"><?= !empty($s['has_scores']) ? 'Continue Encoding' : 'Input Scores' ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No students with pending exam evaluation found.</div>
  </div>
<?php endif; ?>
