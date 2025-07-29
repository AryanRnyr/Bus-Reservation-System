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
    die('Database connection failed: ' . mysqli_connect_error());
}
if($_SESSION['reset']==0){
    header("Location: index.php");
}

if (isset($_GET['token'])) {
    $gettoken = $_GET['token'];
    // $getemail = $_GET['email'];
    $getemail = $_SESSION['resetemail'];
    unset($_SESSION['resetemail']);

    unset($_SESSION["gettoken"]);
    // unset($_SESSION["getemail"]);

    $_SESSION['gettoken']=$gettoken;
    // $_SESSION['getemail']=$getemail;
}
// if (isset($_GET['token']) && isset($_GET['email'])) {
//     $gettoken = $_GET['token'];
//     $getemail = $_GET['email'];

//     unset($_SESSION["gettoken"]);
//     unset($_SESSION["getemail"]);

//     $_SESSION['gettoken']=$gettoken;
//     $_SESSION['getemail']=$getemail;
// }


// Ensure token exists before processing OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['token']) && $_POST['token'] === $gettoken) {
        $token = $_POST['token'];
        $otp = trim($_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6']);

        
        // Prepare query to check OTP and token
        $stmt = $con->prepare("SELECT password_reset_otp, otp_expiry, otp_token, email, id FROM users WHERE otp_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $db_otp = trim($user['password_reset_otp']); // Ensure no spaces
            $otp_expiry = $user['otp_expiry']; // Get expiry timestamp from DB

            // Set Kathmandu timezone (+5:45)
            date_default_timezone_set('Asia/Kathmandu');
            $current_time = time(); // Get current timestamp
            $otp_expiry_timestamp = strtotime($otp_expiry); // Convert to timestamp

           

            // Convert timestamps to minutes
            function convertToMinutes($timestamp) {
                return (date("Y", $timestamp) * 365 * 24 * 60) +
                       (date("m", $timestamp) * 30 * 24 * 60) +
                       (date("d", $timestamp) * 24 * 60) +
                       (date("H", $timestamp) * 60) +
                       date("i", $timestamp);
            }

            $otp_expiry_minutes = convertToMinutes($otp_expiry_timestamp);
            $current_time_minutes = convertToMinutes($current_time);
            $difference_in_minutes = $current_time_minutes - $otp_expiry_minutes;

           

            // Validate OTP and expiration time
            if ($db_otp === $otp) {
                if ($difference_in_minutes <= 10) { // OTP is valid if within 10 minutes
                    $_SESSION['reset_email'] = $user['email'];
                    $_SESSION['reset_token'] = $user['otp_token']; 
                    $_SESSION['reset_otp'] = $otp;
                    header("Location: reset_password.php");
                    exit;
                } else {
                    $_SESSION['errorMessage'] = 'OTP has expired';
                    header("Location: " . $_SERVER['PHP_SELF']);
                }
            } else {
                $_SESSION['errorMessage'] = 'Invalid OTP';
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        } else {
            $_SESSION['errorMessage'] = 'Invalid token or email not found';
            header("Location: " . $_SERVER['PHP_SELF']);
        }
    } else {
        $_SESSION['errorMessage'] = 'Invalid request';
        header("Location: " . $_SERVER['PHP_SELF']);
    }
    exit;
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


.symboltick {
  height: 65px;
  width: 65px;
  background: #4070f4;
  color: #fff;
  font-size: 2.5rem;
  border-radius: 50%;


  display: flex; /* Enable flexbox for the header */
    justify-content: center; /* Center the icon horizontally */
    align-items: center; /* Center the icon vertically */
  
    
}

.beforesymbol {
    display: flex; /* Enable flexbox */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    margin-bottom: 15px;
  }

  /* .otp-input {
  
  text-align: center; 
  font-size: 24px; 
  border: 1px solid #ccc; 
  border-radius: 5px; 
  letter-spacing: 20px; 
  border-radius: 0%;
}

.otp-input:focus {
  border-color: #4CAF50;
  outline: 0.09px solid #4070f4; 
  
} */
.otp-container {
  display: flex;
  justify-content: center;
  gap: 2px; /* Space between the boxes */
}

.otp-input {

  text-align: center; /* Center text inside the box */
  font-size: 20px; /* Font size for the digits */
  border: 1px solid #ccc; /* Border style */
  border-radius: 5px; /* Optional: rounded corners */
  margin: 5px; /* Optional: spacing around the input */
  
}

.otp-input:focus {
  border-color: #4070f4; 
  box-shadow: inset 0 0 5px #4070f4; /* Inner focus effect */
  outline: none; /* Remove default focus outline */
}

    </style>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
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
            <!-- <img src="../images/lock.png" style="margin-top: 5px; height: 70px;"> -->
             
            
          <div class="beforesymbol">  <header class="symboltick">
        <i class="bx bxs-check-shield"></i>
      </header></div>
             
            <!-- <p style="font-size: 20px;">Trouble with logging in?</p> -->
            <?php
            $hiddenemail = $_SESSION['getemail'];
            $at_pos = strpos($hiddenemail, '@'); // Find position of '@'
            $first_part = substr($hiddenemail, 0, 4); // Get first 4 characters
            $last_digit = substr($hiddenemail, $at_pos - 1, 1); // Get the last character before '@'
            $masked_part = str_repeat('*', $at_pos - 5); // Mask the characters in between

            // Combine the parts
            $masked_email = $first_part . $masked_part . $last_digit . substr($hiddenemail, $at_pos);

            ?>
            <p style="font-size: 13px;">Enter the One Time Password (OTP) sent to <strong style="color:maroon"><?= $masked_email; ?></strong></p>
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
            
            <label for="otp" style="margin-top: 5px;">
                <i class="fa fa-key"></i>
            </label>
            <!-- <div class="otp-container">
            <input type="text" name="otp" class="otp-input" placeholder="OTP" id="otp" maxlength="6" autocomplete="off" required style="width: 250px;">
            </div> -->
            <div class="otp-container">
            <input type="text" name="otp1" maxlength="1" id="otp1" class="otp-input" style="width: 35px; padding:0px;" required>
            <input type="text" name="otp2" maxlength="1" id="otp2" class="otp-input" style="width: 35px; padding:0px;" required>
            <input type="text" name="otp3" maxlength="1" id="otp3" class="otp-input" style="width: 35px; padding:0px;" required>
            <input type="text" name="otp4" maxlength="1" id="otp4" class="otp-input" style="width: 35px; padding:0px;" required>
            <input type="text" name="otp5" maxlength="1" id="otp5" class="otp-input" style="width: 35px; padding:0px;" required>
            <input type="text" name="otp6" maxlength="1" id="otp6" class="otp-input" style="width: 35px; padding:0px;" required>
            </div>
            <input type="hidden" name="token" id="token" value="<?= $gettoken; ?>">

            <input class="button" type="submit" value="Submit">

            
            
            <div class="msg" onclick="location.href='index.php';" style="cursor: pointer;">Back to Forget Password!</div>
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




  const otpInputs = document.querySelectorAll('.otp-input');
  
  otpInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
      if (input.value.length === 1 && index < otpInputs.length - 1) {
        otpInputs[index + 1].focus(); // Move to next input box
      }
    });
  });


</script>


</body>
</html>
