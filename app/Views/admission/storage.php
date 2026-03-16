<?php
declare(strict_types=1);
$success = $success ?? null;
$error = $error ?? null;
$recordScopeFilter = (string)($recordScopeFilter ?? 'active');
$activeSemester = $activeSemester ?? null;
$schoolYearFilter = (int)($schoolYearFilter ?? 0);
$semesterFilter = (int)($semesterFilter ?? 0);
$archivedSchoolYears = $archivedSchoolYears ?? [];
$archivedSemesters = $archivedSemesters ?? [];
$archivedSemestersByYear = $archivedSemestersByYear ?? [];
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Result Storage</h4>
    <p class="page-subtitle">View and edit students with recorded scores.</p>
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

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/storage">
  <?php if ($recordScopeFilter !== 'active'): ?>
    <input type="hidden" name="record_scope" value="<?= e($recordScopeFilter) ?>">
  <?php endif; ?>
  <div class="row g-2 align-items-end">
  <div class="col-12 col-md-8">
    <label class="form-label small">Search</label>
    <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or application number">
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label small">Exam Result</label>
    <select class="form-select" name="status">
      <option value="">All statuses</option>
      <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
      <option value="passed" <?= ($statusFilter ?? '') === 'passed' ? 'selected' : '' ?>>Passed</option>
      <option value="failed" <?= ($statusFilter ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
    </select>
  </div>
  </div>
  <?php if ($recordScopeFilter === 'archived'): ?>
    <div class="row g-2 align-items-end mt-1">
      <div class="col-12 col-md-6">
        <label class="form-label small">School Year</label>
        <select class="form-select" id="archivedStorageSchoolYearFilter" name="school_year_id">
          <option value="">All academic years</option>
          <?php foreach ($archivedSchoolYears as $schoolYear): ?>
            <option value="<?= (int)$schoolYear['id'] ?>" <?= $schoolYearFilter === (int)$schoolYear['id'] ? 'selected' : '' ?>>
              <?= e((string)$schoolYear['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label small">Semester</label>
        <select class="form-select" id="archivedStorageSemesterFilter" name="semester_id">
          <option value="">All semesters</option>
          <?php foreach ($archivedSemesters as $semester): ?>
            <option value="<?= (int)$semester['id'] ?>" <?= $semesterFilter === (int)$semester['id'] ? 'selected' : '' ?>>
              <?= e((string)$semester['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  <?php endif; ?>
  <div class="row g-2 mt-1">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
        <div class="d-grid d-md-block">
          <?php if ($recordScopeFilter === 'archived'): ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/storage">Back to Default View</a>
          <?php else: ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/storage?record_scope=archived">View Archived Students</a>
          <?php endif; ?>
        </div>
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/storage<?= $recordScopeFilter !== 'active' ? '?record_scope=' . urlencode($recordScopeFilter) : '' ?>">Clear Filters</a>
          <button class="btn btn-primary" type="submit">Apply Filters</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if (!empty($students)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($students as $s): ?>
      <?php $isArchived = (int)($s['is_deleted'] ?? 0) === 1 || (int)($s['is_archived'] ?? 0) === 1; ?>
      <div class="card storage-mobile-card mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
              <div class="text-muted small">Application No.: <?= e((string)($s['application_number'] ?? 'Not set')) ?></div>
              <div class="text-muted small">Application Status: <?= e(studentApplicationStatusLabel((string)($s['application_status'] ?? 'new_student'))) ?></div>
              <div class="text-muted small">Screening Status: <?= e(studentScreeningStatusLabel((string)($s['screening_status'] ?? 'pending'))) ?></div>
              <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
                <div class="text-muted small"><?= e(trim((string)($s['school_year_name'] ?? 'Not assigned') . ' - ' . (string)($s['semester_name'] ?? 'No semester'))) ?></div>
              <?php endif; ?>
            </div>
            <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
              <span class="badge text-bg-dark">Archived</span>
            <?php else: ?>
              <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>">
                <?= e(studentStatusLabel((string)($s['status'] ?? 'pending'))) ?>
              </span>
            <?php endif; ?>
          </div>
          <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/storage/edit?id=<?= (int)$s['id'] ?>">Edit Scores</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive storage-table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Application Number</th>
            <th>Email</th>
            <th><?= $recordScopeFilter === 'archived' ? 'Academic Year & Semester' : 'Screening / Exam Result' ?></th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <?php $isArchived = (int)($s['is_deleted'] ?? 0) === 1 || (int)($s['is_archived'] ?? 0) === 1; ?>
            <tr>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e((string)($s['application_number'] ?? 'Not set')) ?></td>
              <td><?= e($s['email']) ?></td>
              <td>
                <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
                  <div class="fw-semibold"><?= e(trim((string)($s['school_year_name'] ?? 'Not assigned') . ' - ' . (string)($s['semester_name'] ?? 'No semester'))) ?></div>
                <?php else: ?>
                  <div class="fw-semibold"><?= e(studentScreeningStatusLabel((string)($s['screening_status'] ?? 'pending'))) ?></div>
                  <div class="text-muted small">Exam Result: <?= e(studentStatusLabel((string)($s['status'] ?? 'pending'))) ?></div>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/storage/edit?id=<?= (int)$s['id'] ?>">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No students found.</div>
  </div>
<?php endif; ?>

<?php if ($recordScopeFilter === 'archived'): ?>
  <script>
    (() => {
      const schoolYearSelect = document.getElementById('archivedStorageSchoolYearFilter');
      const semesterSelect = document.getElementById('archivedStorageSemesterFilter');
      const semestersByYear = <?= json_encode($archivedSemestersByYear, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
      const selectedSemester = <?= json_encode((string)$semesterFilter, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

      if (!schoolYearSelect || !semesterSelect) {
        return;
      }

      const renderSemesters = (yearId, keepSelected) => {
        const options = semestersByYear[yearId] || [];
        const currentValue = keepSelected ? selectedSemester : '';

        semesterSelect.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = options.length > 0 ? 'All semesters' : 'Select school year first';
        semesterSelect.appendChild(defaultOption);

        options.forEach((semester) => {
          const option = document.createElement('option');
          option.value = String(semester.id);
          option.textContent = semester.name;
          if (currentValue !== '' && currentValue === String(semester.id)) {
            option.selected = true;
          }
          semesterSelect.appendChild(option);
        });

        semesterSelect.disabled = yearId === '';
      };

      renderSemesters(schoolYearSelect.value, true);
      schoolYearSelect.addEventListener('change', () => renderSemesters(schoolYearSelect.value, false));
    })();
  </script>
<?php endif; ?>
