<?php
// This file expects a $teacher associative array to be defined before including
?>
<div class="modal fade" id="editTeacherModal<?= $teacher['teacher_id'] ?>" tabindex="-1" aria-labelledby="editTeacherModalLabel<?= $teacher['teacher_id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="manage_teachers.php" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>" />
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($teacher['image']) ?>" />
      <div class="modal-header">
        <h5 class="modal-title" id="editTeacherModalLabel<?= $teacher['teacher_id'] ?>">Edit Teacher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="full_name_<?= $teacher['teacher_id'] ?>" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="full_name_<?= $teacher['teacher_id'] ?>" name="full_name" value="<?= htmlspecialchars($teacher['full_name']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="email_<?= $teacher['teacher_id'] ?>" class="form-label">Email</label>
          <input type="email" class="form-control" id="email_<?= $teacher['teacher_id'] ?>" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" />
        </div>
        <div class="mb-3">
          <label for="phone_<?= $teacher['teacher_id'] ?>" class="form-label">Phone</label>
          <input type="text" class="form-control" id="phone_<?= $teacher['teacher_id'] ?>" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" />
        </div>
        <div class="mb-3">
          <label for="hire_date_<?= $teacher['teacher_id'] ?>" class="form-label">Hire Date</label>
          <input type="date" class="form-control" id="hire_date_<?= $teacher['teacher_id'] ?>" name="hire_date" value="<?= htmlspecialchars($teacher['hire_date']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="specialization_<?= $teacher['teacher_id'] ?>" class="form-label">Specialization</label>
          <input type="text" class="form-control" id="specialization_<?= $teacher['teacher_id'] ?>" name="specialization" value="<?= htmlspecialchars($teacher['specialization']) ?>" />
        </div>
        <div class="mb-3">
          <label for="image_<?= $teacher['teacher_id'] ?>" class="form-label">Image</label>
          <input type="file" class="form-control" id="image_<?= $teacher['teacher_id'] ?>" name="image" accept="image/*" />
          <?php if ($teacher['image'] && file_exists($teacher['image'])): ?>
            <img src="<?= htmlspecialchars($teacher['image']) ?>" alt="Teacher Image" class="table-img mt-2" />
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Teacher</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Disallow numbers in full_name and specialization inputs in edit modal
  document.getElementById('full_name_<?= $teacher['teacher_id'] ?>').addEventListener('input', function(e) {
    this.value = this.value.replace(/[0-9]/g, '');
  });
  document.getElementById('specialization_<?= $teacher['teacher_id'] ?>').addEventListener('input', function(e) {
    this.value = this.value.replace(/[0-9]/g, '');
  });

  // Disallow letters in phone input in edit modal
  document.getElementById('phone_<?= $teacher['teacher_id'] ?>').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
  });
</script>
