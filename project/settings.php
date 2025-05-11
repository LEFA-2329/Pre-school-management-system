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

// Fetch current setting from database or session
// For simplicity, using session here; replace with DB if needed
$current_view = $_SESSION['dashboard_view'] ?? 'cards';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_view'])) {
        $view_type = $_POST['view_type'] ?? '';
        if (!in_array($view_type, ['cards', 'charts'])) {
            $errors[] = "Invalid view type selected.";
        } else {
            // Save setting to session or database
            $_SESSION['dashboard_view'] = $view_type;
            $current_view = $view_type;
            $success_message = "Dashboard view preference updated successfully.";
        }
    } elseif (isset($_POST['update_login'])) {
        // Handle login details update
        $username = trim($_POST['username'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username)) {
            $errors[] = "Username cannot be empty.";
        }

        if (!empty($new_password) || !empty($confirm_password)) {
            if ($new_password !== $confirm_password) {
                $errors[] = "New password and confirm password do not match.";
            }
        }

        if (empty($errors)) {
            // Verify current password
            $admin_id = $_SESSION['admin_id'];
            $result = pg_query_params($dbconn, "SELECT password FROM admins WHERE id = $1", array($admin_id));
            if ($result && pg_num_rows($result) === 1) {
                $row = pg_fetch_assoc($result);
                if (!password_verify($current_password, $row['password'])) {
                    $errors[] = "Current password is incorrect.";
                }
            } else {
                $errors[] = "Admin user not found.";
            }
        }

        if (empty($errors)) {
            // Update username and password
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_result = pg_query_params($dbconn, "UPDATE admins SET username = $1, password = $2 WHERE id = $3", array($username, $hashed_password, $admin_id));
            } else {
                $update_result = pg_query_params($dbconn, "UPDATE admins SET username = $1 WHERE id = $2", array($username, $admin_id));
            }
            if ($update_result) {
                $success_message = "Login details updated successfully.";
                $_SESSION['username'] = $username;
            } else {
                $errors[] = "Failed to update login details.";
            }
        }
    } elseif (isset($_POST['upload_profile_image'])) {
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileSize = $_FILES['profile_image']['size'];
            $fileType = $_FILES['profile_image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = './uploads/profile_images/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $newFileName = 'admin_' . $_SESSION['admin_id'] . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $_SESSION['profile_image'] = $dest_path;
                    $success_message = "Profile image uploaded successfully.";
                } else {
                    $errors[] = "There was an error moving the uploaded file.";
                }
            } else {
                $errors[] = "Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions);
            }
        } else {
            $errors[] = "No file uploaded or upload error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings - TinySteps Admin</title>
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
    </style>
</head>
<body>
    <nav class="sidebar">
        <button id="sidebarToggleSidebar" class="btn btn-outline-light mb-3 d-md-none" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3>TinySteps Admin</h3>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learner.php"><i class="fas fa-user-graduate"></i> Manage Learner</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="container mt-4">
            <h2>Settings</h2>
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
            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <h4>Dashboard View Settings</h4>
                <div class="mb-3">
                    <label for="view_type" class="form-label">Dashboard View Type</label>
                    <select class="form-select" id="view_type" name="view_type" required>
                        <option value="cards" <?= $current_view === 'cards' ? 'selected' : '' ?>>Cards</option>
                        <option value="charts" <?= $current_view === 'charts' ? 'selected' : '' ?>>Charts</option>
                    </select>
                </div>
                <button type="submit" name="update_view" class="btn btn-primary mb-4">Save Settings</button>
            </form>

            <div class="mb-4">
                <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#loginDetailsCollapse" aria-expanded="false" aria-controls="loginDetailsCollapse">
                    Update Login Details
                </button>
                <div class="collapse mt-3" id="loginDetailsCollapse">
                    <form method="POST" action="settings.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="submit" name="update_login" class="btn btn-primary mb-4">Update Login Details</button>
                    </form>
                </div>
            </div>

            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <h4>Upload Profile Image</h4>
                <div class="mb-3">
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                </div>
                <button type="submit" name="upload_profile_image" class="btn btn-primary">Upload Image</button>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
