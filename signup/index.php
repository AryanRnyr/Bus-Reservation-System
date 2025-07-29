<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION["s_role"] == "customer") {
        header('Location: ../home');
    } elseif ($_SESSION["s_role"] == "admin") {
        header('Location: ../admin');
    }
}

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

// Database connection details
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$username = $_POST['username'] ?? '';
$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$phoneno = $_POST['phoneno'] ?? '';
$email = $_POST['email'] ?? '';


// Initialize variables for messages
$successMessage = "";
$errorMessage = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if data was submitted
if (!isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['email'], $_POST['firstname'], $_POST['lastname'], $_POST['phoneno'])) {
    $errorMessage[] = 'Please complete the registration form!';
}

// Validate inputs
// Check if the fields are empty or only contain spaces, then do other validations only if they are not
if (isset($_POST['username'])) {
    // Check if the username contains only spaces
    if (ctype_space($_POST['username'])) {
        $errorMessage[] = 'Username cannot contain only spaces!';
    } elseif (empty($_POST['username'])) {
        $errorMessage[] = 'Username cannot be empty!';
    } else {
        // Username-specific validations
        if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
            $errorMessage[] = 'Username is not valid!';
        }
        if (strlen($_POST['username']) < 5) {
            $errorMessage[] = 'Username must be more than 5 characters (letters or digits)';
        }
    }
}

if (isset($_POST['password'])) {
    // Check if the password contains only spaces
    if (ctype_space($_POST['password'])) {
        $errorMessage[] = 'Password cannot contain only spaces!';
    } elseif (empty($_POST['password'])) {
        $errorMessage[] = 'Password cannot be empty!';
    } else {
        // Password-specific validations
        if (strlen($_POST['password']) < 8) {
            $errorMessage[] = 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $_POST['password'])) {
            $errorMessage[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/\d/', $_POST['password'])) {
            $errorMessage[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[\W]/', $_POST['password'])) {
            $errorMessage[] = 'Password must contain at least one special character (e.g., !@#$%^&*).';
        }
    }
}

if (isset($_POST['confirm_password'])) {
    // Check if the confirm password field contains only spaces
    if (ctype_space($_POST['confirm_password'])) {
        $errorMessage[] = 'Confirm Password cannot contain only spaces!';
    } elseif (empty($_POST['confirm_password'])) {
        $errorMessage[] = 'Confirm Password cannot be empty!';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $errorMessage[] = 'Passwords do not match!';
    }
}

if (isset($_POST['email'])) {
    // Check if the email contains only spaces
    if (ctype_space($_POST['email'])) {
        $errorMessage[] = 'Email cannot contain only spaces!';
    } elseif (empty($_POST['email'])) {
        $errorMessage[] = 'Email cannot be empty!';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMessage[] = 'Email is not valid!';
    }
}

if (isset($_POST['firstname'])) {
    // Check if first name contains only spaces
    if (ctype_space($_POST['firstname'])) {
        $errorMessage[] = 'First name cannot contain only spaces!';
    } elseif (empty($_POST['firstname'])) {
        $errorMessage[] = 'First name cannot be empty!';
    } elseif (preg_match('/^[a-zA-Z]+$/', $_POST['firstname']) == 0) {
        $errorMessage[] = 'First name cannot contain numbers!';
    }
}

if (isset($_POST['lastname'])) {
    // Check if last name contains only spaces
    if (ctype_space($_POST['lastname'])) {
        $errorMessage[] = 'Last name cannot contain only spaces!';
    } elseif (empty($_POST['lastname'])) {
        $errorMessage[] = 'Last name cannot be empty!';
    } elseif (preg_match('/^[a-zA-Z]+$/', $_POST['lastname']) == 0) {
        $errorMessage[] = 'Last name cannot contain numbers!';
    }
}

if (isset($_POST['phoneno'])) {
    // Check if phone number contains only spaces
    if (ctype_space($_POST['phoneno'])) {
        $errorMessage[] = 'Phone number cannot contain only spaces!';
    } elseif (empty($_POST['phoneno'])) {
        $errorMessage[] = 'Phone number cannot be empty!';
    } elseif (strpos($_POST['phoneno'], '-') === 0) {
        // Check if the phone number starts with a negative sign
        $errorMessage[] = 'Phone number cannot be a negative number!';
    } elseif (preg_match('/^\d{10}$/', $_POST['phoneno']) == 0) {
        // Ensure the phone number has exactly 10 digits
        $errorMessage[] = 'Phone number must be exactly 10 digits!';
    }
}

if (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE) {
    // Check if the file was uploaded without errors
    if ($_FILES['file']['error'] != 0) {
        $errorMessage[] = 'Error uploading file!';
    } else {
        // Get the file extension
        $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        
        // Allowed image file types
        $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check if the file is an image
        if (!in_array($fileExtension, $allowedFileTypes)) {
            $errorMessage[] = 'Only image files (JPG, JPEG, PNG, GIF) are allowed!';
        } else {
            // If it's an image, check the file size
            if ($_FILES['file']['size'] > 5 * 1024 * 1024) {  // 5MB
                $errorMessage[] = 'File size must be less than 5MB!';
            }

            // Optionally, check the MIME type (to avoid bypassing extension validation)
            $mimeType = mime_content_type($_FILES['file']['tmp_name']);
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $errorMessage[] = 'Invalid file type! Only JPG, PNG, and GIF images are allowed!';
            }
        }
    }
}




