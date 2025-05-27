<?php
// This file expects a $learner associative array and $parents array to be defined before including
?>
<div class="modal fade" id="editLearnerModal<?= $learner['learner_id'] ?>" tabindex="-1" aria-labelledby="editLearnerModalLabel<?= $learner['learner_id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="manage_learners.php" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="learner_id" value="<?= $learner['learner_id'] ?>" />
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($learner['image']) ?>" />
      <div class="modal-header">
        <h5 class="modal-title" id="editLearnerModalLabel<?= $learner['learner_id'] ?>">Edit Learner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="first_name_<?= $learner['learner_id'] ?>" class="form-label">First Name</label>
          <input type="text" class="form-control" id="first_name_<?= $learner['learner_id'] ?>" name="first_name" value="<?= htmlspecialchars($learner['first_name']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="last_name_<?= $learner['learner_id'] ?>" class="form-label">Surname</label>
          <input type="text" class="form-control" id="last_name_<?= $learner['learner_id'] ?>" name="last_name" value="<?= htmlspecialchars($learner['last_name']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="date_of_birth_<?= $learner['learner_id'] ?>" class="form-label">Date of Birth</label>
          <input type="date" class="form-control" id="date_of_birth_<?= $learner['learner_id'] ?>" name="date_of_birth" value="<?= htmlspecialchars($learner['date_of_birth']) ?>" required />
        </div>
     
        <div class="mb-3">
          <label for="enrollment_date_<?= $learner['learner_id'] ?>" class="form-label">Enrollment Date</label>
          <input type="date" class="form-control" id="enrollment_date_<?= $learner['learner_id'] ?>" name="enrollment_date" value="<?= htmlspecialchars($learner['enrollment_date']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="address_<?= $learner['learner_id'] ?>" class="form-label">Address</label>
          <textarea class="form-control" id="address_<?= $learner['learner_id'] ?>" name="address"><?= htmlspecialchars($learner['address']) ?></textarea>
        </div>
        <div class="mb-3">
          <label for="image_<?= $learner['learner_id'] ?>" class="form-label">Image</label>
          <input type="file" class="form-control" id="image_<?= $learner['learner_id'] ?>" name="image" accept="image/*" />
          <?php if ($learner['image'] && file_exists($learner['image'])): ?>
            <img src="<?= htmlspecialchars($learner['image']) ?>" alt="Learner Image" class="table-img mt-2" />
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Learner</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Disallow numbers in first_name and last_name inputs in edit modal
  document.getElementById('first_name_<?= $learner['learner_id'] ?>').addEventListener('input', function(e) {
    this.value = this.value.replace(/[0-9]/g, '');
  });
  document.getElementById('last_name_<?= $learner['learner_id'] ?>').addEventListener('input', function(e) {
    this.value = this.value.replace(/[0-9]/g, '');
  });
</script>
