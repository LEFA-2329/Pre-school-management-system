<?php
// This file expects a $book associative array to be defined before including
?>
<div class="modal fade" id="editBookModal<?= $book['book_id'] ?>" tabindex="-1" aria-labelledby="editBookModalLabel<?= $book['book_id'] ?>" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="manage_book.php" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>" />
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($book['image']) ?>" />
      <div class="modal-header">
        <h5 class="modal-title" id="editBookModalLabel<?= $book['book_id'] ?>">Edit Book</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="title_<?= $book['book_id'] ?>" class="form-label">Title</label>
          <input type="text" class="form-control" id="title_<?= $book['book_id'] ?>" name="title" value="<?= htmlspecialchars($book['title']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="author_<?= $book['book_id'] ?>" class="form-label">Author</label>
          <input type="text" class="form-control" id="author_<?= $book['book_id'] ?>" name="author" value="<?= htmlspecialchars($book['author']) ?>" />
        </div>
        <div class="mb-3">
          <label for="isbn_<?= $book['book_id'] ?>" class="form-label">ISBN</label>
          <input type="text" class="form-control" id="isbn_<?= $book['book_id'] ?>" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" />
        </div>
        <div class="mb-3">
          <label for="published_year_<?= $book['book_id'] ?>" class="form-label">Published Year</label>
          <input type="number" class="form-control" id="published_year_<?= $book['book_id'] ?>" name="published_year" value="<?= htmlspecialchars($book['published_year']) ?>" />
        </div>
        <div class="mb-3">
          <label for="copies_available_<?= $book['book_id'] ?>" class="form-label">Copies Available</label>
          <input type="number" class="form-control" id="copies_available_<?= $book['book_id'] ?>" name="copies_available" value="<?= htmlspecialchars($book['copies_available']) ?>" min="0" />
        </div>
        <div class="mb-3">
          <label for="image_<?= $book['book_id'] ?>" class="form-label">Image</label>
          <input type="file" class="form-control" id="image_<?= $book['book_id'] ?>" name="image" accept="image/*" />
          <?php if ($book['image'] && file_exists($book['image'])): ?>
            <img src="<?= htmlspecialchars($book['image']) ?>" alt="Book Image" class="table-img mt-2" />
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update Book</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
