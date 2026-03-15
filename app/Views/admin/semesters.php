<?php

declare(strict_types=1);
$activeSemester = $activeSemester ?? null;
$recordScopeFilter = (string)($recordScopeFilter ?? 'active');
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Academic Year & Semester</h4>
    <p class="page-subtitle">Manage academic years, semester status, and the currently active term.</p>
  </div>
  <div class="page-actions">
    <?php if ($activeSemester): ?>
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-3">
        <div>
          <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">Current Academic Year</h6>
          <div class="fw-semibold"><?= e((string)($activeSchoolYear['name'] ?? '')) ?></div>
        </div>
        <div class="d-grid d-md-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" type="button"
            data-bs-toggle="modal" data-bs-target="#archiveAcademicYearModal"
            data-id="<?= (int)($activeSchoolYear['id'] ?? 0) ?>" data-name="<?= e((string)($activeSchoolYear['name'] ?? '')) ?>" title="Archive Academic Year">
            Archive Academic Year
          </button>
          <button class="btn btn-primary btn-sm" type="button"
            data-bs-toggle="modal" data-bs-target="#addAcademicYearModal" title="Add Semester">
            Add Semester
          </button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if (!$activeSemester): ?>
  <div class="alert alert-warning mb-4">No active semester is set. Please set one below.</div>
<?php endif; ?>

<?php $hasActiveSchoolYear = $hasActiveSchoolYear ?? false; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-4">
  <div class="d-grid d-md-block">
    <?php if ($recordScopeFilter === 'archived'): ?>
      <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/semesters">Back to Default View</a>
    <?php else: ?>
      <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/semesters?record_scope=archived">View Archived Academic Years</a>
    <?php endif; ?>
  </div>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
    <?php if (!$hasActiveSchoolYear): ?>
      <div class="d-grid d-md-block">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">New Academic Year / Semester</button>
      </div>
    <?php endif; ?>
    <?php if ($hasActiveSchoolYear): ?>
      <div class="text-muted small">
        <i class="fa-solid fa-circle-info me-1"></i>Archive the current Academic Year to add a new one.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($activeSchoolYear) || !empty($archivedSchoolYears)): ?>
  <?php if ($recordScopeFilter !== 'archived' && !empty($activeSchoolYear)): ?>
    <div class="mb-4">
      <?php
      $sy = $activeSchoolYear;
      $syId = (int)($sy['id'] ?? 0);
      $syName = $sy['name'] ?? '';
      $semesters = $semestersByYear[$syId] ?? [];
      ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Semester Name</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($semesters)): ?>
              <?php foreach ($semesters as $sem): ?>
                <?php
                $isSemActive = (int)$sem['is_active'] === 1;
                $isSemArchived = (int)($sem['is_archived'] ?? 0) === 1;
                ?>
                <tr>
                  <td class="fw-semibold">
                    <?= e($sem['name']) ?>
                    <?php if ($isSemActive): ?>
                      <span class="badge text-bg-primary ms-2">Current</span>
                    <?php elseif ($isSemArchived): ?>
                      <span class="badge text-bg-dark ms-2">Archived</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <?php if (!$isSemActive && !$isSemArchived): ?>
                      <div class="d-flex justify-content-end gap-2">
                        <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/set-active" class="d-inline">
                          <?= csrfField() ?>
                          <input type="hidden" name="semester_id" value="<?= (int)$sem['id'] ?>">
                          <button type="submit" class="btn btn-outline-primary btn-sm">Set as Current</button>
                        </form>
                        <button
                          class="btn btn-outline-dark btn-sm"
                          type="button"
                          data-bs-toggle="modal"
                          data-bs-target="#archiveSemesterModal"
                          data-id="<?= (int)$sem['id'] ?>"
                          data-name="<?= e($sem['name']) ?>">
                          Archive
                        </button>
                      </div>
                    <?php elseif ($isSemActive): ?>
                      <span class="text-muted small">Currently active</span>
                    <?php else: ?>
                      <button
                        class="btn btn-outline-secondary btn-sm"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#restoreSemesterModal"
                        data-id="<?= (int)$sem['id'] ?>"
                        data-name="<?= e($sem['name']) ?>">
                        Restore
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" class="text-muted text-center py-4">No semesters initialized for this academic year.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($recordScopeFilter === 'archived' && !empty($archivedSchoolYears)): ?>
    <!-- <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="text-muted text-uppercase fw-bold mb-0" style="font-size: 0.75rem; letter-spacing: 1px;">Archived Academic Years</h6>
      <span class="badge bg-light text-muted border" style="font-size: 0.7rem;"><?= e((string)($pagination['total'] ?? 0)) ?> total</span>
    </div> -->
    <div class="d-block d-md-none">
      <?php foreach ($archivedSchoolYears as $sy): ?>
        <?php
        $syId = (int)$sy['id'];
        $semesters = $semestersByYear[$syId] ?? [];
        $semesterNames = array_map(static fn(array $sem): string => (string)($sem['name'] ?? ''), $semesters);
        ?>
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="fw-semibold"><?= e($sy['name']) ?></div>
            <div class="text-muted small mt-1">
              <?= !empty($semesterNames) ? e(implode(', ', $semesterNames)) : 'No semesters recorded.' ?>
            </div>
            <button
              class="btn btn-success btn-sm w-100 mt-3"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#restoreAcademicYearModal"
              data-id="<?= $syId ?>"
              data-name="<?= e($sy['name']) ?>">
              Restore Academic Year
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="d-none d-md-block">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Academic Year</th>
              <th>Semesters</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($archivedSchoolYears as $sy): ?>
              <?php
              $syId = (int)$sy['id'];
              $semesters = $semestersByYear[$syId] ?? [];
              $semesterNames = array_map(static fn(array $sem): string => (string)($sem['name'] ?? ''), $semesters);
              ?>
              <tr>
                <td class="fw-semibold">
                  <?= e($sy['name']) ?>
                  <span class="badge text-bg-dark ms-1">Archived</span>
                </td>
                <td><?= !empty($semesterNames) ? e(implode(', ', $semesterNames)) : 'No semesters recorded.' ?></td>
                <td class="text-end">
                  <button
                    class="btn btn-success btn-sm"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#restoreAcademicYearModal"
                    data-id="<?= $syId ?>"
                    data-name="<?= e($sy['name']) ?>">
                    Restore
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php
    $pagination = $pagination ?? null;
    require __DIR__ . '/../partials/pagination.php';
    ?>
  <?php endif; ?>

