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

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_login'])) {
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
            $result = pg_query_params($dbconn, "SELECT password_hash FROM admins WHERE admin_id = $1", array($_SESSION['admin_id']));
            if ($result && pg_num_rows($result) === 1) {
                $row = pg_fetch_assoc($result);
                if (!password_verify($current_password, $row['password_hash'])) {
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
                $update_result = pg_query_params($dbconn, "UPDATE admins SET username = $1, password = $2 WHERE admin_id = $3", array($username, $hashed_password, $_SESSION['admin_id']));
            } else {
                $update_result = pg_query_params($dbconn, "UPDATE admins SET username = $1 WHERE admin_id = $2", array($username, $_SESSION['admin_id']));
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
         .btn-save{
            background:rgb(0, 247, 255);
            color:white;
             box-shadow:0 4px 10px rgba(0, 0, 0, 0.2);
             border:none;
            transition:all 0.2s ease;
        }
        .btn-save:hover{
            background:rgba(0, 247, 255, 0.53);
            transform:scale(0.95);
            box-shadow:0 4px 10px rgba(0, 0, 0, 0.19);
        }
         .btn-upload{
            background:rgb(1, 160, 4);
            color:white;
             box-shadow:0 4px 10px rgba(0, 0, 0, 0.2);
             border:none;
            transition:all 0.2s ease;
        }
        .btn-upload:hover{
            background:rgb(1, 102, 3);
            transform:scale(0.95);
            box-shadow:0 4px 10px rgba(0, 0, 0, 0.19);
        }
        .btn-update{
            background:rgb(241, 161, 1);
            color:white;
             box-shadow:0 4px 10px rgba(0, 0, 0, 0.2);
             border:none;
            transition:all 0.2s ease;
        }
        .btn-update:hover{
            background:rgb(102, 80, 1);
            transform:scale(0.95);
            box-shadow:0 4px 10px rgba(0, 0, 0, 0.19);
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
       <!-- <a href="manage_learner.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="settings.php" class="active"><i class="fas fa-cogs"></i> Settings</a>-->
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
        

            <div class="mb-4">
                <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#loginDetailsCollapse" aria-expanded="false" aria-controls="loginDetailsCollapse">
                   <i class="fa-solid fa-pen"></i> Update Login Details
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
                        <button type="submit" name="update_login" class="btn btn-update mb-4"><i class="fa-solid fa-thumbs-up"></i>  Update Login Details</button>
                    </form>
                </div>
            </div>

            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <h4>Upload Profile Image</h4>
                <div class="mb-3">
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                </div>
                <button type="submit" name="upload_profile_image" class="btn btn-upload"><i class="fa-solid fa-upload"></i> Upload Image</button>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
