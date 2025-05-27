<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success_message = '';

// Handle borrow book action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    $learner_id = intval($_POST['learner_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $borrow_date = $_POST['borrow_date'] ?? '';

    if (!$learner_id) {
        $errors[] = "Invalid learner selected.";
    }
    if (!$book_id) {
        $errors[] = "Invalid book selected.";
    }
    if (!$borrow_date) {
        $errors[] = "Borrow date is required.";
    }

    if (count($errors) === 0) {
        // Calculate due_date as 14 days after borrow_date
        $due_date = date('Y-m-d', strtotime($borrow_date . ' +14 days'));

        // Insert borrow record with due_date
        $query = "INSERT INTO borrow_records (learner_id, book_id, borrow_date, due_date, return_date, status) VALUES ($1, $2, $3, $4, NULL, $5)";
        $status = 'borrowed';
        $result = pg_query_params($dbconn, $query, [$learner_id, $book_id, $borrow_date, $due_date, $status]);
        if ($result) {
            $success_message = "Book borrowed successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Handle return book action
if (isset($_GET['return'])) {
    $borrow_id = intval($_GET['return']);
    if ($borrow_id) {
        $return_date = date('Y-m-d');
        $query = "UPDATE borrow_records SET return_date = $1 WHERE borrow_id = $2";
        $result = pg_query_params($dbconn, $query, [$return_date, $borrow_id]);
        if ($result) {
            $success_message = "Book returned successfully.";
        } else {
            $errors[] = "Database error: " . pg_last_error($dbconn);
        }
    }
}

// Fetch borrow records with learner and book info
$query = "SELECT br.borrow_id, br.borrow_date, br.return_date, l.first_name, l.last_name, b.title
          FROM borrow_records br
          JOIN learners l ON br.learner_id = l.learner_id
          JOIN books b ON br.book_id = b.book_id
          ORDER BY br.borrow_date DESC";
$result = pg_query($dbconn, $query);
$borrow_records = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $borrow_records[] = $row;
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
    </style>
</head>
<body>
    <nav class="sidebar">
        <button id="sidebarToggleSidebar" class="btn btn-outline-light mb-3 d-md-none" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3>Manage</h3>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learners.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php" class="active"><i class="fas fa-book-reader"></i> Borrow Records</a>
          <a href="settings.php"><i class="fas fa-gear"></i> settings</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="container mt-4">
            <h2>Manage Borrow Records</h2>
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
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Learner</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrow_records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                            <td><?= htmlspecialchars($record['title']) ?></td>
                            <td><?= htmlspecialchars($record['borrow_date']) ?></td>
                            <td><?= htmlspecialchars($record['return_date'] ?? '') ?></td>
                            <td><?= $record['return_date'] ? 'Returned' : 'Borrowed' ?></td>
                            <td>
                                <?php if (!$record['return_date']): ?>
                                    <a href="?return=<?= $record['borrow_id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this book as returned?');">Return</a>
                                <?php else: ?>
                                    <span class="text-muted">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($borrow_records) === 0): ?>
                        <tr><td colspan="6" class="text-center">No borrow records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
