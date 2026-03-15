<?php
declare(strict_types=1);
$activeSemester = $activeSemester ?? null;
?>

<style>
:root {
    --cares-maroon: #8c1d22;
}

.btn-maroon {
    background-color: var(--cares-maroon);
    color: #ffffff;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(140, 29, 34, 0.15);
}

.btn-maroon:hover {
    background-color: #72161b;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(140, 29, 34, 0.25);
}

.btn-maroon:active {
    transform: translateY(0);
}
</style>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Academic Year & Semester</h4>
    <p class="page-subtitle">Manage academic years and semesters. Set the active/current semester.</p>
  </div>
  <div class="page-actions"></div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($activeSemester): ?>
  <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3" style="border-radius: 12px; background: rgba(13, 110, 253, 0.05); color: #0d6efd;">
    <div class="bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
      <i class="fa-solid fa-calendar-check" style="font-size: 1.2rem;"></i>
    </div>
    <div>
      <div class="small fw-bold text-uppercase opacity-75" style="letter-spacing: 0.5px; font-size: 0.65rem;">Current Active Semester</div>
      <div class="fw-bold h6 mb-0"><?= e($activeSemester['sy_name']) ?> â€” <?= e($activeSemester['semester_name']) ?></div>
    </div>
  </div>
<?php else: ?>
  <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center gap-3" style="border-radius: 12px; background: rgba(255, 193, 7, 0.05); color: #856404;">
    <div class="bg-warning bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
      <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
    </div>
    <div>
      <div class="small fw-bold text-uppercase opacity-75" style="letter-spacing: 0.5px; font-size: 0.65rem;">Configuration Required</div>
      <div class="fw-bold h6 mb-0">No active semester is set. Please set one below.</div>
    </div>
  </div>
<?php endif; ?>

<?php $hasActiveSchoolYear = $hasActiveSchoolYear ?? false; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <?php if ($hasActiveSchoolYear): ?>
  <button class="btn btn-secondary px-4 py-2 d-flex align-items-center gap-2 fw-bold disabled" type="button" style="border-radius: 12px; font-size: 0.9rem; opacity: 0.6; cursor: not-allowed;" title="Archive the current Academic Year first before adding a new one">
    <i class="fa-solid fa-plus-circle" style="font-size: 1.1rem;"></i>
    <span>New Academic Year / Semester</span>
  </button>
  <span class="text-muted small ms-2"><i class="fa-solid fa-circle-info me-1"></i>Archive the current Academic Year to add a new one</span>
  <?php else: ?>
  <button class="btn btn-maroon px-4 py-2 d-flex align-items-center gap-2 fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal" style="border-radius: 12px; font-size: 0.9rem;">
    <i class="fa-solid fa-plus-circle" style="font-size: 1.1rem;"></i>
    <span>New Academic Year / Semester</span>
  </button>
  <?php endif; ?>
</div>

