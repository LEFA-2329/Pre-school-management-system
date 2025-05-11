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

// Handle Add Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');
    $image = '';

    if (!$full_name) {
        $errors[] = "Full name is required.";
    }
    if (!$hire_date) {
        $errors[] = "Hire date is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/teachers/';
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
        $query = "INSERT INTO teachers (full_name, email, phone, hire_date, specialization, image) VALUES ($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($dbconn, $query, [$full_name, $email, $phone, $hire_date, $specialization, $image]);
        if ($result) {
            $success_message = "Teacher added successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Update Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $teacher_id = intval($_POST['teacher_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');
    $image = $_POST['existing_image'] ?? '';

    if (!$teacher_id) {
        $errors[] = "Invalid teacher ID.";
    }
    if (!$full_name) {
        $errors[] = "Full name is required.";
    }
    if (!$hire_date) {
        $errors[] = "Hire date is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/teachers/';
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
        $query = "UPDATE teachers SET full_name = $1, email = $2, phone = $3, hire_date = $4, specialization = $5, image = $6 WHERE teacher_id = $7";
        $result = pg_query_params($dbconn, $query, [$full_name, $email, $phone, $hire_date, $specialization, $image, $teacher_id]);
        if ($result) {
            $success_message = "Teacher updated successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle Delete Teacher
if (isset($_GET['delete'])) {
    $teacher_id = intval($_GET['delete']);
    if ($teacher_id) {
        $query = "DELETE FROM teachers WHERE teacher_id = $1";
        $result = pg_query_params($dbconn, $query, [$teacher_id]);
        if ($result) {
            $success_message = "Teacher deleted successfully.";
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

// Fetch total teachers count with search filter
if ($search !== '') {
    $count_query = "SELECT COUNT(*) FROM teachers WHERE full_name ILIKE $1 OR email ILIKE $1 OR specialization ILIKE $1";
    $count_result = pg_query_params($dbconn, $count_query, ['%' . $search . '%']);
    $count_row = pg_fetch_row($count_result);
    $total_teachers = intval($count_row[0]);

    $query = "SELECT * FROM teachers WHERE full_name ILIKE $1 OR email ILIKE $1 OR specialization ILIKE $1 ORDER BY teacher_id DESC LIMIT $2 OFFSET $3";
    $result = pg_query_params($dbconn, $query, ['%' . $search . '%', $limit, $offset]);
} else {
    $total_result = pg_query($dbconn, "SELECT COUNT(*) FROM teachers");
    $total_row = pg_fetch_row($total_result);
    $total_teachers = intval($total_row[0]);

    $query = "SELECT * FROM teachers ORDER BY teacher_id DESC LIMIT $1 OFFSET $2";
    $result = pg_query_params($dbconn, $query, [$limit, $offset]);
}

$total_pages = ceil($total_teachers / $limit);
$teachers = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $teachers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Teachers - TinySteps Admin</title>
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
        <a href="manage_learners.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
         <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="#"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="container mt-4">
            <h2>Manage Teachers</h2>
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or specialization" value="<?= htmlspecialchars($search) ?>" />
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

            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add Teacher</button>

            <!-- Add Teacher Modal -->
            <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" action="manage_teachers.php" enctype="multipart/form-data" class="modal-content">
                  <input type="hidden" name="action" value="add" />
                  <div class="modal-header">
                    <h5 class="modal-title" id="addTeacherModalLabel">Add Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="full_name" class="form-label">Full Name</label>
                      <input type="text" class="form-control" id="full_name" name="full_name" required />
                    </div>
                    <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" class="form-control" id="email" name="email" />
                    </div>
                    <div class="mb-3">
                      <label for="phone" class="form-label">Phone</label>
                      <input type="text" class="form-control" id="phone" name="phone" />
                    </div>
                    <div class="mb-3">
                      <label for="hire_date" class="form-label">Hire Date</label>
                      <input type="date" class="form-control" id="hire_date" name="hire_date" required />
                    </div>
                    <div class="mb-3">
                      <label for="specialization" class="form-label">Specialization</label>
                      <input type="text" class="form-control" id="specialization" name="specialization" />
                    </div>
                    <div class="mb-3">
                      <label for="image" class="form-label">Image</label>
                      <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Teacher</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hire Date</th>
                        <th>Specialization</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td>
                                <?php if ($teacher['image'] && file_exists($teacher['image'])): ?>
                                    <img src="<?= htmlspecialchars($teacher['image']) ?>" alt="Teacher Image" class="table-img" />
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($teacher['full_name']) ?></td>
                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                            <td><?= htmlspecialchars($teacher['phone']) ?></td>
                            <td><?= htmlspecialchars($teacher['hire_date']) ?></td>
                            <td><?= htmlspecialchars($teacher['specialization']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTeacherModal<?= $teacher['teacher_id'] ?>">Edit</button>
                                <a href="?delete=<?= $teacher['teacher_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
                            </td>
                        </tr>
                        <?php include 'edit_teacher_modal.php'; ?>
                    <?php endforeach; ?>
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
</body>
</html>
