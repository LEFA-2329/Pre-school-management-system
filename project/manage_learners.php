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

// Handle Add Learner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $enrollment_date = $_POST['enrollment_date'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $image = '';

    if (!$first_name) {
        $errors[] = "First name is required.";
    }
    if (!$last_name) {
        $errors[] = "Last name is required.";
    }
    if (!$date_of_birth) {
        $errors[] = "Date of birth is required.";
    }
    if (!$enrollment_date) {
        $errors[] = "Enrollment date is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/learners/';
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
        $query = "INSERT INTO learners (first_name, last_name, date_of_birth, enrollment_date, address, image) VALUES ($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($dbconn, $query, [$first_name, $last_name, $date_of_birth, $enrollment_date, $address, $image]);
        if ($result) {
            $success_message = "Learner added successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Update Learner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $learner_id = intval($_POST['learner_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $enrollment_date = $_POST['enrollment_date'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $image = $_POST['existing_image'] ?? '';

    if (!$learner_id) {
        $errors[] = "Invalid learner ID.";
    }
    if (!$first_name) {
        $errors[] = "First name is required.";
    }
    if (!$last_name) {
        $errors[] = "Last name is required.";
    }
    if (!$date_of_birth) {
        $errors[] = "Date of birth is required.";
    }
    if (!$enrollment_date) {
        $errors[] = "Enrollment date is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/learners/';
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
        $query = "UPDATE learners SET first_name = $1, last_name = $2, date_of_birth = $3, enrollment_date = $4, address = $5, image = $6 WHERE learner_id = $7";
        $result = pg_query_params($dbconn, $query, [$first_name, $last_name, $date_of_birth, $enrollment_date, $address, $image, $learner_id]);
        if ($result) {
            $success_message = "Learner updated successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Delete Learner
if (isset($_GET['delete'])) {
    $learner_id = intval($_GET['delete']);
    if ($learner_id) {
        $query = "DELETE FROM learners WHERE learner_id = $1";
        $result = pg_query_params($dbconn, $query, [$learner_id]);
        if ($result) {
            $success_message = "Learner deleted successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

$search = trim($_GET['search'] ?? '');

// Pagination setup
$limit = 10;
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total learners count with search filter
if ($search !== '') {
    $count_query = "SELECT COUNT(*) FROM learners WHERE first_name ILIKE $1 OR last_name ILIKE $1 OR address ILIKE $1";
    $count_result = pg_query_params($dbconn, $count_query, ['%' . $search . '%']);
    $count_row = pg_fetch_row($count_result);
    $total_learners = intval($count_row[0]);

    $query = "SELECT * FROM learners WHERE first_name ILIKE $1 OR last_name ILIKE $1 OR address ILIKE $1 ORDER BY learner_id DESC LIMIT $2 OFFSET $3";
    $result = pg_query_params($dbconn, $query, ['%' . $search . '%', $limit, $offset]);
} else {
    $total_result = pg_query($dbconn, "SELECT COUNT(*) FROM learners");
    $total_row = pg_fetch_row($total_result);
    $total_learners = intval($total_row[0]);

    $query = "SELECT * FROM learners ORDER BY learner_id DESC LIMIT $1 OFFSET $2";
    $result = pg_query_params($dbconn, $query, [$limit, $offset]);
}

$total_pages = ceil($total_learners / $limit);
$learners = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $learners[] = $row;
    }
}

// Fetch parents for dropdown
$parents_result = pg_query($dbconn, "SELECT parent_id, full_name FROM parents ORDER BY full_name ASC");
$parents = [];
if ($parents_result) {
    while ($row = pg_fetch_assoc($parents_result)) {
        $parents[] = $row;
    }
}

// Fetch books for borrow modal
$books_result = pg_query($dbconn, "SELECT book_id, title FROM books ORDER BY title ASC");
$books = [];
if ($books_result) {
    while ($row = pg_fetch_assoc($books_result)) {
        $books[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Learner - TinySteps Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .sidebar {
            width: 260px;
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
        }
        .sidebar h3 {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 2px;
        }
        .sidebar a {
            color: white;
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
            background-color: #495057;
            text-decoration: none;
        }
        .content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .header h2 {
            font-weight: 700;
            color: #212529;
        }
        .user-info {
            font-size: 1rem;
            color: #6c757d;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
                flex-direction: row;
                overflow-x: auto;
                padding: 0.5rem 1rem;
            }
            .sidebar h3 {
                display: none;
            }
            .sidebar a {
                flex: 1 0 auto;
                margin: 0 0.25rem;
                text-align: center;
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
            .sidebar a i {
                margin: 0 0 4px 0;
                font-size: 1.1rem;
            }
            .content {
                padding: 1rem 1.5rem;
            }
        }
        .table-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .table-img:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .pagination {
            justify-content: center;
        }
    </style>
    <style>
        .modal-content {
            background-color: #fff !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            z-index: 1050 !important;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            background-color: #fff !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            color: #212529 !important;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <button id="sidebarToggleSidebar" class="btn btn-outline-light mb-3 d-md-none" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3>TinySteps Admin</h3>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learner.php" class="active"><i class="fas fa-user-graduate"></i> Manage Learner</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="#"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="container mt-4">
            <h2>Manage Learner</h2>
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by first name, last name, or address" value="<?= htmlspecialchars($search) ?>" />
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

            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addLearnerModal">Add Learner</button>

            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Date of Birth</th>
                        <th>Parent</th>
                        <th>Enrollment Date</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($learners as $learner): ?>
                        <tr>
                            <td>
                                <?php if ($learner['image'] && file_exists($learner['image'])): ?>
                                    <img src="<?= htmlspecialchars($learner['image']) ?>" alt="Learner Image" class="table-img" />
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($learner['first_name']) ?></td>
                            <td><?= htmlspecialchars($learner['last_name']) ?></td>
                                <td><?= htmlspecialchars($learner['date_of_birth']) ?></td>
                                <td>
                                    <?php
                                    $parent_name = '';
                                    $parent_found = false;
                                    foreach ($parents as $parent) {
                                        if (isset($parent['learner_id']) && $parent['learner_id'] == $learner['learner_id']) {
                                            $parent_name = $parent['full_name'];
                                            $parent_found = true;
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($parent_name);
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($learner['enrollment_date']) ?></td>
                            <td><?= htmlspecialchars($learner['address']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editLearnerModal<?= $learner['learner_id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#borrowBookModal" onclick="document.getElementById('learner_id').value = '<?= $learner['learner_id'] ?>';">Borrow Book</button>
                                <a href="?delete=<?= $learner['learner_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this learner?');">Delete</a>
                            </td>
                        </tr>
                        <?php include 'edit_learner_modal.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleSidebarBtn = document.getElementById('sidebarToggleSidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    </script>

    <!-- Add Learner Modal -->
    <div class="modal fade" id="addLearnerModal" tabindex="-1" aria-labelledby="addLearnerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="manage_learner.php" enctype="multipart/form-data" class="modal-content">
          <input type="hidden" name="action" value="add" />
          <div class="modal-header">
            <h5 class="modal-title" id="addLearnerModalLabel">Add Learner</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" id="first_name" name="first_name" required />
            </div>
            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" required />
            </div>
            <div class="mb-3">
              <label for="date_of_birth" class="form-label">Date of Birth</label>
              <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required />
            </div>
            <div class="mb-3 d-none">
              <label for="parent_id" class="form-label">Parent</label>
              <select class="form-select" id="parent_id" name="parent_id" disabled>
                <option value="">Select Parent</option>
                <?php foreach ($parents as $parent): ?>
                  <option value="<?= $parent['parent_id'] ?>"><?= htmlspecialchars($parent['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="enrollment_date" class="form-label">Enrollment Date</label>
              <input type="date" class="form-control" id="enrollment_date" name="enrollment_date" required />
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <textarea class="form-control" id="address" name="address"></textarea>
            </div>
            <div class="mb-3">
              <label for="image" class="form-label">Image</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Add Learner</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