<?php else: ?>
  <div class="card shadow-sm border-0 bg-light">
    <div class="card-body text-muted text-center py-5">
      <i class="fa-solid fa-calendar-days mb-3 d-block" style="font-size: 2rem; color: #ddd;"></i>
      <?= $recordScopeFilter === 'archived' ? 'No archived academic years found.' : 'No academic years found. Add one to get started.' ?>
    </div>
  </div>
<?php endif; ?>

<!-- Add Semester Modal -->
<div class="modal fade" id="addAcademicYearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/store">
        <?= csrfField() ?>
        <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
          <h5 class="modal-title fw-bold text-maroon">Initialise New Semester</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 pt-3">
          <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.7rem;">Academic Year</label>
            <?php if (isset($activeSchoolYear)): ?>
              <input class="form-control shadow-sm py-2 bg-light text-muted" type="text" name="name" required value="<?= e($activeSchoolYear['name']) ?>" readonly style="border-radius: 10px; cursor: not-allowed;">
              <div class="form-text small mt-1"><i class="fa-solid fa-circle-info me-1"></i>Bound to active academic year. Archive it to create a new one.</div>
            <?php else: ?>
              <input class="form-control shadow-sm py-2" type="text" name="name" required placeholder="e.g. 2024-2025" style="border-radius: 10px;">
            <?php endif; ?>
          </div>

          <div class="mb-2">
            <label class="form-label small fw-bold text-muted text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.7rem;">Term Period</label>
            <select class="form-select shadow-sm py-2" name="semester" required style="border-radius: 10px;">
              <option value="" disabled selected>Select term...</option>
              <option value="1st Semester">1st Semester</option>
              <option value="2nd Semester">2nd Semester</option>
              <option value="Summer">Summer</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-top-0 p-4 pt-2">
          <button type="submit" class="btn btn-maroon w-100 py-3 fw-bold shadow-sm" style="border-radius: 12px;">Create Semester</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Academic Year Modal -->
