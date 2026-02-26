<?php
declare(strict_types=1);

$logs = $logs ?? [];
$actionList = $actionList ?? [];

$badgeForAction = static function (string $action): string {
    if ($action === '') {
        return 'text-bg-light border';
    }

    $upper = strtoupper($action);
    if (str_contains($upper, 'ARCHIVE_ACCOUNT')) {
        return 'badge-log-archive-account';
    }
    if (str_contains($upper, 'RESTORE_ACCOUNT')) {
        return 'badge-log-restore-account';
    }
    if (str_contains($upper, 'ARCHIVE_STUDENT')) {
        return 'badge-log-archive-student';
    }
    if (str_contains($upper, 'RESTORE_STUDENT')) {
        return 'badge-log-restore-student';
    }
    if (str_contains($upper, 'DELETE') || str_contains($upper, 'REJECT')) {
        return 'text-bg-danger';
    }
    if (str_contains($upper, 'ARCHIVE')) {
        return 'text-bg-dark';
    }
    if (str_contains($upper, 'RESTORE')) {
        return 'text-bg-info';
    }
    if (str_contains($upper, 'CREATE') || str_contains($upper, 'REGISTER') || str_contains($upper, 'ENCODE')) {
        return 'text-bg-success';
    }
    if (str_contains($upper, 'VERIFY')) {
        return 'text-bg-success';
    }
    if (str_contains($upper, 'UPDATE')) {
        return 'text-bg-primary';
    }
    if (str_contains($upper, 'RESET') || str_contains($upper, 'FORCE')) {
        return 'text-bg-warning';
    }
    if (str_contains($upper, 'LOGIN')) {
        return 'text-bg-success';
    }
    if (str_contains($upper, 'LOGOUT')) {
        return 'text-bg-secondary';
    }

    return 'text-bg-light border';
};

$formatAction = static function (string $action): string {
    return ucwords(strtolower(str_replace('_', ' ', $action)));
};

$formatEntity = static function (string $entity): string {
    return ucwords(strtolower(str_replace('_', ' ', $entity)));
};
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Activity Logs</h4>
    <p class="page-subtitle">Review your recent activity and changes.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission">Back to Dashboard</a>
  </div>
</div>

<form class="mb-3" method="get" action="<?= e(BASE_PATH) ?>/admission/logs">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-lg-5">
      <label class="form-label small">Search Logs</label>
      <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by record or details">
    </div>
    <div class="col-12 col-md-4 col-lg-2">
      <label class="form-label small">From Date</label>
      <input class="form-control" type="date" name="start_date" value="<?= e((string)($startDate ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4 col-lg-2">
      <label class="form-label small">To Date</label>
      <input class="form-control" type="date" name="end_date" value="<?= e((string)($endDate ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4 col-lg-3">
      <label class="form-label small">Action Filter</label>
      <select class="form-select" name="action">
        <option value="">All actions</option>
        <?php foreach ($actionList as $action): ?>
          <option value="<?= e((string)$action) ?>" <?= ($actionFilter ?? '') === $action ? 'selected' : '' ?>>
            <?= e($formatAction((string)$action)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-12">
      <div class="d-grid d-md-flex justify-content-md-end gap-2">
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/admission/logs">Clear Filters</a>
        <button class="btn btn-primary" type="submit">Apply Filters</button>
      </div>
    </div>
  </div>
</form>

<?php if (!empty($logs)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($logs as $log): ?>
      <?php
      $action = (string)($log['action'] ?? '');
      $entity = (string)($log['entity'] ?? '');
      $entityId = $log['entity_id'] ?? null;
      $entityName = (string)($log['entity_name'] ?? '');
      $entityRef = (string)($log['entity_ref'] ?? '');
      $createdAt = !empty($log['created_at']) ? date('M j, Y H:i', strtotime((string)$log['created_at'])) : '-';
      ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="text-muted small">Occurred: <?= e($createdAt) ?></div>
            <span class="badge <?= e($badgeForAction($action)) ?>">
              <?= e($formatAction($action)) ?>
            </span>
          </div>

          <div class="text-muted small">
            <div>
              Record:
              <?php if ($entityName !== '' && ($entity === 'students' || $entity === 'users')): ?>
                <?= e($entityName) ?>
              <?php else: ?>
                <?= e($entity !== '' ? $formatEntity($entity) : '-') ?>
                <?php if ($entityName !== ''): ?>
                  -  <?= e($entityName) ?>
                <?php elseif ($entityId): ?>
                  #<?= e((string)$entityId) ?>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($entityRef !== '' && $entity !== 'students' && $entity !== 'users'): ?>
                <span class="text-muted">(<?= e($entityRef) ?>)</span>
              <?php endif; ?>
            </div>
            <div>Details: <?= e((string)($log['details'] ?? '-')) ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-none d-md-block">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Date/Time</th>
            <th>Action</th>
            <th>Record</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
            <?php
            $action = (string)($log['action'] ?? '');
            $entity = (string)($log['entity'] ?? '');
            $entityId = $log['entity_id'] ?? null;
            $entityName = (string)($log['entity_name'] ?? '');
            $entityRef = (string)($log['entity_ref'] ?? '');
            $createdAt = !empty($log['created_at']) ? date('M j, Y H:i', strtotime((string)$log['created_at'])) : '-';
            ?>
            <tr>
              <td class="text-muted small"><?= e($createdAt) ?></td>
              <td>
                <span class="badge <?= e($badgeForAction($action)) ?>">
                  <?= e($formatAction($action)) ?>
                </span>
              </td>
              <td class="text-muted small">
                <?php if ($entityName !== '' && ($entity === 'students' || $entity === 'users')): ?>
                  <?= e($entityName) ?>
                <?php else: ?>
                  <?= e($entity !== '' ? $formatEntity($entity) : '-') ?>
                  <?php if ($entityName !== ''): ?>
                    -  <?= e($entityName) ?>
                  <?php elseif ($entityId): ?>
                    #<?= e((string)$entityId) ?>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($entityRef !== '' && $entity !== 'students' && $entity !== 'users'): ?>
                  <span class="text-muted">(<?= e($entityRef) ?>)</span>
                <?php endif; ?>
              </td>
              <td class="text-muted small"><?= e((string)($log['details'] ?? '-')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="card-body text-muted">No activity logs found.</div>
  </div>
<?php endif; ?>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../partials/pagination.php';
?>
