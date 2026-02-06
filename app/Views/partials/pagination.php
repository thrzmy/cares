<?php
declare(strict_types=1);

$pagination = $pagination ?? null;
if (empty($pagination)) {
    return;
}

$page = (int)($pagination['page'] ?? 1);
$pages = (int)($pagination['pages'] ?? 1);
$total = (int)($pagination['total'] ?? 0);
$perPage = (int)($pagination['perPage'] ?? 0);
$from = (int)($pagination['from'] ?? 0);
$to = (int)($pagination['to'] ?? 0);
$basePath = (string)($pagination['basePath'] ?? '');
$query = $pagination['query'] ?? [];

if ($total <= 0) {
    return;
}

$query = array_filter($query, static fn($value) => $value !== '' && $value !== null);

$buildUrl = static function (int $targetPage) use ($basePath, $query): string {
    $params = $query;
    if ($targetPage > 1) {
        $params['page'] = $targetPage;
    } else {
        unset($params['page']);
    }
    $qs = http_build_query($params);
    return e(BASE_PATH . $basePath . ($qs !== '' ? ('?' . $qs) : ''));
};

$window = 2;
$start = max(1, $page - $window);
$end = min($pages, $page + $window);
?>

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-3">
  <div class="text-muted small">
    Showing <?= e((string)$from) ?>-<?= e((string)$to) ?> of <?= e((string)$total) ?>
  </div>
  <nav aria-label="Pagination">
    <ul class="pagination mb-0">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $page <= 1 ? '#' : $buildUrl($page - 1) ?>" aria-label="Previous">
          &laquo;
        </a>
      </li>

      <?php if ($start > 1): ?>
        <li class="page-item">
          <a class="page-link" href="<?= $buildUrl(1) ?>">1</a>
        </li>
        <?php if ($start > 2): ?>
          <li class="page-item disabled">
            <span class="page-link">&hellip;</span>
          </li>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= $i === $page ? '#' : $buildUrl($i) ?>"><?= e((string)$i) ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($end < $pages): ?>
        <?php if ($end < $pages - 1): ?>
          <li class="page-item disabled">
            <span class="page-link">&hellip;</span>
          </li>
        <?php endif; ?>
        <li class="page-item">
          <a class="page-link" href="<?= $buildUrl($pages) ?>"><?= e((string)$pages) ?></a>
        </li>
      <?php endif; ?>

      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= $page >= $pages ? '#' : $buildUrl($page + 1) ?>" aria-label="Next">
          &raquo;
        </a>
      </li>
    </ul>
  </nav>
</div>
