<?php
declare(strict_types=1);

// Provided via Controller:
// $courses, $groupedParts, $weightsMap, $success, $error
?>

<div class="page-header mb-3">
    <div>
        <div class="page-kicker">Administrator</div>
        <h4 class="fw-bold mb-1">Matrix Configuration</h4>
        <p class="page-subtitle">Configure exam part weights per course and set max scores.</p>
    </div>
    <div class="page-actions d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addExamPartModal">
            <i class="fa-solid fa-file-circle-plus"></i> Add Exam Part
        </button>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fa-solid fa-folder-plus"></i> Add Course
        </button>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 bg-success-subtle text-success d-flex align-items-center gap-3 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div class="fw-medium"><?= e($success) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger-subtle text-danger d-flex align-items-center gap-3 shadow-sm" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5"></i>
        <div class="fw-medium"><?= e($error) ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-maroon d-flex align-items-center gap-2">
            <i class="fa-solid fa-table-cells-large"></i> Course & Item Weights
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="matrix-scroll-hint px-4 pt-3 small text-muted d-md-none">
            Swipe left or right to view the full matrix.
        </div>
        <div class="table-responsive matrix-table-wrap">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix" method="POST" id="matrixForm">
                <?= csrfField() ?>
                <input type="hidden" name="page" value="<?= e((string)(($pagination['page'] ?? 1))) ?>">

                <table class="table table-bordered table-hover m-0 matrix-table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="bg-light sticky-col" style="min-width: 280px; left: 0; z-index: 2; border-right: 2px solid #dee2e6;">Courses</th>
                            <th class="text-center bg-light" style="width: 60px;">Action</th>

                            <?php foreach ($groupedParts as $group): ?>
                                <th colspan="<?= count($group['parts']) ?>" class="text-center bg-light text-maroon border-start border-end matrix-group-heading" style="border-bottom: 2px solid var(--cares-maroon-light);">
                                    <?= e($group['category_name']) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th class="bg-light sticky-col" style="left: 0; z-index: 2; border-right: 2px solid #dee2e6;">
                                <div class="text-muted small fw-medium mt-1">Maximum Valid Score (Raw)</div>
                            </th>
                            <th class="bg-light"></th>
                            <?php foreach ($groupedParts as $group): ?>
                                <?php foreach ($group['parts'] as $part): ?>
                                    <th class="text-center bg-light px-2 matrix-part-col" title="<?= e($part['name']) ?>" style="vertical-align: bottom;">
                                        <div class="small fw-semibold text-dark mb-2 matrix-part-label"><?= e($part['name']) ?></div>
                                        <input type="number"
                                               name="max_scores[<?= $part['id'] ?>]"
                                               value="<?= (float)$part['max_score'] ?>"
                                               class="form-control form-control-sm text-center mx-auto shadow-sm"
                                               style="height: 30px; font-size: 0.8rem; font-weight: 600; color: var(--cares-maroon);"
                                               min="0" step="0.01" required>
                                    </th>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="100" class="text-center py-5 text-muted">
                                    <div class="mb-3"><i class="fa-solid fa-folder-open fs-1 text-light"></i></div>
                                    <h6 class="fw-semibold text-secondary">No courses found</h6>
                                    <p class="small mb-0">Add a course to start configuring the matrix.</p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="sticky-col bg-white fw-medium text-dark py-3" style="left: 0; z-index: 1; border-right: 2px solid #dee2e6;">
                                    <div class="d-flex align-items-center gap-3 ps-2">
                                        <div class="avatar-circle shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem; background-color: var(--cares-gold-dark);">
                                            <?= e(substr($course['course_code'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-maroon fs-6"><?= e($course['course_code']) ?></div>
                                            <div class="small text-muted text-truncate" style="max-width: 200px;" title="<?= e($course['course_name']) ?>"><?= e($course['course_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-course rounded-circle" style="width: 32px; height: 32px; padding: 0;" data-id="<?= $course['id'] ?>" data-code="<?= e($course['course_code']) ?>" title="Delete Course">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>

                                <?php foreach ($groupedParts as $group): ?>
                                    <?php foreach ($group['parts'] as $part): ?>
                                        <?php
                                            $weight = $weightsMap[$course['id']][$part['id']] ?? 0.00;
                                        ?>
                                        <td class="text-center px-1 matrix-part-col">
                                            <input type="number"
                                                   name="weights[<?= $course['id'] ?>][<?= $part['id'] ?>]"
                                                   value="<?= number_format((float)$weight, 2, '.', '') ?>"
                                                   class="form-control form-control-sm text-center mx-auto weight-input <?= (float)$weight > 0 ? 'has-weight' : '' ?>"
                                                   style="height: 32px;"
                                                   min="0" max="100" step="0.01" tabindex="1">
                                        </td>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <div class="card-footer bg-light border-top py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="text-muted small d-none d-md-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-info text-info"></i>
            Values represent the percentage weight applied to the student's normalized score.
        </div>
        <button type="button" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm fw-semibold" onclick="document.getElementById('matrixForm').submit();">
            <i class="fa-regular fa-floppy-disk"></i> Save Matrix Configuration
        </button>
    </div>
</div>

<?php
$pagination = $pagination ?? null;
require __DIR__ . '/../partials/pagination.php';
?>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix/add-course" method="POST">
                <?= csrfField() ?>
                <div class="modal-header bg-light border-bottom border-black border-opacity-10 py-3">
                    <h5 class="modal-title text-maroon fw-bold d-flex align-items-center gap-2" id="addCourseModalLabel">
                        <i class="fa-solid fa-folder-plus"></i> Add New Course
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label for="course_code" class="form-label fw-semibold text-dark">Course Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="course_code" name="course_code" placeholder="e.g. BSIT" required>
                    </div>
                    <div>
                        <label for="course_name" class="form-label fw-semibold text-dark">Course Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="course_name" name="course_name" placeholder="e.g. Bachelor of Science in Information Technology" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm">
                        <i class="fa-solid fa-check"></i> Save Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Exam Part Modal -->
<div class="modal fade" id="addExamPartModal" tabindex="-1" aria-labelledby="addExamPartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= e(BASE_PATH) ?>/administrator/matrix/add-part" method="POST">
                <?= csrfField() ?>
                <div class="modal-header bg-light border-bottom border-black border-opacity-10 py-3">
                    <h5 class="modal-title text-maroon fw-bold d-flex align-items-center gap-2" id="addExamPartModalLabel">
                        <i class="fa-solid fa-file-circle-plus"></i> Add Exam Part
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="part_name" class="form-label fw-semibold text-dark">Part Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="part_name" name="name" placeholder="e.g. Logical Reasoning" required>
                    </div>
                    <div class="mb-3">
                        <label for="max_score" class="form-label fw-semibold text-dark">Maximum Raw Score <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="max_score" name="max_score" value="100" min="1" step="0.01" required>
                        <div class="form-text mt-1">Numerical maximum valid score for this exam division.</div>
                    </div>
                    <div>
                        <label for="category_id" class="form-label fw-semibold text-dark">Category Group <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="0">Uncategorized</option>
                            <?php foreach ($groupedParts as $group): ?>
                                <?php if ($group['category_name'] !== 'Uncategorized' && $group['category_name'] !== 'Exam Parts'): ?>
                                    <?php
                                        $catId = $group['parts'][0]['category_id'] ?? 0;
                                    ?>
                                    <?php if ($catId): ?>
                                    <option value="<?= e($catId) ?>"><?= e($group['category_name']) ?></option>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light py-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm">
                        <i class="fa-solid fa-check"></i> Save Part
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete form -->
<form id="deleteCourseForm" action="<?= e(BASE_PATH) ?>/administrator/matrix/delete-course" method="POST" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="course_id" id="delete_course_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all inputs on focus for easier typing
    document.querySelectorAll('.weight-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.select();
        });

        // Add visual queue for active weights
        input.addEventListener('input', function() {
            if (parseFloat(this.value) > 0) {
                this.classList.add('has-weight');
            } else {
                this.classList.remove('has-weight');
            }
        });
    });

    // Delete course handler
    document.querySelectorAll('.btn-delete-course').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const code = this.dataset.code;

            if (confirm(`Are you sure you want to remove ${code} from the matrix? This action cannot be undone.`)) {
                document.getElementById('delete_course_id').value = id;
                document.getElementById('deleteCourseForm').submit();
            }
        });
    });
});
</script>

<style>
/* Smooth visual cue for inputs with value > 0 */
.weight-input.has-weight {
    background-color: rgba(214, 168, 79, 0.1);
    border-color: var(--cares-gold);
    color: var(--cares-maroon-dark);
    font-weight: 600;
}
.weight-input:not(.has-weight) {
    color: var(--cares-text-muted);
}
</style>