<div class="modal fade" id="editAcademicYearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/update">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="editSyId">
        <div class="modal-header">
          <h5 class="modal-title">Edit Academic Year</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Academic Year Name <span class="required-asterisk">*</span></label>
          <input class="form-control" type="text" name="name" id="editSyName" required>
        </div>
        <div class="modal-footer border-top-0 px-4 pb-4">
          <button type="submit" class="btn btn-maroon w-100 py-2 fw-bold" style="border-radius: 8px;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Archive Academic Year Modal -->
<div class="modal fade" id="archiveAcademicYearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/archive">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="archiveSyId">
        <div class="modal-header">
          <h5 class="modal-title">Archive Academic Year</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Archive academic year <strong id="archiveSyName">this academic year</strong> and all its semesters?</p>
          <p class="text-muted small mt-2 mb-0">This will also archive all students currently associated with any semester in this academic year.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-dark">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Archive Semester Modal -->
<div class="modal fade" id="archiveSemesterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/archive-semester">
        <?= csrfField() ?>
        <input type="hidden" name="semester_id" id="archiveSemesterId">
        <div class="modal-header">
          <h5 class="modal-title">Archive Semester</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Archive <strong id="archiveSemesterName">this semester</strong>?</p>
          <p class="text-muted small mb-0">This will also archive students associated with this semester.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-dark">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Restore Semester Modal -->
<div class="modal fade" id="restoreSemesterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/restore-semester">
        <?= csrfField() ?>
        <input type="hidden" name="semester_id" id="restoreSemesterId">
        <div class="modal-header">
          <h5 class="modal-title">Restore Semester</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Restore <strong id="restoreSemesterName">this semester</strong> and its students?</p>
          <p class="text-muted small mt-2 mb-0">This will return the semester and its associated students to the active records.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Restore</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Restore Academic Year Modal -->
<div class="modal fade" id="restoreAcademicYearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/restore">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="restoreSyId">
        <div class="modal-header">
          <h5 class="modal-title">Restore Academic Year</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Restore academic year <strong id="restoreSyName">this academic year</strong> and all its semesters?</p>
          <p class="text-muted small mt-2 mb-0">This will also restore students archived under its semesters.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Restore</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const editSyModal = document.getElementById('editAcademicYearModal');
  if (editSyModal) {
    editSyModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      editSyModal.querySelector('#editSyId').value = btn.getAttribute('data-id') || '';
      editSyModal.querySelector('#editSyName').value = btn.getAttribute('data-name') || '';
    });
  }

  const archiveSyModal = document.getElementById('archiveAcademicYearModal');
  if (archiveSyModal) {
    archiveSyModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      archiveSyModal.querySelector('#archiveSyId').value = btn.getAttribute('data-id') || '';
      archiveSyModal.querySelector('#archiveSyName').textContent = btn.getAttribute('data-name') || 'this academic year';
    });
  }

  const archiveSemesterModal = document.getElementById('archiveSemesterModal');
  if (archiveSemesterModal) {
    archiveSemesterModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      archiveSemesterModal.querySelector('#archiveSemesterId').value = btn.getAttribute('data-id') || '';
      archiveSemesterModal.querySelector('#archiveSemesterName').textContent = btn.getAttribute('data-name') || 'this semester';
    });
  }

  const restoreSyModal = document.getElementById('restoreAcademicYearModal');
  if (restoreSyModal) {
    restoreSyModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      restoreSyModal.querySelector('#restoreSyId').value = btn.getAttribute('data-id') || '';
      restoreSyModal.querySelector('#restoreSyName').textContent = btn.getAttribute('data-name') || 'this academic year';
    });
  }

  const restoreSemesterModal = document.getElementById('restoreSemesterModal');
  if (restoreSemesterModal) {
    restoreSemesterModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      restoreSemesterModal.querySelector('#restoreSemesterId').value = btn.getAttribute('data-id') || '';
      restoreSemesterModal.querySelector('#restoreSemesterName').textContent = btn.getAttribute('data-name') || 'this semester';
    });
  }
</script>