<?php
session_start();
require_once 'config.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy session and redirect to login page
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username) {
        $errors[] = "Username is required.";
    }
    if (!$password) {
        $errors[] = "Password is required.";
    }

    if (count($errors) === 0) {
        $query = "SELECT admin_id, password_hash, full_name FROM admins WHERE username = $1";
        $result = pg_query_params($dbconn, $query, [$username]);

        if (!$result) {
            die("Database query error: " . pg_last_error());
        }

        if (pg_num_rows($result) === 1) {
            $admin = pg_fetch_assoc($result);
            if (password_verify($password, $admin['password_hash'])) {
                // Password is correct, start session
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $admin['full_name'];
                $success_message = "Login successful. Welcome, " . htmlspecialchars($admin['full_name']) . ".";
                // Redirect to admin dashboard or home page
                header('Location: admin_dashboard.php');
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login - TinySteps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script>
        function validateForm() {
            const username = document.forms["loginForm"]["username"].value.trim();
            const password = document.forms["loginForm"]["password"].value;
            let errors = [];

            if (!username) {
                errors.push("Username is required.");
            }
            if (!password) {
                errors.push("Password is required.");
            }

            if (errors.length > 0) {
                alert(errors.join("\\n"));
                return false;
            }
            return true;
        }
    </script>
    <style>
       
        html{
            background-image:url('images/beauty.jpg');
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size:100%;
        }
        body{
            background:linear-gradient(15deg,black,transparent);
        }
        .card-1{
            background:linear-gradient(12deg,black 5%,transparent);
            border:none;
            border-top:3px solid rgb(143, 2, 179);
            border-radius:none;
        }
        .logo{
            background:linear-gradient(90deg,silver,silver,rgb(176, 3, 173),rgb(143, 2, 179),rgb(225, 224, 225),transparent,transparent,rgb(1, 104, 109),goldenrod,goldenrod,transparent,transparent,transparent);
            -webkit-background-clip: text;
            background-clip:text;
            color:transparent;
            background-size:400% 400%;
            animation: logo 10s infinite ease;
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
        .form-label{
            color:rgb(228, 222, 228);
        }
        .input{
            border:none;
            background:white;
            border-radius:3px;
            border-bottom:1px solid silver;
            transition:all 0.3s ease;
        }
        .input:focus{
            border:none;
            outline:none;
            color:black;
             background:rgb(255, 255, 255);
             border-bottom:1px solid rgba(0, 120, 168, 0.61);
        }
        .btn-login{
            background:linear-gradient(100deg,rgb(1, 57, 79),rgb(0, 142, 198));
        }
        

    </style>
</head>
<body>
    <div class="d-flex vh-100">
        <div class="container my-auto">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card-1 shadow p-4">
                        <h2 class="text-center mb-4 logo">Admin Login</h2>
                        <?php
                        if (!empty($errors)) {
                            echo '<div class="alert alert-danger"><ul>';
                            foreach ($errors as $error) {
                                echo "<li>" . htmlspecialchars($error) . "</li>";
                            }
                            echo '</ul></div>';
                        }
                        if ($success_message) {
                            echo '<div class="alert alert-success">' . $success_message . '</div>';
                        }
                        ?>
                        <form name="loginForm" action="login.php" method="POST" onsubmit="return validateForm()">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control input" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" />
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control input" id="password" name="password" required />
                            </div>
                            <button type="submit" class="btn btn-login w-100">Login</button>
                            <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign up here</a>.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
