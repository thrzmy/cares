<?php
declare(strict_types=1);
$success = flash('success');
$error = flash('error');
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Account Management</h4>
    <p class="page-subtitle">Manage system users (administrators and admissions staff).</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts/create">Create Account</a>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator">Back to Dashboard</a>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/administrator/accounts">
  <?php if (($recordScopeFilter ?? 'active') !== 'active'): ?>
    <input type="hidden" name="record_scope" value="<?= e((string)$recordScopeFilter) ?>">
  <?php endif; ?>
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-5">
      <label class="form-label small">Search Accounts</label>
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by name or email">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label small">User Role</label>
      <select class="form-select" name="role">
        <option value="">All roles</option>
        <option value="administrator" <?= ($roleFilter ?? '') === 'administrator' ? 'selected' : '' ?>>Administrator</option>
        <option value="admission" <?= ($roleFilter ?? '') === 'admission' ? 'selected' : '' ?>>Admissions</option>
      </select>
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label small">Verification Status</label>
      <select class="form-select" name="status">
        <option value="">All statuses</option>
        <option value="pending" <?= ($statusFilter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="verified" <?= ($statusFilter ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
        <option value="rejected" <?= ($statusFilter ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label small">Access</label>
      <select class="form-select" name="access">
        <option value="all" <?= ($accessFilter ?? '') === 'all' ? 'selected' : '' ?>>All Accesses</option>
        <option value="active" <?= ($accessFilter ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="disabled" <?= ($accessFilter ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
      </select>
    </div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-12">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
        <div class="d-grid d-md-block">
          <?php if (($recordScopeFilter ?? 'active') === 'archived'): ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/accounts">Back to Default View</a>
          <?php else: ?>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/accounts?record_scope=archived">Archived Accounts</a>
          <?php endif; ?>
        </div>
        <div class="d-grid d-md-flex gap-2 justify-content-md-end">
          <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/accounts<?= ($recordScopeFilter ?? 'active') !== 'active' ? '?record_scope=' . urlencode((string)$recordScopeFilter) : '' ?>">Clear Filters</a>
          <button class="btn btn-primary" type="submit">Apply Filters</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php if (!empty($users)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($users as $u): ?>
      <?php
      $isActive = (int)$u['is_active'] === 1;
      $isArchived = (int)($u['is_deleted'] ?? 0) === 1;
      $status = (string)($u['account_status'] ?? 'verified');
      $isSystemRole = in_array($u['role'], ['administrator', 'admission'], true);
      $roleLabel = $u['role'] === 'admission'
        ? 'Admissions'
        : ($u['role'] === 'administrator' ? 'Administrator' : ucfirst((string)$u['role']));
      $roleBadgeClass = $u['role'] === 'administrator'
        ? 'text-bg-light border'
        : 'text-bg-light border';
      $roleBadgeStyle = $u['role'] === 'administrator'
        ? 'color: var(--cares-maroon); background-color: rgba(111, 17, 25, 0.10) !important; border-color: rgba(111, 17, 25, 0.32) !important;'
        : '';
      ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($u['name']) ?></div>
              <div class="text-muted small"><?= e($u['email']) ?></div>
            </div>
            <span class="badge <?= e($roleBadgeClass) ?>"<?= $roleBadgeStyle !== '' ? ' style="' . e($roleBadgeStyle) . '"' : '' ?>><?= e($roleLabel) ?></span>
          </div>

          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge <?= $status === 'verified' ? 'text-bg-success' : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') ?>">
              <?= e(ucfirst($status)) ?>
            </span>
            <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
              <?= $isActive ? 'Active' : 'Disabled' ?>
            </span>
            <?php if ($isArchived): ?>
              <span class="badge text-bg-dark">Archived</span>
            <?php endif; ?>
          </div>

          <?php if ($status === 'rejected'): ?>
            <div class="text-muted small mt-2">Rejection reason: <?= e((string)($u['rejection_reason'] ?? '')) ?></div>
            <div class="text-muted small">Rejected by: <?= e((string)($u['rejected_by_name'] ?? '-')) ?></div>
            <div class="text-muted small">Rejected at: <?= e(!empty($u['rejected_at']) ? date('M j, Y H:i', strtotime((string)$u['rejected_at'])) : '-') ?></div>
          <?php elseif ($isArchived): ?>
            <div class="text-muted small mt-2">Deleted by: <?= e((string)($u['deleted_by_name'] ?? '-')) ?></div>
            <div class="text-muted small">Deleted at: <?= e(!empty($u['deleted_at']) ? date('M j, Y H:i', strtotime((string)$u['deleted_at'])) : '-') ?></div>
          <?php elseif ($status === 'verified'): ?>
            <div class="text-muted small mt-2">Verified by: <?= e((string)($u['verified_by_name'] ?? '-')) ?></div>
            <div class="text-muted small">Verified at: <?= e(!empty($u['verified_at']) ? date('M j, Y H:i', strtotime((string)$u['verified_at'])) : '-') ?></div>
          <?php endif; ?>

          <?php if ($isArchived): ?>
            <button
              class="btn btn-success btn-sm w-100 mt-3"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#restoreAccountModal"
              data-id="<?= (int)$u['id'] ?>"
              data-name="<?= e($u['name']) ?>">
              Restore Account
            </button>
          <?php elseif ($status !== 'rejected'): ?>
            <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(BASE_PATH) ?>/administrator/accounts/edit?id=<?= (int)$u['id'] ?>">
              Edit Account
            </a>
          <?php else: ?>
            <button
              class="btn btn-danger btn-sm w-100 mt-3"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#reverifyModal"
              data-id="<?= (int)$u['id'] ?>"
              data-name="<?= e($u['name']) ?>">
              Re-verify Account
            </button>
          <?php endif; ?>

          <?php if (!$isArchived && $isSystemRole && $status === 'pending'): ?>
            <button
              class="btn btn-success btn-sm w-100 mt-2"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#verifyModal"
              data-id="<?= (int)$u['id'] ?>"
              data-name="<?= e($u['name']) ?>">
              Verify Account
            </button>
            <button
              class="btn btn-outline-danger btn-sm w-100 mt-2"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#rejectModal"
              data-id="<?= (int)$u['id'] ?>"
              data-name="<?= e($u['name']) ?>">
              Reject Account
            </button>
          <?php endif; ?>

          <?php if (!$isArchived): ?>
            <button
              class="btn btn-outline-dark btn-sm w-100 mt-2"
              type="button"
              data-bs-toggle="modal"
              data-bs-target="#archiveAccountModal"
              data-id="<?= (int)$u['id'] ?>"
              data-name="<?= e($u['name']) ?>">
              Archive Account
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
            <th>Email</th>
            <th>Role</th>
            <th>Verification Status</th>
            <th>Access</th>
            <th>Verification Details</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php
            $isActive = (int)$u['is_active'] === 1;
            $isArchived = (int)($u['is_deleted'] ?? 0) === 1;
            $status = (string)($u['account_status'] ?? 'verified');
            $isSystemRole = in_array($u['role'], ['administrator', 'admission'], true);
            $roleLabel = $u['role'] === 'admission'
              ? 'Admissions'
              : ($u['role'] === 'administrator' ? 'Administrator' : ucfirst((string)$u['role']));
            $roleBadgeClass = $u['role'] === 'administrator'
              ? 'text-bg-light border'
              : 'text-bg-light border';
            $roleBadgeStyle = $u['role'] === 'administrator'
              ? 'color: var(--cares-maroon); background-color: rgba(111, 17, 25, 0.10) !important; border-color: rgba(111, 17, 25, 0.32) !important;'
              : '';
            ?>
            <tr>
              <td class="fw-semibold"><?= e($u['name']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><span class="badge <?= e($roleBadgeClass) ?>"<?= $roleBadgeStyle !== '' ? ' style="' . e($roleBadgeStyle) . '"' : '' ?>><?= e($roleLabel) ?></span></td>
              <td>
                <span class="badge <?= $status === 'verified' ? 'text-bg-success' : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') ?>">
                  <?= e(ucfirst($status)) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                  <?= $isActive ? 'Active' : 'Disabled' ?>
                </span>
                <?php if ($isArchived): ?>
                  <span class="badge text-bg-dark ms-1">Archived</span>
                <?php endif; ?>
              </td>
              <td class="text-muted small">
                <?php if ($status === 'rejected'): ?>
                  <div>Rejection reason: <?= e((string)($u['rejection_reason'] ?? '')) ?></div>
                  <div>Rejected by: <?= e((string)($u['rejected_by_name'] ?? '-')) ?></div>
                  <div>Rejected at: <?= e(!empty($u['rejected_at']) ? date('M j, Y H:i', strtotime((string)$u['rejected_at'])) : '-') ?></div>
                <?php elseif ($isArchived): ?>
                  <div>Deleted by: <?= e((string)($u['deleted_by_name'] ?? '-')) ?></div>
                  <div>Deleted at: <?= e(!empty($u['deleted_at']) ? date('M j, Y H:i', strtotime((string)$u['deleted_at'])) : '-') ?></div>
                <?php elseif ($status === 'verified'): ?>
                  <div>Verified by: <?= e((string)($u['verified_by_name'] ?? '-')) ?></div>
                  <div>Verified at: <?= e(!empty($u['verified_at']) ? date('M j, Y H:i', strtotime((string)$u['verified_at'])) : '-') ?></div>
                <?php else: ?>
                  <div>Awaiting verification</div>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                  <?php if ($isArchived): ?>
                    <button
                      class="btn btn-success btn-sm"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#restoreAccountModal"
                      data-id="<?= (int)$u['id'] ?>"
                      data-name="<?= e($u['name']) ?>">
                      Restore
                    </button>
                  <?php elseif ($status !== 'rejected'): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_PATH) ?>/administrator/accounts/edit?id=<?= (int)$u['id'] ?>">Edit</a>
                  <?php else: ?>
                    <button
                      class="btn btn-danger btn-sm"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#reverifyModal"
                      data-id="<?= (int)$u['id'] ?>"
                      data-name="<?= e($u['name']) ?>">
                      Re-verify
                    </button>
                  <?php endif; ?>
                  <?php if (!$isArchived && $isSystemRole && $status === 'pending'): ?>
                    <button
                      class="btn btn-success btn-sm"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#verifyModal"
                      data-id="<?= (int)$u['id'] ?>"
                      data-name="<?= e($u['name']) ?>">
                      Verify
                    </button>
                    <button
                      class="btn btn-outline-danger btn-sm"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#rejectModal"
                      data-id="<?= (int)$u['id'] ?>"
                      data-name="<?= e($u['name']) ?>">
                      Reject
                    </button>
                  <?php endif; ?>
                  <?php if (!$isArchived): ?>
                    <button
                      class="btn btn-outline-dark btn-sm"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#archiveAccountModal"
                      data-id="<?= (int)$u['id'] ?>"
                      data-name="<?= e($u['name']) ?>">
                      Archive
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No matching accounts found.</div>
  </div>
<?php endif; ?>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../../partials/pagination.php';
?>

<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/verify">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="verifyAccountId">
        <div class="modal-header">
          <h5 class="modal-title">Verify Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Verify <strong id="verifyAccountName">this account</strong>?</p>
          <p class="text-muted small mb-0">This will activate access and allow login.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Verify</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="reverifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/verify">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="reverifyAccountId">
        <div class="modal-header">
          <h5 class="modal-title">Re-Verify Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Re-verify <strong id="reverifyAccountName">this account</strong>?</p>
          <p class="text-muted small mb-0">This will restore verified status and allow login.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm Re-Verify</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/reject">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="rejectAccountId">
        <div class="modal-header">
          <h5 class="modal-title">Reject Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Reject <strong id="rejectAccountName">this account</strong>?</p>
          <label class="form-label small">Rejection reason (optional)</label>
          <input class="form-control" type="text" name="reason" placeholder="Add a brief reason (optional)">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-danger">Confirm Rejection</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="archiveAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/archive">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="archiveAccountId">
        <div class="modal-header">
          <h5 class="modal-title">Archive Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Archive <strong id="archiveAccountName">this account</strong>?</p>
          <p class="text-muted small mb-0">The account will be hidden from default lists and access will be disabled.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-dark">Confirm Archive</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="restoreAccountModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= e(BASE_PATH) ?>/administrator/accounts/restore">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="restoreAccountId">
        <div class="modal-header">
          <h5 class="modal-title">Restore Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Restore <strong id="restoreAccountName">this account</strong>?</p>
          <p class="text-muted small mb-0">This will return the account to the default list and set Access to Active.</p>
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
  const verifyModal = document.getElementById('verifyModal');
  if (verifyModal) {
    verifyModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const idInput = verifyModal.querySelector('#verifyAccountId');
      const nameLabel = verifyModal.querySelector('#verifyAccountName');
      idInput.value = id || '';
      nameLabel.textContent = name || 'this account';
    });
  }

  const reverifyModal = document.getElementById('reverifyModal');
  if (reverifyModal) {
    reverifyModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const idInput = reverifyModal.querySelector('#reverifyAccountId');
      const nameLabel = reverifyModal.querySelector('#reverifyAccountName');
      idInput.value = id || '';
      nameLabel.textContent = name || 'this account';
    });
  }

  const rejectModal = document.getElementById('rejectModal');
  if (rejectModal) {
    rejectModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const idInput = rejectModal.querySelector('#rejectAccountId');
      const nameLabel = rejectModal.querySelector('#rejectAccountName');
      idInput.value = id || '';
      nameLabel.textContent = name || 'this account';
    });
  }

  const archiveAccountModal = document.getElementById('archiveAccountModal');
  if (archiveAccountModal) {
    archiveAccountModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      archiveAccountModal.querySelector('#archiveAccountId').value = id || '';
      archiveAccountModal.querySelector('#archiveAccountName').textContent = name || 'this account';
    });
  }

  const restoreAccountModal = document.getElementById('restoreAccountModal');
  if (restoreAccountModal) {
    restoreAccountModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      restoreAccountModal.querySelector('#restoreAccountId').value = id || '';
      restoreAccountModal.querySelector('#restoreAccountName').textContent = name || 'this account';
    });
  }
</script>
