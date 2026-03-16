<?php
declare(strict_types=1);
$parts = $parts ?? [];
$groupedParts = $groupedParts ?? [];
$scoresMap = $scoresMap ?? [];
$success = $success ?? null;
$error = $error ?? null;
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
<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= e((string)$success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e((string)$error) ?></div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/result_summary_content.php'; ?>

<?php if (!empty($parts)): ?>
  <form method="post" action="<?= e(BASE_PATH) ?>/admission/encode/edit" class="mt-3" data-summary-score-form>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$student['id'] ?>">
    <input type="hidden" name="mode" value="summary-edit">

    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
          <div>
            <h6 class="fw-bold mb-1">Exam Scores</h6>
            <p class="text-muted small mb-0">Update recorded scores directly from this summary.</p>
          </div>
          <div class="d-flex gap-2" data-summary-score-actions>
            <button type="reset" class="btn btn-outline-secondary btn-sm" data-summary-reset disabled>Reset</button>
            <button type="button" class="btn btn-primary btn-sm" data-summary-save disabled>Save Changes</button>
          </div>
        </div>

        <div class="row g-3">
          <?php foreach (($groupedParts ?: [['category_name' => 'Exam Parts', 'parts' => $parts]]) as $group): ?>
            <div class="col-12 col-xl-6">
              <section class="encode-part-card h-100">
                <header class="encode-part-card__header">
                  <h6 class="encode-part-card__title mb-0"><?= e((string)($group['category_name'] ?? 'Exam Parts')) ?></h6>
                </header>
                <div class="encode-part-card__body">
                  <div class="row g-3">
                    <?php foreach (($group['parts'] ?? []) as $part): ?>
                      <?php
                        $partId = (int)$part['id'];
                        $maxScore = (float)$part['max_score'];
                        $value = $scoresMap[$partId] ?? '';
                        $displayValue = $value === '' ? '0' : number_format((float)$value, 0, '.', '');
                      ?>
                      <div class="col-12 col-md-6">
                        <label class="encode-score-label" for="summary-score-<?= $partId ?>">
                          <span><?= e((string)$part['name']) ?></span>
                          <span class="encode-score-max">Max: <?= e(number_format($maxScore, 0)) ?></span>
                        </label>
                        <div class="encode-score-input-wrap">
                          <input
                            id="summary-score-<?= $partId ?>"
                            class="form-control encode-score-input"
                            type="number"
                            name="scores[<?= $partId ?>]"
                            min="0"
                            max="<?= e((string)$maxScore) ?>"
                            step="1"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            value="<?= e($displayValue) ?>"
                            data-initial-value="<?= e($displayValue) ?>"
                            required
                          >
                          <span class="encode-score-suffix">pts</span>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </form>

  <div class="modal fade" id="summarySaveConfirmModal" tabindex="-1" aria-labelledby="summarySaveConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="summarySaveConfirmModalLabel">Save Score Changes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Saving these scores will update the student's result and recommendation based on the latest recorded values.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" data-summary-confirm-save>Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const form = document.querySelector('[data-summary-score-form]');
      const actions = document.querySelector('[data-summary-score-actions]');
      if (!form || !actions) {
        return;
      }

      const inputs = Array.from(form.querySelectorAll('input[name^="scores["]'));
      const resetButton = form.querySelector('[data-summary-reset]');
      const saveButton = form.querySelector('[data-summary-save]');
      const confirmSaveButton = document.querySelector('[data-summary-confirm-save]');
      const confirmModalEl = document.getElementById('summarySaveConfirmModal');
      const confirmModal = confirmModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(confirmModalEl) : null;

      const syncDirtyState = () => {
        const dirty = inputs.some((input) => String(input.value) !== String(input.dataset.initialValue ?? ''));
        if (resetButton) {
          resetButton.disabled = !dirty;
        }
        if (saveButton) {
          saveButton.disabled = !dirty;
        }
      };

      inputs.forEach((input) => {
        input.addEventListener('input', syncDirtyState);
        input.addEventListener('change', syncDirtyState);
      });

      form.addEventListener('reset', () => {
        window.setTimeout(syncDirtyState, 0);
      });

      if (saveButton) {
        saveButton.addEventListener('click', () => {
          if (saveButton.disabled) {
            return;
          }
          if (!form.reportValidity()) {
            return;
          }
          if (confirmModal) {
            confirmModal.show();
            return;
          }
        });
      }

      if (confirmSaveButton) {
        confirmSaveButton.addEventListener('click', () => {
          if (saveButton && saveButton.disabled) {
            return;
          }
          if (!form.reportValidity()) {
            return;
          }
          if (confirmModal) {
            confirmModal.hide();
          }
          form.requestSubmit();
        });
      }

      syncDirtyState();
    });
  </script>
<?php endif; ?>
