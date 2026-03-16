<?php
declare(strict_types=1);
$success = flash('success');
$error = flash('error');
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
    <h4 class="fw-bold mb-1">Student Management</h4>
    <p class="page-subtitle">Manage admission student profiles, course choices, and academic details.</p>
  </div>
</div>

<?php if (!empty($activeSemester)): ?>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-4">
    <div>
      <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Current Academic Year</h6>
      <div class="fw-semibold"><?= e((string)($activeSemester['school_year_name'] ?? '')) ?></div>
    </div>
    <div class="d-grid d-md-block">
      <a class="btn btn-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students/create">Add Student</a>
    </div>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/students">
  <?php if ($recordScopeFilter !== 'active'): ?>
    <input type="hidden" name="record_scope" value="<?= e((string)$recordScopeFilter) ?>">
  <?php endif; ?>
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-8">
      <label class="form-label small">Search Students</label>
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or application number">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label small">Application Type</label>
      <select class="form-select" name="application_status">
        <option value="">All application types</option>
        <option value="new_student" <?= ($applicationStatusFilter ?? '') === 'new_student' ? 'selected' : '' ?>>New Student</option>
        <option value="transferee" <?= ($applicationStatusFilter ?? '') === 'transferee' ? 'selected' : '' ?>>Transferee</option>
        <option value="returning_student" <?= ($applicationStatusFilter ?? '') === 'returning_student' ? 'selected' : '' ?>>Returning Student</option>
        <option value="adult_learner" <?= ($applicationStatusFilter ?? '') === 'adult_learner' ? 'selected' : '' ?>>Adult Learner</option>
        <option value="old_curriculum" <?= ($applicationStatusFilter ?? '') === 'old_curriculum' ? 'selected' : '' ?>>Old Curriculum</option>
        <option value="als_passer" <?= ($applicationStatusFilter ?? '') === 'als_passer' ? 'selected' : '' ?>>ALS Passer</option>
      </select>
    </div>
  </div>
  <?php if ($recordScopeFilter === 'archived'): ?>
    <div class="row g-2 align-items-end mt-1">
      <div class="col-12 col-md-6">
        <label class="form-label small">School Year</label>
        <select class="form-select" id="archivedAdmissionSchoolYearFilter" name="school_year_id">
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
        <select class="form-select" id="archivedAdmissionSemesterFilter" name="semester_id">
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
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students">Back to Default View</a>
          <?php else: ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students?record_scope=archived">View Archived Students</a>
          <?php endif; ?>
        </div>
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students<?= $recordScopeFilter !== 'active' ? '?record_scope=' . urlencode((string)$recordScopeFilter) : '' ?>">Clear Filters</a>
          <button class="btn btn-primary" type="submit">Apply Filters</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if (!empty($students)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($students as $s): ?>
      <?php $isArchived = (int)($s['is_deleted'] ?? 0) === 1; ?>
      <?php
      ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
              <div class="text-muted small">Application No.: <?= e((string)($s['application_number'] ?? 'Not provided')) ?></div>
              <div class="text-muted small">Application Type: <?= e(studentApplicationStatusLabel((string)($s['application_status'] ?? 'new_student'))) ?></div>
              <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
                <div class="text-muted small"><?= e(trim((string)($s['school_year_name'] ?? 'Not assigned') . ' - ' . (string)($s['semester_name'] ?? 'No semester'))) ?></div>
              <?php endif; ?>
            </div>
            <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
              <span class="badge text-bg-dark">Archived</span>
            <?php else: ?>
              <span class="badge text-bg-light border text-dark">
                <?= e(studentApplicationStatusLabel((string)($s['application_status'] ?? 'new_student'))) ?>
              </span>
            <?php endif; ?>
          </div>
          <?php if (!$isArchived): ?>
            <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit Record</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light" style="position: sticky; top: 0; z-index: 2;">
          <tr>
            <th>Application Number</th>
            <th>Student</th>
            <th>Email</th>
            <th><?= $recordScopeFilter === 'archived' ? 'Academic Year & Semester' : 'Application Status' ?></th>
            <?php if ($recordScopeFilter !== 'archived'): ?>
              <th class="text-end">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <?php $isArchived = (int)($s['is_deleted'] ?? 0) === 1; ?>
            <tr>
              <td class="fw-semibold"><?= e((string)($s['application_number'] ?? 'Not provided')) ?></td>
              <td><?= e($s['name']) ?></td>
              <td><?= e($s['email']) ?></td>
              <td>
                <?php if ($recordScopeFilter === 'archived' && $isArchived): ?>
                  <div class="text-muted small"><?= e(trim((string)($s['school_year_name'] ?? 'Not assigned') . ' - ' . (string)($s['semester_name'] ?? 'No semester'))) ?></div>
                <?php else: ?>
                  <div class="text-muted small"><?= e(studentApplicationStatusLabel((string)($s['application_status'] ?? 'new_student'))) ?></div>
                <?php endif; ?>
              </td>
              <?php if ($recordScopeFilter !== 'archived'): ?>
                <td class="text-end">
                  <?php if (!$isArchived): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit Record</a>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
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

<?php if ($recordScopeFilter === 'archived'): ?>
  <script>
    (() => {
      const schoolYearSelect = document.getElementById('archivedAdmissionSchoolYearFilter');
      const semesterSelect = document.getElementById('archivedAdmissionSemesterFilter');
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