//    // Check if data was submitted
// if (!isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['email'])) {
//     $errorMessage[] = 'Please complete the registration form!';
// }

// // Validate inputs
// if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['email'])) {
//     $errorMessage[] = 'Please complete the registration form!';
// }

// // Check if any field is filled with only spaces
// if (ctype_space($_POST['username'])) {
//     $errorMessage[] = 'Username cannot contain only spaces!';
// }

// if (ctype_space($_POST['password'])) {
//     $errorMessage[] = 'Password cannot contain only spaces!';
// }

// if (ctype_space($_POST['confirm_password'])) {
//     $errorMessage[] = 'Confirm Password cannot contain only spaces!';
// }

// if (ctype_space($_POST['email'])) {
//     $errorMessage[] = 'Email cannot contain only spaces!';
// }

// // Check if first name and last name are filled with only spaces
// if (ctype_space($_POST['firstname'])) {
//     $errorMessage[] = 'First name cannot contain only spaces!';
// }

// if (ctype_space($_POST['lastname'])) {
//     $errorMessage[] = 'Last name cannot contain only spaces!';
// }

// // Check if phone number is filled with only spaces
// if (ctype_space($_POST['phoneno'])) {
//     $errorMessage[] = 'Phone number cannot contain only spaces!';
// }

// if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
//     $errorMessage[] = 'Email is not valid!';
// }

// if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
//     $errorMessage[] = 'Username is not valid!';
// }

// if (strlen($_POST['username']) < 5) {
//     $errorMessage[] = 'Username must be more than 5 characters (letters or digits)';
// }

// // Password validation
// $password = $_POST['password'];

// // First, check if the password is empty or contains only spaces
// if (ctype_space($password)) {
//     $errorMessage[] = "Password cannot contain only spaces.";
// } 
// // Then, check if the password length is at least 8 characters
// elseif (strlen($password) < 8) {
//     $errorMessage[] = "Password must be at least 8 characters long.";
// } 
// else {
//     // Check for uppercase letter
//     if (!preg_match('/[A-Z]/', $password)) {
//         $errorMessage[] = "Password must contain at least one uppercase letter.";
//     }

//     // Check for a number
//     if (!preg_match('/\d/', $password)) {
//         $errorMessage[] = "Password must contain at least one number.";
//     }

//     // Check for a special character
//     if (!preg_match('/[\W]/', $password)) {
//         $errorMessage[] = "Password must contain at least one special character (e.g., !@#$%^&*).";
//     }
// }

// // Check if confirm_password exists and matches (only if password is valid)
// if (empty($errorMessage) && isset($_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
//     $errorMessage[] = 'Passwords do not match!';
// }

// // First name validation
// if (preg_match('/^[a-zA-Z]+$/', $_POST['firstname']) == 0) {
//     $errorMessage[] = 'First name cannot contain numbers!';
// }

// // Last name validation
// if (preg_match('/^[a-zA-Z]+$/', $_POST['lastname']) == 0) {
//     $errorMessage[] = 'Last name cannot contain numbers!';
// }

// // Phone number validation
// if (preg_match('/^[0-9]+$/', $_POST['phoneno']) == 0) {
//     $errorMessage[] = 'Phone number cannot contain alphabets!';
// }

