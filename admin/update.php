<?php
session_start();
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';
$conn= mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $phoneno = $_POST['phoneno'];
    $user_image = $_POST['filename'];
    $id = $_SESSION["id"];
    $error = [];

    if (empty($firstname)) {
        $error[] = "First name cannot be empty.";
    }
    

    if (ctype_space($firstname)) {
        $error[] = "First name cannot contain only spaces.";
    }

    if (!preg_match("/^[a-zA-Z ]+$/", $firstname)) {
        $error[] = "First name must contain only letters and spaces.";
    }

    if (strlen($firstname) < 3) {
        $error[] = "First name must be at least 3 characters long.";
    }
    
    
    if (empty($lastname)) {
        $error[] = "Last name cannot be empty.";
    }

    if (ctype_space($lastname)) {
        $error[] = "Last name cannot contain only spaces.";
    }

    if (!preg_match("/^[a-zA-Z ]+$/", $lastname)) {
        $error[] = "Last name must contain only letters and spaces.";
    }

    if (strlen($lastname) < 3) {
        $error[] = "Last name must be at least 3 characters long.";
    }
    
     // Phone number validation
     if (empty($phoneno) || !ctype_digit($phoneno) || strlen($phoneno) != 10 || $phoneno < 0) {
        $error[] = "Phone number must be exactly 10 digits, numeric, and cannot be negative.";
    }

   // Password Validation (Only if user provides a new password)
if (!empty($_POST['password'])) {
    $password = $_POST['password'];

    // Check if the password is less than 8 characters
    if (strlen($password) < 8) {
        $error[] = "Password must be at least 8 characters long.";
    } 
    // If length is valid, check other criteria
    else {
        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $error[] = "Password must contain at least one uppercase letter.";
        }

        // Check for a number
        if (!preg_match('/\d/', $password)) {
            $error[] = "Password must contain at least one number.";
        }

        // Check for a special character
        if (!preg_match('/[\W]/', $password)) {
            $error[] = "Password must contain at least one special character (e.g., !@#$%^&*).";
        }
    }
}


    
    
    // File upload validation
    $targetDir = "../images/users/";
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    if (!empty($fileName)) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            $error[] = "Only JPG, JPEG, PNG, and GIF image files are allowed.";
        } else {
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
                $error[] = "Failed to upload the image.";
                $fileName = $user_image; // Keep old image if upload fails
            }
        }
    } else {
        $fileName = $user_image; // Keep old image if no new file is uploaded
    }

    // If there are errors, store them in session and redirect back to the form
    if (!empty($error)) {
        $_SESSION["validate_msg"] = $error;
        echo '<script>window.history.back();</script>';
        exit();
    }

    // Update logic
    if (empty($_POST['password'])) {
        $sql = "UPDATE users SET firstname=?, lastname=?, phoneno=?, user_image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            echo mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "ssssi", $firstname, $lastname, $phoneno, $fileName, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: profile.php");
            } else {
                echo mysqli_stmt_error($stmt);
            }
        }
    } else {
        $sql = "UPDATE users SET password=?, firstname=?, lastname=?, phoneno=?, user_image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            echo mysqli_error($conn);
        } else {
            $hashpassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sssssi", $hashpassword, $firstname, $lastname, $phoneno, $fileName, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: profile.php");
            } else {
                echo mysqli_stmt_error($stmt);
            }
        }
    }
}
?>
