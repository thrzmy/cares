<?php
declare(strict_types=1);
$parts = $parts ?? [];
$groupedParts = $groupedParts ?? [];
$scoresMap = $scoresMap ?? [];
$sortedParts = $parts;
usort($sortedParts, static function (array $a, array $b) use ($scoresMap): int {
    $aValue = $scoresMap[(int)$a['id']] ?? null;
    $bValue = $scoresMap[(int)$b['id']] ?? null;
    if ($aValue === null && $bValue === null) {
        return strcmp((string)$a['name'], (string)$b['name']);
    }
    if ($aValue === null) {
        return 1;
    }
    if ($bValue === null) {
        return -1;
    }
    return ((float)$bValue <=> (float)$aValue);
});
?>
<div class="page-header mb-3">
  <div>
    <div class="page-kicker">Admission</div>
    <h4 class="fw-bold mb-1">Result Summary</h4>
    <p class="page-subtitle">Student: <?= e($student['name']) ?> &middot; <?= e($student['email']) ?></p>
  </div>
  <div class="page-actions">
    <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/admission/results">Back to Results & Recommendation</a>
  </div>
</div>
<?php require __DIR__ . '/../partials/result_summary_content.php'; ?>