// if (strlen($_POST['phoneno']) > 10) {
//     $errorMessage[] = 'Phone number cannot exceed 10 digits!';
// }

// if (strlen($_POST['phoneno']) < 10) {
//     $errorMessage[] = 'The phone number must be at least 10 digits!';
// }


    // Proceed if no errors
    if (empty($errorMessage)) {
        // // Check if the username already exists
        // if ($stmt = $con->prepare('SELECT id FROM users WHERE username = ?')) {
        //     $stmt->bind_param('s', $_POST['username']);
        //     $stmt->execute();
        //     $stmt->store_result();
        //     if ($stmt->num_rows > 0) {
        //         $errorMessage[] = 'Username exists, please choose another!';

         // Check if the username or email already exists
         if ($stmt = $con->prepare('SELECT id FROM users WHERE username = ? OR email = ?')) {
            $stmt->bind_param('ss', $_POST['username'], $_POST['email']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                // Check which field exists
                $stmt->bind_result($existingId);
                $stmt->fetch();

                // Prepare a separate query to distinguish between username and email
                if ($checkUsername = $con->prepare('SELECT id FROM users WHERE username = ?')) {
                    $checkUsername->bind_param('s', $_POST['username']);
                    $checkUsername->execute();
                    $checkUsername->store_result();
                    if ($checkUsername->num_rows > 0) {
                        $errorMessage[] = 'Username exists, please choose another!';
                    }
                    $checkUsername->close();
                }

                if ($checkEmail = $con->prepare('SELECT id FROM users WHERE email = ?')) {
                    $checkEmail->bind_param('s', $_POST['email']);
                    $checkEmail->execute();
                    $checkEmail->store_result();
                    if ($checkEmail->num_rows > 0) {
                        $errorMessage[] = 'Email exists, please choose another!';
                    }
                    $checkEmail->close();
                }
            } else {
                // Insert new account
                if ($stmt = $con->prepare('INSERT INTO users (username, password, firstname, lastname, phoneno, email, user_role, user_image, status, activation_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
                    // $password = $_POST["password"];
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $firstname = $_POST["firstname"];
                    $lastname = $_POST["lastname"];
                    $phoneno = $_POST["phoneno"];
                    $user_role = "customer";
                    $status="0";

                    // Image upload
                    $targetDir = "../images/users/";
                    $fileName = basename($_FILES['file']['name']);
                    $targetFilePath = $targetDir . $fileName;

                    if (!empty($fileName) && move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
                        // Use uploaded image
                    } else {
                        $fileName = "user_default.jpg"; // Default image if upload fails
                    }
                    $uniqid = uniqid();
                    $stmt->bind_param('ssssssssis', $_POST['username'], $password, $firstname, $lastname, $phoneno, $_POST['email'], $user_role, $fileName, $status, $uniqid);
                    $stmt->execute();
                    // $successMessage = 'You have successfully registered! You will be redirected shortly.';
                    try {
                        // Set up the SMTP server
                        $mail->isSMTP();  // Use SMTP
                        $mail->Host = 'smtp.gmail.com';  // Use your SMTP server (e.g., Gmail, Mailtrap, etc.)
                        $mail->SMTPAuth = true;
                        $mail->Username = 'info.rnbus@gmail.com';  // Your SMTP username (e.g., Mailtrap or Gmail username)
                        // $mail->Password = 'Aishwarya@1';  // Your SMTP password (e.g., Mailtrap or Gmail password)
                        $mail->Password = 'uuzn fxwa aejr gbhr';  // Your SMTP password (e.g., Mailtrap or Gmail password)
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
                        $mail->Port = 587;  // Port for sending the email (587 for TLS, 465 for SSL)
                    
                        // Set the sender's information
                        $mail->setFrom('info.rnbus@gmail.com', 'info');  // Your email address
                        $mail->addAddress($email, $firstname.''.$lastname);  // Recipient's email address
                    
                        // Set the email format to HTML
                        $mail->isHTML(true);
                        $activate_link = 'http://localhost/RNBus/signup/activate.php?email=' . $_POST['email'] . '&code=' . $uniqid;
                        $mail->Subject = 'Account Activation Required';
                       
                       
                        $mail->Body    = '<p>Dear sir/madam,<br><br>

                        Please click the following link to activate your account: <a href="' . $activate_link . '">' . "Click Here" . '</a>
                        <br><br>
                        Regards,<br>
                        Customer Service Department<br>
                        RN Bus Pvt. Ltd.<br>
                        +977-9840594031<br>
                        Chhetrapati, Kathmandu, Nepal<br>
                        </p>';
                        
                        
                        $mail->AltBody = '<p>Please click the following link to activate your account: <a href="' . $activate_link . '">' . "Click Here"  . '</a></p>';
                    
                        // Send the email
                        $mail->send();
                        // echo 'Message has been sent successfully!';
                        $successMessage = 'Please check your email to activate your account!';
                        echo "<script>document.addEventListener('DOMContentLoaded', function() { 
                            document.getElementById('loader').style.display = 'none'; 
                            document.getElementById('successModal').style.display = 'flex'; 
                        });</script>";
                    } catch (Exception $e) {
                        // Handle errors if any
                        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        $successMessage = 'Something is wrong!';
                    }
                    // $successMessage = 'Please check your email to activate your account!';
                    // echo "<script>setTimeout(function(){ window.location.href = '../login'; }, 1000);</script>";
                } else {
                    $errorMessage[] = 'Could not prepare statement!';
                }
            }
            $stmt->close();
        } else {
            $errorMessage[] = 'Could not prepare statement!';
        }
    }
    $con->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <!-- <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="util.css"> -->
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <style>
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
                <li><a href="policies/">Policies</a></li>
                <!-- <li><a href="../login/" class="login-btn">Login</a></li> -->
            </ul>
        </nav>
    </header>