<?php if (!empty($activeSchoolYear) || !empty($archivedSchoolYears)): ?>

  <?php if (!empty($activeSchoolYear)): ?>
    <h5 class="fw-bold mb-3 mt-4 text-dark"><i class="fa-solid fa-bolt text-warning me-2"></i>Current Academic Year</h5>
    <div class="row g-3 mb-4">
      <?php
      $sy = $activeSchoolYear;
      $syId = (int)($sy['id'] ?? 0);
      $syName = $sy['name'] ?? '';
      $semesters = $semestersByYear[$syId] ?? [];
      $isActiveSY = true;
      ?>
      <div class="col-12">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden; border-left: 5px solid var(--cares-maroon) !important;">
          <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-maroon bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-calendar-alt text-maroon"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0 text-dark"><?= e($syName) ?></h5>
                <div class="d-flex gap-2 mt-1">
                  <span class="badge bg-maroon bg-opacity-10 text-maroon border-0 py-1 px-2" style="font-size: 0.6rem; font-weight: 800; letter-spacing: 0.5px;">Current Active Year</span>
                </div>
              </div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-light btn-sm text-warning shadow-sm" type="button"
                data-bs-toggle="modal" data-bs-target="#archiveAcademicYearModal"
                data-id="<?= $syId ?>" data-name="<?= e($syName) ?>" title="Archive All Records" style="border-radius: 6px;">
                <i class="fa-solid fa-box-archive"></i>
              </button>
              <button class="btn btn-light btn-sm text-primary shadow-sm" type="button"
                data-bs-toggle="modal" data-bs-target="#addAcademicYearModal" title="Add Semester to this Year" style="border-radius: 6px;">
                <i class="fa-solid fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr style="border-bottom: 2px solid #f1f1f1;">
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Semester Name</th>
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Status Indicator</th>
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted text-end border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Management Actions</th>
                  </tr>
                </thead>
                <tbody style="border-top: 0;">
                  <?php if (!empty($semesters)): ?>
                    <?php foreach ($semesters as $sem): ?>
                      <?php
                        $isSemActive = (int)$sem['is_active'] === 1;
                        $isSemArchived = (int)($sem['is_archived'] ?? 0) === 1;
                      ?>
                      <tr class="<?= $isSemArchived ? 'bg-light opacity-50' : '' ?>">
                        <td class="px-4 py-3">
                          <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold text-dark fs-6"><?= e($sem['name']) ?></div>
                            <?php if ($isSemArchived): ?>
                              <span class="badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.6rem;"><i class="fa-solid fa-lock me-1"></i>Archived</span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <?php if ($isSemActive): ?>
                            <span class="badge bg-maroon bg-opacity-10 px-3 py-2" style="border-radius: 8px; font-size: 0.75rem; color: var(--cares-maroon);"><i class="fa-solid fa-circle-check me-2"></i>Current Semester</span>
                          <?php elseif ($isSemArchived): ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2" style="border-radius: 8px; font-size: 0.75rem;"><i class="fa-solid fa-box-archive me-2"></i>Archived</span>
                          <?php else: ?>
                            <span class="badge bg-light text-muted border px-3 py-2" style="border-radius: 8px; font-size: 0.75rem;"><i class="fa-solid fa-circle me-2 opacity-50"></i>Inactive</span>
                          <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-end">
                          <div class="d-flex justify-content-end gap-2 align-items-center">
                            <?php if (!$isSemActive && !$isSemArchived): ?>
                              <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/set-active" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="semester_id" value="<?= (int)$sem['id'] ?>">
                                <button type="submit" class="btn btn-maroon btn-sm px-4 py-2 shadow-sm fw-bold" style="border-radius: 10px; font-size: 0.75rem;">Set Primary</button>
                              </form>
                              <form method="post" action="<?= e(BASE_PATH) ?>/administrator/semesters/archive-semester" class="d-inline" onsubmit="return confirm('Archive this semester? This will also archive all students associated with it.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="semester_id" value="<?= (int)$sem['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm px-3 py-2 shadow-sm" title="Archive Semester" style="border-radius: 10px; border-color: #ffd43b; color: #856404;">
                                  <i class="fa-solid fa-box-archive"></i>
                                </button>
                              </form>
                            <?php elseif ($isSemActive): ?>
                              <span class="badge bg-emerald text-white px-3 py-2 shadow-sm" style="border-radius: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">CURRENT</span>
                            <?php elseif ($isSemArchived): ?>
                              <span class="text-muted small italic opacity-75">Immutable Archive</span>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-muted small text-center py-5">
                        <i class="fa-solid fa-calendar-xmark d-block mb-2 opacity-25 h4"></i>
                        No semesters initialized for this academic year.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($archivedSchoolYears)): ?>
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
      <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-box-archive text-secondary me-2"></i>Archived Academic Years</h5>
      <button class="btn btn-outline-secondary btn-sm px-3 py-2 fw-bold shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#archivedAYSection" aria-expanded="false" aria-controls="archivedAYSection" style="border-radius: 10px; font-size: 0.8rem;">
        <i class="fa-solid fa-eye me-1"></i> View Archives
      </button>
    </div>
    <div class="collapse" id="archivedAYSection">
    <div class="row g-3 mb-4">
      <?php foreach ($archivedSchoolYears as $sy): ?>
        <?php
        $syId = (int)$sy['id'];
        $semesters = $semestersByYear[$syId] ?? [];
        ?>
      <div class="col-12">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
          <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-maroon bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-calendar-alt text-maroon"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0 text-dark"><?= e($sy['name']) ?></h5>
                <div class="d-flex gap-2 mt-1">
                  <span class="badge bg-secondary-subtle text-secondary py-1 px-2" style="font-size: 0.6rem; font-weight: 800; letter-spacing: 0.5px;">Archived</span>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr style="border-bottom: 2px solid #f1f1f1;">
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Semester Name</th>
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Status Indicator</th>
                    <th class="px-4 py-2 text-uppercase small fw-bold text-muted text-end border-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Management Actions</th>
                  </tr>
                </thead>
                <tbody style="border-top: 0;">
                  <?php if (!empty($semesters)): ?>
                    <?php foreach ($semesters as $sem): ?>
                      <tr class="bg-light opacity-50">
                        <td class="px-4 py-3">
                          <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold text-dark fs-6"><?= e($sem['name']) ?></div>
                            <span class="badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.6rem;"><i class="fa-solid fa-lock me-1"></i>Archived</span>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2" style="border-radius: 8px; font-size: 0.75rem;"><i class="fa-solid fa-box-archive me-2"></i>Archived</span>
                        </td>
                        <td class="px-4 py-3 text-end">
                          <span class="text-muted small italic opacity-75">Immutable Archive</span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-muted small text-center py-5">
                        <i class="fa-solid fa-calendar-xmark d-block mb-2 opacity-25 h4"></i>
                        No semesters were in this academic year.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
  <?php endif; ?>

<?php else: ?>
  <div class="card shadow-sm border-0 bg-light">
    <div class="card-body text-muted text-center py-5">
      <i class="fa-solid fa-calendar-days mb-3 d-block" style="font-size: 2rem; color: #ddd;"></i>
      No academic years found. Add one to get started.
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
        <div class="modal-footer border-top-0 px-4 pb-4">
          <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark" style="border-radius: 8px;">Archive Academic Year</button>
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
</script>
