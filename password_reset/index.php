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

if (isset($_SESSION['successMessage']) && $_SESSION['successMessage'] == 'OTP sent to your email.') {
    // Redirect after 5 seconds to otp_password.php, passing the email as a URL parameter
    $_SESSION["reset"]=1;
    header("refresh:2;url=verify_otp.php?token=" . urlencode($_SESSION['token']));
    // header("refresh:2;url=verify_otp.php?token=" . urlencode($_SESSION['token']). "&email=". urlencode($_SESSION['resetemail']));
    unset($_SESSION['token']);
    unset($_SESSION['successMessage']);
    // unset($_SESSION['resetemail']);
    exit; // Don't forget to exit after header to stop further code execution
}


$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = trim($_POST['username']); // 'username' can be email or regular username

    // Check if the input is a valid email
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $is_email = true;
        $email = filter_var($input, FILTER_SANITIZE_EMAIL);
        $query_param = $email;
    } else {
        $is_email = false;
        $username = $input;
        $query_param = $username;
    }

    if ($input=="admin") {
        // Store the error message in a session variable
        $_SESSION['errorMessage'] = 'You are not allowed to do this!';
        // Redirect to the same page to display the error
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Check if the user exists (email or username)
    $stmt = $con->prepare($is_email ? "SELECT id, otp_attempts, otp_request_date, email FROM users WHERE email = ?" : "SELECT id, otp_attempts, otp_request_date, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $query_param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $otp_attempts = $row['otp_attempts'];
        $newemail=$row['email'];
        
        // Set the time zone to Asia/Kathmandu
        date_default_timezone_set('Asia/Kathmandu');

        // Now create the DateTime objects
        $otp_request_date = new DateTime($row['otp_request_date']);
        $current_time = new DateTime(); // Get current time in Asia/Kathmandu timezone

        // Convert current date and otp_request_date to total minutes
        $current_minutes = $current_time->format('U') / 60; // Unix timestamp to minutes
        $otp_request_minutes = $otp_request_date->format('U') / 60; // Unix timestamp to minutes

        // Calculate the difference in minutes
        $diff_minutes = $current_minutes - $otp_request_minutes;

        // if ($newemail=="admin@aryanrauniyar.com.np") {
        //     // Store the error message in a session variable
        //     $_SESSION['errorMessage'] = 'You are not allowed to do this!';
        //     // Redirect to the same page to display the error
        //     header("Location: " . $_SERVER['PHP_SELF']);
        //     exit;
        // }
        // Check if OTP attempts exceed the limit (3) within the hour
        if ($otp_attempts >= 3 && $diff_minutes < 60) {
            // Store the error message in a session variable
            $_SESSION['errorMessage'] = 'Too many OTP requests. Try again later.';
            // Redirect to the same page to display the error
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // If more than 1 hour (60 minutes) has passed, reset attempts to 0
        if ($diff_minutes >= 60) {
            $stmt = $con->prepare("UPDATE users SET otp_attempts = 0 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        // Generate 6-digit OTP securely
        $otp = random_int(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $token = bin2hex(random_bytes(16));

        // Update OTP in the database
        $stmt = $con->prepare("UPDATE users SET password_reset_otp = ?, otp_expiry = ?, otp_attempts = otp_attempts + 1, otp_request_date = NOW(), otp_token = ? WHERE id = ?");
        $stmt->bind_param("sssi", $otp, $otp_expiry, $token, $user_id);

        if (!$stmt->execute()) {
            $_SESSION['errorMessage'] = 'Failed to update OTP in the database.';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Send OTP email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'info.rnbus@gmail.com';
            $mail->Password = 'uuzn fxwa aejr gbhr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('info.rnbus@gmail.com', 'RN Bus Pvt. Ltd.');
            $mail->addAddress($newemail); // Send email to the user (if valid email was used)
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "<p>Dear user,</p><p>Your OTP code is: <strong>$otp</strong></p><p>This OTP is valid for 10 minutes.</p><p>Regards,<br>RN Bus Pvt. Ltd.</p>";
            $mail->send();

            unset($_SESSION['successMessage']);
            unset($_SESSION['resetemail']);
            $_SESSION['successMessage'] = 'OTP sent to your email.';
            $_SESSION['resetemail'] = $newemail;
            $_SESSION['token'] = $token;

            // Redirect after sending OTP
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
            
        } catch (Exception $e) {
            $_SESSION['errorMessage'] = 'Failed to send OTP. Try again.';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['errorMessage'] = 'Username or email not found.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
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

.msg:hover{
    color: black;
}

.message {
            text-align: center;
            margin-top: 10px;
        }
        .error { color: #d9534f; }
        .success { color: #28a745; }

        
       /* Loader styles */
.loader {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 0px solid #f3f3f3;
    border-top: 0px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    z-index: 9999;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-content p {
    margin-bottom: 20px;
}

.modal-content button {
    padding: 10px 20px;
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

/* Loading overlay */
#loadingOverlay {
    display: none; /* Hidden by default */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    z-index: 9999; /* Ensure it is on top */
    justify-content: center;
    align-items: center;
}

/* Spinner */
.spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #f8b600;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

/* Spin animation */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
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

    <div class="loader" id="loader"></div>
    <div id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    

        <!-- <h1>Login</h1> -->
         <h1>
            <img src="../images/lock.png" style="margin-top: 5px; height: 70px;">
            <p style="font-size: 20px;">Trouble with logging in?</p>
            <p style="font-size: 13px;">Enter your email address or username.</p>
        </h1>
         <!-- Success and error messages -->
<?php if (!empty($_SESSION['errorMessage'])): ?>
    <p class="message error"><?= htmlspecialchars($_SESSION['errorMessage']); ?></p>
    <?php unset($_SESSION['errorMessage']); ?> <!-- Clear the session error message -->
<?php endif; ?>
<?php if (!empty($_SESSION['successMessage'])): ?>
    <p class="message success"><?= htmlspecialchars($_SESSION['successMessage']); ?></p>
    <?php unset($_SESSION['successMessage']); ?> <!-- Clear the session success message -->
<?php endif; ?>

        <form method="post">
            
            <label for="username">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="username" placeholder="Username/Email" id="username" required>
            <!-- <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password" placeholder="Password" id="password" required> -->
            
           
            
            <input class="button" type="submit" value="Submit">

            
            
            <div class="msg" onclick="location.href='../login/';" style="cursor: pointer;">Back to Login!</div>
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


// Wait for the document to be fully loaded
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form"); // Select the form
    const loader = document.getElementById("loader"); // Select the loader
    const loadingOverlay = document.getElementById("loadingOverlay"); // Select the overlay

    // When the form is submitted, show the spinner and disable the form to prevent multiple submissions
    form.addEventListener("submit", function(event) {
        // Show the loader and overlay
        loadingOverlay.style.display = "flex";
        loader.style.display = "block";

        // Optionally, disable the form fields or submit button to prevent further actions while loading
        const submitButton = form.querySelector("button[type='submit']");
        submitButton.disabled = true;

        // You can handle the form submission with AJAX if you want to avoid page reload
        // If you're submitting the form traditionally, the page will reload, and the loader will be hidden by the server response
    });
});




</script>


</body>
</html>
