<?php
require_once 'config.php';

$errors = [];
$success_message = '';

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_username($username) {
    // Username: 3-20 characters, alphanumeric, underscores, and @ symbol
    return preg_match('/^[a-zA-Z0-9_@]{3,20}$/', $username);
}

function validate_password($password) {
    // Password: minimum 8 characters, at least one uppercase, one lowercase, one digit, one special char
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');

    if (!$username || !validate_username($username)) {
        $errors[] = "Invalid username. Use 3-20 alphanumeric characters or underscores.";
    }

    if (!$email || !validate_email($email)) {
        $errors[] = "Invalid email address.";
    }

    if (!$password || !validate_password($password)) {
        $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, digit, and special character.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    if (!$full_name) {
        $errors[] = "Full name is required.";
    }

    if (count($errors) === 0) {
        // Check if username or email already exists
        $check_query = "SELECT admin_id FROM admins WHERE username = $1 OR email = $2";
        $result = pg_query_params($dbconn, $check_query, [$username, $email]);

        if (!$result) {
            die("Database query error: " . pg_last_error());
        }

        if (pg_num_rows($result) > 0) {
            $errors[] = "Username or email already exists.";
        } else {
            // Insert new admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO admins (username, password_hash, full_name, email) VALUES ($1, $2, $3, $4)";
            $insert_result = pg_query_params($dbconn, $insert_query, [$username, $password_hash, $full_name, $email]);

            if ($insert_result) {
                $success_message = "Signup successful. You can now <a href='login.php'>login</a>.";
            } else {
                $errors[] = "Failed to create admin: " . pg_last_error();
            }
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
    <script>
        function validateForm() {
            const username = document.forms["signupForm"]["username"].value.trim();
            const email = document.forms["signupForm"]["email"].value.trim();
            const password = document.forms["signupForm"]["password"].value;
            const fullName = document.forms["signupForm"]["full_name"].value.trim();
            const usernameRegex =  /^[a-zA-Z0-9_@]{3,20}$/;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            let errors = [];

            if (!usernameRegex.test(username)) {
                errors.push("Username must be 3-20 characters, alphanumeric or underscores.");
            }
            if (!email || !email.includes("@")) {
                errors.push("Please enter a valid email address.");
            }
            if (!passwordRegex.test(password)) {
                errors.push("Password must be at least 8 characters and include uppercase, lowercase, digit, and special character.");
            }
            if (!fullName) {
                errors.push("Full name is required.");
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
            border-top:3px solid rgb(0, 142, 198);
            border-radius:none;
        }
        .logo{
            background:linear-gradient(90deg,silver,silver,rgb(176, 3, 173),rgb(143, 2, 179),rgb(225, 224, 225),transparent,transparent,rgb(1, 104, 109),goldenrod,goldenrod,transparent,transparent,transparent);
            -webkit-background-clip: text;
            background-clip:text;
            color:transparent;
            background-size:400% 400%;
            letter-spacing: 0.2rem;
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
            background:ghostwhite;
            border-radius:3px;
            border-bottom:1px solid silver;
            transition:all 0.3s ease;
        }
        .input:focus{
            border:none;
            outline:none;
            color:black;
             border-bottom:1px solid rgba(0, 120, 168, 0.61);
        }
        .btn-login{
            background:linear-gradient(100deg,rgb(1, 57, 79),rgb(0, 142, 198));
            color: white;
            border: none;
        }
        .sign{
            text-decoration:none;
            color:aqua;
            transition:all 0.3s ease-in-out;
        }
        .sign:hover{
            filter:brightness(40%);
        }
    </style>
</head>
<body>
    <div class="d-flex vh-100">
        <div class="container my-auto">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card-1 shadow p-4">
                        <h2 class="text-center mb-4 logo">Admin Signup</h2>
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
<form name="signupForm" action="signup.php" method="POST" onsubmit="return validateForm()">
    <div class="mb-3">
        <label for="full_name" class="form-label">Admin Code</label>
        <input type="text" class="form-control input" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? '') ?>" />
    </div>
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control input" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" />
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control input" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" />
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control input" id="password" name="password" required />
        <div id="passwordHelp" class="form-text">
            At least 8 characters, including uppercase, lowercase, number, and special character.
        </div>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control input" id="confirm_password" name="confirm_password" required />
    </div>
    <button type="submit" class="btn-login w-100">Sign Up</button>
    <p class="mt-3 text-center" style="color:brown">Already have an account? <a class="sign" href="login.php">Login here</a>.</p>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
