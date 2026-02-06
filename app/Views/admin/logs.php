<?php
declare(strict_types=1);

$logs = $logs ?? [];
$actionList = $actionList ?? [];
$entityList = $entityList ?? [];

$badgeForAction = static function (string $action): string {
    if ($action === '') {
        return 'text-bg-light border';
    }

    $upper = strtoupper($action);
    if (str_contains($upper, 'DELETE') || str_contains($upper, 'REJECT')) {
        return 'text-bg-danger';
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
?>

<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Administrator</div>
    <h4 class="fw-bold mb-1">Monitor Logs</h4>
    <p class="page-subtitle">Review user activity and system events.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/administrator">Back to Dashboard</a>
  </div>
</div>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= e(BASE_PATH) ?>/administrator/logs">
  <div class="col-12 col-md-6">
    <label class="form-label small">Search</label>
    <input class="form-control" type="text" name="q" value="<?= e((string)($q ?? '')) ?>" placeholder="Search by user, action, entity, or details">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">From</label>
    <input class="form-control" type="date" name="start_date" value="<?= e((string)($startDate ?? '')) ?>">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">To</label>
    <input class="form-control" type="date" name="end_date" value="<?= e((string)($endDate ?? '')) ?>">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">Action</label>
    <select class="form-select" name="action">
      <option value="">All actions</option>
      <?php foreach ($actionList as $action): ?>
        <option value="<?= e((string)$action) ?>" <?= ($actionFilter ?? '') === $action ? 'selected' : '' ?>>
          <?= e($formatAction((string)$action)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small">Entity</label>
    <select class="form-select" name="entity">
      <option value="">All entities</option>
      <?php foreach ($entityList as $entity): ?>
        <option value="<?= e((string)$entity) ?>" <?= ($entityFilter ?? '') === $entity ? 'selected' : '' ?>>
          <?= e(ucfirst((string)$entity)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-12 d-grid d-md-flex justify-content-end gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/administrator/logs">Clear Filters</a>
    <button class="btn btn-outline-primary" type="submit">Filter</button>
  </div>
</form>

<?php if (!empty($logs)): ?>
  <div class="d-block d-md-none">
    <?php foreach ($logs as $log): ?>
      <?php
      $userLabel = 'System';
      if (!empty($log['user_name'])) {
          $userLabel = (string)$log['user_name'];
      } elseif (!empty($log['user_id'])) {
          $userLabel = 'User #' . (int)$log['user_id'];
      }
      $action = (string)($log['action'] ?? '');
      $entity = (string)($log['entity'] ?? '');
      $entityId = $log['entity_id'] ?? null;
      $entityName = (string)($log['entity_name'] ?? '');
      $entityRef = (string)($log['entity_ref'] ?? '');
      $createdAt = !empty($log['created_at']) ? date('M j, Y H:i', strtotime((string)$log['created_at'])) : '-';
      ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="fw-semibold"><?= e($userLabel) ?></div>
              <?php if (!empty($log['user_email'])): ?>
                <div class="text-muted small"><?= e((string)$log['user_email']) ?></div>
              <?php endif; ?>
            </div>
            <span class="badge <?= e($badgeForAction($action)) ?> text-uppercase">
              <?= e($formatAction($action)) ?>
            </span>
          </div>

          <div class="mt-3 text-muted small">
            <div>
              Entity:
              <?php if ($entityName !== '' && ($entity === 'students' || $entity === 'users')): ?>
                <?= e($entityName) ?>
              <?php else: ?>
                <?= e($entity !== '' ? ucfirst($entity) : '-') ?>
                <?php if ($entityName !== ''): ?>
                  — <?= e($entityName) ?>
                <?php elseif ($entityId): ?>
                  #<?= e((string)$entityId) ?>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($entityRef !== '' && $entity !== 'students' && $entity !== 'users'): ?>
                <span class="text-muted">(<?= e($entityRef) ?>)</span>
              <?php endif; ?>
            </div>
            <div>Time: <?= e($createdAt) ?></div>
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
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Entity</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
            <?php
            $userLabel = 'System';
            if (!empty($log['user_name'])) {
                $userLabel = (string)$log['user_name'];
            } elseif (!empty($log['user_id'])) {
                $userLabel = 'User #' . (int)$log['user_id'];
            }
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
                <div class="fw-semibold"><?= e($userLabel) ?></div>
                <?php if (!empty($log['user_email'])): ?>
                  <div class="text-muted small"><?= e((string)$log['user_email']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= e($badgeForAction($action)) ?> text-uppercase">
                  <?= e($formatAction($action)) ?>
                </span>
              </td>
              <td class="text-muted small">
                <?php if ($entityName !== '' && ($entity === 'students' || $entity === 'users')): ?>
                  <?= e($entityName) ?>
                <?php else: ?>
                  <?= e($entity !== '' ? ucfirst($entity) : '-') ?>
                  <?php if ($entityName !== ''): ?>
                    — <?= e($entityName) ?>
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
    <div class="card-body text-muted">No logs found.</div>
  </div>
<?php endif; ?>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../partials/pagination.php';
?>
