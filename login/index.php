<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION["s_role"] == "customer") {
        header('Location: ../home');
    } elseif ($_SESSION["s_role"] == "admin") {
        header('Location: ../admin');
    }
    exit;
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (!$con) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Initialize variables for error/success messages
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $errorMessage = 'Please fill both the username/email and password fields!';
    } else {
        $input = trim($_POST['username']); // Username or Email
        $password = $_POST['password'];

        // Check if input is an email (contains '@')
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT * FROM users WHERE email = '$input'";
        } else {
            $query = "SELECT * FROM users WHERE username = '$input'";
        }

        $select_user = mysqli_query($con, $query);

        if (mysqli_num_rows($select_user) == 0) {
            $errorMessage = "User not found";
        } else {
            $row = mysqli_fetch_assoc($select_user);
            $db_username = $row['username'];
            $db_user_password = $row['password'];
            $user_firstname = $row['firstname'];
            $user_lastname = $row['lastname'];
            $db_user_role = $row['user_role'];
            $id = $row['id'];
            $_SESSION["firstname"] = $user_firstname;
            $status = $row["status"];

            if ($status == "1") {
                if (password_verify($password, $db_user_password)) {
                    // Store session details
                    $_SESSION['s_username'] = $db_username;
                    $_SESSION['s_role'] = $db_user_role;
                    $_SESSION['loggedin'] = TRUE;
                    $_SESSION['name'] = $db_username;
                    $_SESSION['id'] = $id;
                    $_SESSION['lastname'] = $user_lastname;
                    $_SESSION['email'] = $row["email"];
                    $_SESSION['phoneno'] = $row["phoneno"];

                    // Redirect user
                    if ($db_user_role == 'admin') {
                        header("Location: ../admin");
                    } elseif ($db_user_role == 'customer') {
                        if (isset($_SESSION['redirect_to'])) {
                            $redirect_url = $_SESSION['redirect_to'];
                            unset($_SESSION['redirect_to']);
                            header("Location: $redirect_url");
                        } else {
                            header("Location: ../home/");
                        }
                    }
                    exit;
                } else {
                    $errorMessage = "Username/Email or Password is incorrect";
                }
            } elseif ($status == "0") {
                $errorMessage = "Please click on the link in your email to activate your account.";
            } elseif ($status == "2") {
                $errorMessage = "This account is disabled. Please contact the Administrator.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="..css/style.css">
    <!-- <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="util.css"> -->
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <style>
        .message {
            text-align: center;
            margin-top: 10px;
            color: #d9534f;
        }
        .success {
            color: #28a745;
        }
        header {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); 
}

    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
        <button class="hamburger" aria-label="Toggle menu">
            ☰
        </button>
        <ul class="nav-links">
            <li><a href="../contactus/">Contact</a></li>
            <li><a href="../aboutus/">About Us</a></li>
            <li><a href="../policies/">Policies</a></li>
        </ul>
    </nav>
</header>

<div class="background">
    <div class="login">
        <h1>Login</h1>
         <!-- Success and error messages -->
         <?php if (!empty($errorMessage)): ?>
                <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>
        <form method="post">
            
            <label for="username">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username" placeholder="Username/Email" id="username" required>
            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            
           
            
            <input class="button" type="submit" value="Login">

            
            <div class="msg" style="width: 100%; margin-bottom: 15px; font-size: 14px;"> <a class="signup" href="../password_reset/">Forgotten your password?</a></div>
           
            
            <div class="msg">New to RN Bus? <a class="signup" href="../signup/">Sign Up</a>!</div>
        </form>
    </div>
</div>

<footer class="footer">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

<script>
      document.addEventListener("DOMContentLoaded", function() {
    // Add click event to the hamburger button
    document.querySelector('.hamburger').addEventListener('click', function() {
        // Toggle the 'active' class on the nav-links
        document.querySelector('.nav-links').classList.toggle('active');
    });
});


</script>
</body>
</html>