<div class="background">
    <div class="login">
    
    <div class="loader" id="loader"></div>
    <div id="loadingOverlay">
    <div class="spinner"></div>
    </div>

<div class="modal" id="successModal">
    <div class="modal-content">
        <p>Please check your email to activate your account!</p>
        <button id="closeModal">OK</button>
    </div>
</div>

        <h1>Register</h1>
        <!-- Display success and error messages -->
        <?php if (!empty($errorMessage)): ?>
            <?php foreach ($errorMessage as $error): ?>
                <li class="message error" style="text-align: left; margin-left: 20px; margin-top: 0px;"><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" >
            <label for="username"><i class="fas fa-user"></i></label>
            <input type="text" name="username" placeholder="Username" id="username" required value="<?= htmlspecialchars($username); ?>">

            <label for="firstname"><i class="fas fa-user"></i></label>
            <input type="text" name="firstname" placeholder="First Name" id="firstname" required value="<?= htmlspecialchars($firstname); ?>">

            <label for="lastname"><i class="fas fa-user"></i></label>
            <input type="text" name="lastname" placeholder="Last Name" id="lastname" required value="<?= htmlspecialchars($lastname); ?>">

            <label for="phoneno"><i class="fas fa-phone"></i></label>
            <input type="text" name="phoneno" placeholder="Phone No" id="phoneno" required value="<?= htmlspecialchars($phoneno); ?>">

            <label for="password"><i class="fas fa-lock"></i></label>
            <input type="password" name="password" placeholder="Password" id="password" required>

            <label for="confirm_password"><i class="fas fa-lock"></i></label>
            <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirm_password" required>

            <label for="email"><i class="fas fa-envelope"></i></label>
            <input type="email" name="email" placeholder="Email" id="email" required value="<?= htmlspecialchars($email); ?>">

            <label for="file"><i class="fas fa-image"></i></label>
            <input type="file" name="file" id="fileUpload">

            <input type="submit" value="Register">
            <div class="msg">Already have an account? <a class="signup" href="../login/">Login</a>!</div>
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

document.addEventListener("DOMContentLoaded", function () {
    const loader = document.getElementById("loader");
    const successModal = document.getElementById("successModal");
    const closeModal = document.getElementById("closeModal");

    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {
        // Show the loader
        loader.style.display = "block";
    });

    // Handle showing success modal
    <?php if (!empty($successMessage)) { ?>
        loader.style.display = "none";
        successModal.style.display = "flex";
    <?php } ?>

    closeModal.addEventListener("click", function () {
        successModal.style.display = "none";
    });
});

// Show the loading spinner on form submission
document.querySelector('form').addEventListener('submit', function (e) {
    // Show the loading overlay
    document.getElementById('loadingOverlay').style.display = 'flex';
});
</script>
</body>
</html>
