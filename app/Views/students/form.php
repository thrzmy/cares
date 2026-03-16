<?php
declare(strict_types=1);
$mode = (string)($mode ?? 'edit');
$student = $student ?? [];
$activeSemester = $activeSemester ?? null;
$courseOptions = $courseOptions ?? [];
$courseSummaries = $courseSummaries ?? [];
?>
<div class="card shadow-sm">
  <div class="card-body">
    <?php
    $status = (string)($student['status'] ?? 'pending');
    $screeningStatus = (string)($student['screening_status'] ?? 'pending');
    $applicationStatus = (string)($student['application_status'] ?? 'new_student');
    $physicalStatus = (string)($student['physical_requirement_status'] ?? 'pending');
    $cctChoice = (string)($student['cct_choice'] ?? 'none');
    ?>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
      <div>
        <h5 class="fw-bold mb-1"><?= e((string)($title ?? 'Student')) ?></h5>
        <p class="text-muted mb-0">Manage student profile, application details, and screening information.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_PATH) ?>/<?= e(str_starts_with((string)$action, '/admission') ? 'admission' : 'administrator') ?>/students">Back</a>
    </div>

    <hr>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e((string)$error) ?></div>
    <?php endif; ?>

    <?php if (!empty($activeSemester)): ?>
      <div class="alert alert-light border mb-3">
        <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 1px;">Assigned Semester</div>
        <div class="fw-semibold"><?= e((string)($activeSemester['label'] ?? '')) ?></div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">Set an active semester first before saving students.</div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_PATH) ?><?= e((string)$action) ?>">
      <?= csrfField() ?>
      <?php if ($mode === 'edit'): ?>
        <input type="hidden" name="id" value="<?= e((string)($student['id'] ?? '')) ?>">
      <?php endif; ?>

      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="fw-bold mb-1">Personal Details</h6>
            <p class="text-muted small mb-0">Basic student identity and contact information.</p>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Application Number</label>
            <input class="form-control" type="text" name="application_number" value="<?= e((string)($student['application_number'] ?? '')) ?>">
            <div class="form-text">Use the official application number from the admission records.</div>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Last Name</label>
            <input class="form-control" type="text" name="last_name" required value="<?= e((string)($student['last_name'] ?? '')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">First Name</label>
            <input class="form-control" type="text" name="first_name" required value="<?= e((string)($student['first_name'] ?? '')) ?>">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Middle Name</label>
            <input class="form-control" type="text" name="middle_name" value="<?= e((string)($student['middle_name'] ?? '')) ?>" placeholder="Optional">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?= e((string)($student['email'] ?? '')) ?>" placeholder="Optional">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">City</label>
            <input class="form-control" type="text" name="city" value="<?= e((string)($student['city'] ?? '')) ?>">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Province</label>
            <input class="form-control" type="text" name="province" value="<?= e((string)($student['province'] ?? '')) ?>">
          </div>
        </div>
      </div>

      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="fw-bold mb-1">Academic Profile</h6>
            <p class="text-muted small mb-0">Program preferences and school background used during screening.</p>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-12 col-md-4">
            <label class="form-label">Application Status</label>
            <select class="form-select" name="application_status" required>
              <option value="new_student" <?= $applicationStatus === 'new_student' ? 'selected' : '' ?>>New Student</option>
              <option value="transferee" <?= $applicationStatus === 'transferee' ? 'selected' : '' ?>>Transferee</option>
              <option value="returning_student" <?= $applicationStatus === 'returning_student' ? 'selected' : '' ?>>Returning Student</option>
              <option value="adult_learner" <?= $applicationStatus === 'adult_learner' ? 'selected' : '' ?>>Adult Learner</option>
              <option value="old_curriculum" <?= $applicationStatus === 'old_curriculum' ? 'selected' : '' ?>>Old Curriculum</option>
              <option value="als_passer" <?= $applicationStatus === 'als_passer' ? 'selected' : '' ?>>ALS Passer</option>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">SHS Strand</label>
            <input class="form-control" type="text" name="shs_strand" value="<?= e((string)($student['shs_strand'] ?? '')) ?>" placeholder="Example: HUMSS, STEM, ABM, ICT, GAS">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">General Average / GPA</label>
            <input class="form-control" type="number" name="gpa" min="0" max="100" step="0.01" value="<?= e((string)($student['gpa'] ?? '')) ?>" placeholder="Example: 86.00">
            <div class="form-text">Required for some programs.</div>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">CCT Choice</label>
            <select class="form-select" name="cct_choice" required>
              <option value="none" <?= $cctChoice === 'none' ? 'selected' : '' ?>>Not Selected Yet</option>
              <option value="first" <?= $cctChoice === 'first' ? 'selected' : '' ?>>1st Choice</option>
              <option value="second" <?= $cctChoice === 'second' ? 'selected' : '' ?>>2nd Choice</option>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">1st Choice</label>
            <select class="form-select" id="firstChoice" name="first_choice">
              <option value="">Select program</option>
              <?php foreach ($courseOptions as $courseOption): ?>
                <option value="<?= e((string)$courseOption['id']) ?>" <?= in_array((string)($student['first_choice'] ?? ''), [(string)$courseOption['id'], (string)$courseOption['code']], true) ? 'selected' : '' ?>>
                  <?= e((string)$courseOption['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">2nd Choice</label>
            <select class="form-select" id="secondChoice" name="second_choice">
              <option value="">Select program</option>
              <?php foreach ($courseOptions as $courseOption): ?>
                <option value="<?= e((string)$courseOption['id']) ?>" <?= in_array((string)($student['second_choice'] ?? ''), [(string)$courseOption['id'], (string)$courseOption['code']], true) ? 'selected' : '' ?>>
                  <?= e((string)$courseOption['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="fw-bold mb-1">Screening Details</h6>
            <p class="text-muted small mb-0">Record the extra screening checks and bonus points used for program qualification.</p>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-12 col-md-3">
            <label class="form-label">Physical Requirement</label>
            <select class="form-select" name="physical_requirement_status">
              <option value="pending" <?= $physicalStatus === 'pending' ? 'selected' : '' ?>>Pending / Not Required</option>
              <option value="met" <?= $physicalStatus === 'met' ? 'selected' : '' ?>>Met</option>
              <option value="not_met" <?= $physicalStatus === 'not_met' ? 'selected' : '' ?>>Not Met</option>
            </select>
            <div class="form-text">Required for some programs.</div>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Honors / Awards</label>
            <input class="form-control" type="number" name="honors_awards_points" min="0" max="5" step="1" inputmode="numeric" pattern="[0-9]*" value="<?= e((string)($student['honors_awards_points'] ?? '')) ?>" placeholder="0 to 5">
            <div class="form-text">
              <?php foreach (screeningHonorsPointPresets() as $index => $preset): ?>
                <?= $index > 0 ? ' </br> ' : '' ?><?= e((string)$preset['label']) ?> = <?= e((string)$preset['points']) ?>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Residence</label>
            <input class="form-control" type="number" name="residence_points" min="0" max="5" step="1" inputmode="numeric" pattern="[0-9]*" value="<?= e((string)($student['residence_points'] ?? '')) ?>" placeholder="0 to 5">
            <div class="form-text">Maximum of 5 points.</div>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Other</label>
            <input class="form-control" type="number" name="other_screening_points" min="0" max="5" step="1" inputmode="numeric" pattern="[0-9]*" value="<?= e((string)($student['other_screening_points'] ?? '')) ?>" placeholder="0 to 5">
            <div class="form-text">Maximum of 5 points.</div>
          </div>
        </div>
      </div>

      <?php if (!empty($courseSummaries)): ?>
      <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="fw-bold mb-1">System Status</h6>
            <p class="text-muted small mb-0">These values are computed by the system from encoded scores and screening rules.</p>
          </div>
        </div>
        <input type="hidden" name="status" value="<?= e($status) ?>">
        <input type="hidden" name="screening_status" value="<?= e($screeningStatus) ?>">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Exam Result</label>
            <input class="form-control" type="text" value="<?= e(studentStatusLabel($status)) ?>" readonly disabled>
            <div class="form-text">Based on Part 1 subject cutoffs when scores are encoded.</div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Screening Status</label>
            <input class="form-control" type="text" value="<?= e(studentScreeningStatusLabel($screeningStatus)) ?>" readonly disabled>
            <div class="form-text">Based on program screening after the entrance exam result is available.</div>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Per-Course Qualification</label>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($courseSummaries as $course): ?>
              <div class="border rounded-3 px-3 py-2 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                  <div class="fw-semibold"><?= e((string)$course['course_code']) ?> - <?= e((string)$course['course_name']) ?></div>
                  <div class="text-muted small">
                    <?= !empty($course['qualified']) ? 'Qualified' : 'Not Qualified' ?>
                    &middot; <?= e((string)($course['result_label'] ?? 'Failed')) ?>
                  </div>
                </div>
                <div class="text-md-end">
                  <div class="fw-semibold"><?= e(number_format((float)($course['qualification_percentage'] ?? 0), 2)) ?>%</div>
                  <div class="text-muted small">qualification score</div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <input type="hidden" name="status" value="<?= e($status) ?>">
      <input type="hidden" name="screening_status" value="<?= e($screeningStatus) ?>">
      <div class="d-flex flex-wrap gap-2 mt-4">
        <button class="btn btn-primary" type="submit"><?= $mode === 'create' ? 'Create Student' : 'Save Changes' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(BASE_PATH) ?>/<?= e(str_starts_with((string)$action, '/admission') ? 'admission' : 'administrator') ?>/students">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const firstChoice = document.getElementById('firstChoice');
  const secondChoice = document.getElementById('secondChoice');
  if (!firstChoice || !secondChoice) {
    return;
  }

  const syncSecondChoiceOptions = () => {
    const firstValue = firstChoice.value;
    Array.from(secondChoice.options).forEach((option) => {
      if (option.value === '') {
        option.disabled = false;
        return;
      }
      option.disabled = option.value === firstValue;
    });

    if (secondChoice.value !== '' && secondChoice.value === firstValue) {
      secondChoice.value = '';
    }
  };

  syncSecondChoiceOptions();
  firstChoice.addEventListener('change', syncSecondChoiceOptions);
});
</script>
