<?php
// This file expects $learners and $books arrays to be defined before including
?>
<div class="modal fade" id="borrowBookModal" tabindex="-1" aria-labelledby="borrowBookModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="manage_borrow_records.php" class="modal-content">
      <input type="hidden" name="action" value="borrow" />
      <div class="modal-header">
        <h5 class="modal-title" id="borrowBookModalLabel">Borrow Book</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="learner_id" class="form-label">Learner</label>
          <select class="form-select" id="learner_id" name="learner_id" required>
            <option value="">Select Learner</option>
            <?php foreach ($learners as $learner): ?>
              <option value="<?= $learner['learner_id'] ?>"><?= htmlspecialchars($learner['first_name'] . ' ' . $learner['last_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="book_id" class="form-label">Book</label>
          <select class="form-select" id="book_id" name="book_id" required>
            <option value="">Select Book</option>
            <?php foreach ($books as $book): ?>
              <option value="<?= $book['book_id'] ?>"><?= htmlspecialchars($book['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="borrow_date" class="form-label">Borrow Date</label>
          <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="<?= date('Y-m-d') ?>" required />
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Borrow</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
