<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle CRUD operations
$errors = [];
$success_message = '';

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $published_year = intval($_POST['published_year'] ?? 0);
    $copies_available = intval($_POST['copies_available'] ?? 1);
    $image = '';

    if (!$title) {
        $errors[] = "Title is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($tmp_name, $destination)) {
            $image = $destination;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (count($errors) === 0) {
        $query = "INSERT INTO books (title, author, isbn, published_year, copies_available, image) VALUES ($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($dbconn, $query, [$title, $author, $isbn, $published_year, $copies_available, $image]);
        if ($result) {
            $success_message = "Book added successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Update Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $book_id = intval($_POST['book_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $published_year = intval($_POST['published_year'] ?? 0);
    $copies_available = intval($_POST['copies_available'] ?? 1);
    $image = $_POST['existing_image'] ?? '';

    if (!$book_id) {
        $errors[] = "Invalid book ID.";
    }
    if (!$title) {
        $errors[] = "Title is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($tmp_name, $destination)) {
            $image = $destination;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (count($errors) === 0) {
        $query = "UPDATE books SET title = $1, author = $2, isbn = $3, published_year = $4, copies_available = $5, image = $6 WHERE book_id = $7";
        $result = pg_query_params($dbconn, $query, [$title, $author, $isbn, $published_year, $copies_available, $image, $book_id]);
        if ($result) {
            $success_message = "Book updated successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Delete Book
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    if ($book_id) {
        $query = "DELETE FROM books WHERE book_id = $1";
        $result = pg_query_params($dbconn, $query, [$book_id]);
        if ($result) {
            $success_message = "Book deleted successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

$search = trim($_GET['search'] ?? '');

// Pagination setup
$limit = 5;
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total books count with search filter
if ($search !== '') {
    $count_query = "SELECT COUNT(*) FROM books WHERE title ILIKE $1 OR author ILIKE $1 OR isbn ILIKE $1";
    $count_result = pg_query_params($dbconn, $count_query, ['%' . $search . '%']);
    $count_row = pg_fetch_row($count_result);
    $total_books = intval($count_row[0]);

    $query = "SELECT * FROM books WHERE title ILIKE $1 OR author ILIKE $1 OR isbn ILIKE $1 ORDER BY book_id DESC LIMIT $2 OFFSET $3";
    $result = pg_query_params($dbconn, $query, ['%' . $search . '%', $limit, $offset]);
} else {
    $total_result = pg_query($dbconn, "SELECT COUNT(*) FROM books");
    $total_row = pg_fetch_row($total_result);
    $total_books = intval($total_row[0]);

    $query = "SELECT * FROM books ORDER BY book_id DESC LIMIT $1 OFFSET $2";
    $result = pg_query_params($dbconn, $query, [$limit, $offset]);
}

$total_pages = ceil($total_books / $limit);
$books = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $books[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ratang Bana Pre School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
           min-height: 100vh;
            display: flex;
            flex-direction: row;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: whitesmoke;
            margin: 0;
        }
        .sidebar {
            width: 260px;
            background:white;
            color: #aaa;
            min-height: 100vh;
            padding: 1.5rem 1rem;
            display: flex;
            border-top-right-radius: 8rem;
            border-bottom-right-radius: 8rem;
            flex-direction: column;
        }
         .sidebar h3 {
             background:linear-gradient(90deg,#aaa,#333,silver,rgb(70, 0, 73),rgb(70, 0, 73),rgb(1, 157, 189),rgb(0, 63, 73),rgb(0, 204, 204),rgb(0, 128, 36),rgb(92, 1, 117),transparent,transparent,transparent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size:400% 300%;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 2px;
            animation: logo 10s infinite;
        }
          @keyframes logo{
            0%{
                background-position:0%;
            }
            50%{
                background-position:100%;
            }
            100%{
                background-position:0%;
            }
        }
        .sidebar a {
             color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            
            transition: background-color 0.3s ease;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        .sidebar a:hover, .sidebar a.active {
             background-color:rgb(0, 85, 111);
            text-decoration: none;
            color:white;
        }
        .content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
        }
            h2 {
            font-weight: 700;
            color:rgb(0, 117, 121);
            border-top:3px solid rgb(0, 85, 111);
            padding-top:1rem;
            padding-bottom:1rem;
        }
        .pagination {
            justify-content: center;
        }
        .book{
            height:3rem;
            width:100%;
            border:2px solid black;
            border-radius:5px;
            transition:all 1s ease-in-out;
        }
        .book:hover{
            transform:scale(2) rotateY(360deg);
        }
          .btn-add{
            background:rgb(0, 247, 255);
            color:white;
             box-shadow:0 4px 10px rgba(0, 0, 0, 0.2);
             border:none;
            transition:all 0.2s ease;
        }
        .btn-add:hover{
            background:rgba(0, 247, 255, 0.53);
            transform:scale(0.95);
            box-shadow:0 4px 10px rgba(0, 0, 0, 0.19);
        }
         .btn-edit{
            background:rgb(0, 63, 73);
            color:white;
             border:none;
            transition:all 0.5s ease;
        }
        .btn-edit:hover{
            background:none;
            color:rgb(0, 63, 73);
            transform:scale(2);
        }
            .btn-del{
             background:rgb(109, 1, 1);
            color:white;
            transition:all 0.5s ease;
        }
         .btn-del:hover{
            background:none;
            color:rgb(109, 1, 1);
            transform:scale(2);
        }
        /* Add small image preview size in modals 
        .modal-content img.book {
            width: 50px !important;
            height: 50px !important;
            object-fit: cover !important;
            border-radius: 5px !important;
            border: 2px solid silver !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
            margin-top: 0.5rem !important;
        }*/
    </style>
    <nav class="sidebar">
        <button id="sidebarToggleSidebar" class="btn btn-outline-light mb-3 d-md-none" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3>Manage</h3>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learners.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php" class="active"><i class="fas fa-book"></i> Manage Books</a>
       <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="settings.php"><i class="fas fa-gear"></i> settings</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="container mt-4">
            <h2>Manage Books</h2>
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN" value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                </div>
            </form>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <button class="btn btn-add mb-3" data-bs-toggle="modal" data-bs-target="#addBookModal"><i class="fa-solid fa-plus"></i> Add Book</button>

            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>BOOK</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Published year</th>
                        <th>Copies Available</th>
                        <th>Action<th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <?php if ($book['image'] && file_exists($book['image'])): ?>
                                    <img class="book" src="<?= htmlspecialchars($book['image']) ?>" alt="Book Image" class="table-img" />
                                <?php else: ?>
                                    <i class="fas fa-book fa-2x text-secondary"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= htmlspecialchars($book['published_year']) ?></td>
                            <td><?= htmlspecialchars($book['copies_available']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#editBookModal<?= $book['book_id'] ?>"><i class="fa-solid fa-pen-to-square"></i></button>
                                <a href="?delete=<?= $book['book_id'] ?>" class="btn btn-sm btn-del" onclick="return confirm('Are you sure you want to delete this book?');"><i class="fa-solid fa-trash"></i></a>
                                <?php include 'edit_book_modal.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Add Book Modal -->
            <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" action="manage_book.php" enctype="multipart/form-data" class="modal-content">
                  <input type="hidden" name="action" value="add" />
                  <div class="modal-header">
                    <h5 class="modal-title" id="addBookModalLabel">Add Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="title" class="form-label">Title</label>
                      <input type="text" class="form-control" id="title" name="title" required />
                    </div>
                    <div class="mb-3">
                      <label for="author" class="form-label">Author</label>
                      <input type="text" class="form-control" id="author" name="author" />
                    </div>
                    <div class="mb-3">
                      <label for="isbn" class="form-label">ISBN</label>
                      <input type="text" class="form-control" id="isbn" name="isbn" />
                    </div>
                    <div class="mb-3">
                      <label for="published_year" class="form-label">Published Year</label>
                      <input type="number" class="form-control" id="published_year" name="published_year" />
                    </div>
                    <div class="mb-3">
                      <label for="copies_available" class="form-label">Copies Available</label>
                      <input type="number" class="form-control" id="copies_available" name="copies_available" min="0" value="1" />
                    </div>
                    <div class="mb-3">
                      <label for="image" class="form-label">Image</label>
                      <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Book</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?= ($p === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
      // Disallow numbers in author input
      document.getElementById('author').addEventListener('input', function(e) {
        this.value = this.value.replace(/[0-9]/g, '');
      });
    </script>
</body>
</html>
