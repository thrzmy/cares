<?php
declare(strict_types=1);
$success = flash('success');
$error = flash('error');
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Student Management</h4>
    <p class="page-subtitle">Manage student details, IDs, and admission status.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students/create">Add Student</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission">Back to Dashboard</a>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/students">
  <?php if (($recordScopeFilter ?? 'active') !== 'active'): ?>
    <input type="hidden" name="record_scope" value="<?= e((string)$recordScopeFilter) ?>">
  <?php endif; ?>
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-8">
      <label class="form-label small">Search Students</label>
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name, email, or ID number">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label small">Admission Status</label>
      <select class="form-select" name="status">
        <option value="">All statuses</option>
        <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="admitted" <?= ($statusFilter ?? '') === 'admitted' ? 'selected' : '' ?>>Admitted</option>
        <option value="rejected" <?= ($statusFilter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        <option value="waitlisted" <?= ($statusFilter ?? '') === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
      </select>
    </div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
        <div class="d-grid d-md-block">
          <?php if (($recordScopeFilter ?? 'active') === 'archived'): ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students">Back to Default View</a>
          <?php else: ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students?record_scope=archived">Archived Students</a>
          <?php endif; ?>
        </div>
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/students<?= ($recordScopeFilter ?? 'active') !== 'active' ? '?record_scope=' . urlencode((string)$recordScopeFilter) : '' ?>">Clear Filters</a>
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
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($s['name']) ?></div>
              <div class="text-muted small"><?= e($s['email']) ?></div>
              <div class="text-muted small">Student ID: <?= e((string)($s['id_number'] ?? 'Not provided')) ?></div>
            </div>
            <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>">
              <?= e(ucfirst((string)($s['status'] ?? 'pending'))) ?>
            </span>
          </div>
          <?php if ($isArchived): ?>
            <button
              class="btn btn-success btn-sm w-100 mt-3"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#restoreStudentModalAdmission"
              data-id="<?= (int)$s['id'] ?>"
              data-name="<?= e($s['name']) ?>">
              Restore Record
            </button>
          <?php else: ?>
            <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit Record</a>
            <button
              class="btn btn-outline-dark btn-sm w-100 mt-2"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#archiveStudentModalAdmission"
              data-id="<?= (int)$s['id'] ?>"
              data-name="<?= e($s['name']) ?>">
              Archive Record
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Student ID</th>
            <th>Email</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <?php $isArchived = (int)($s['is_deleted'] ?? 0) === 1; ?>
            <tr>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td><?= e((string)($s['id_number'] ?? 'Not provided')) ?></td>
              <td><?= e($s['email']) ?></td>
              <td>
                <span class="badge <?= e(studentStatusBadgeClass((string)($s['status'] ?? 'pending'))) ?>">
                  <?= e(ucfirst((string)($s['status'] ?? 'pending'))) ?>
                </span>
                <?php if ($isArchived): ?>
                  <span class="badge text-bg-dark ms-1">Archived</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <?php if ($isArchived): ?>
                  <button
                    class="btn btn-success btn-sm"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#restoreStudentModalAdmission"
                    data-id="<?= (int)$s['id'] ?>"
                    data-name="<?= e($s['name']) ?>">
                    Restore
                  </button>
                <?php else: ?>
                  <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/admission/students/edit?id=<?= (int)$s['id'] ?>">Edit Record</a>
                  <button
                    class="btn btn-outline-dark btn-sm"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#archiveStudentModalAdmission"
                    data-id="<?= (int)$s['id'] ?>"
                    data-name="<?= e($s['name']) ?>">
                    Archive
                  </button>
                <?php endif; ?>
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

<div class="modal fade" id="archiveStudentModalAdmission" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/admission/students/archive">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="archiveStudentIdAdmission">
        <div class="modal-header">
          <h5 class="modal-title">Archive Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Archive <strong id="archiveStudentNameAdmission">this student</strong>?</p>
          <p class="text-muted small mb-0">The student will be hidden from the default list.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-dark">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="restoreStudentModalAdmission" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/admission/students/restore">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="restoreStudentIdAdmission">
        <div class="modal-header">
          <h5 class="modal-title">Restore Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Restore <strong id="restoreStudentNameAdmission">this student</strong>?</p>
          <p class="text-muted small mb-0">This will return the student to the default list.</p>
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
  const archiveStudentModalAdmission = document.getElementById('archiveStudentModalAdmission');
  if (archiveStudentModalAdmission) {
    archiveStudentModalAdmission.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      archiveStudentModalAdmission.querySelector('#archiveStudentIdAdmission').value = button.getAttribute('data-id') || '';
      archiveStudentModalAdmission.querySelector('#archiveStudentNameAdmission').textContent = button.getAttribute('data-name') || 'this student';
    });
  }

  const restoreStudentModalAdmission = document.getElementById('restoreStudentModalAdmission');
  if (restoreStudentModalAdmission) {
    restoreStudentModalAdmission.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      restoreStudentModalAdmission.querySelector('#restoreStudentIdAdmission').value = button.getAttribute('data-id') || '';
      restoreStudentModalAdmission.querySelector('#restoreStudentNameAdmission').textContent = button.getAttribute('data-name') || 'this student';
    });
  }
</script>
