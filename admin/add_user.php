<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for error/success messages
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from the form inputs
    $userName = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phoneNo = $_POST['phone_no'] ?? '';
    $userRole = $_POST['user_role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $status="1";

    // Validation: Check if inputs are empty
    if (empty($userName) || empty($email) || empty($firstName) || empty($lastName) || empty($phoneNo) || empty($userRole) || empty($password) || empty($confirmPassword)) {
        $errorMessage = "Please fill in all fields.";
    } elseif (strlen($userName) < 5) {
        $errorMessage = "Username must be greater than 4 characters.";
    } elseif (!preg_match("/^[a-zA-Z]*$/", $firstName)) {
        $errorMessage = "First name can only contain alphabets.";
    } elseif (!preg_match("/^[a-zA-Z]*$/", $lastName)) {
        $errorMessage = "Last name can only contain alphabets.";
    } elseif (!preg_match("/^\d{10}$/", $phoneNo)) {
        $errorMessage = "Phone number must be exactly 10 digits.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $errorMessage = "Password must be greater than 5 characters.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Password and confirm password do not match.";
    } else {
        // Check if username or email already exists
        $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt = $conn->prepare($checkQuery)) {
            $stmt->bind_param("ss", $userName, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = "Username or email already exists. Please choose a different one.";
            } else {
                // Insert query to add new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash password before storing
                $fileName = "user_default.jpg";
                $activation="activated";
                
                $sql = "INSERT INTO users (username, email, firstname, lastname, phoneno, user_role, password, user_image, status, activation_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssssssssis", $userName, $email, $firstName, $lastName, $phoneNo, $userRole, $hashedPassword, $fileName, $status, $activation);

                    if ($stmt->execute()) {
                        $successMessage = "New user added successfully!";
                        header("refresh:0.7;url=list_users.php");
                    } else {
                        $errorMessage = "Error: Could not execute query. " . $conn->error;
                    }
                } else {
                    $errorMessage = "Error: Could not prepare query. " . $conn->error;
                }
            }

            // Close the statement after all queries
            $stmt->close();
        } else {
            $errorMessage = "Error: Could not prepare check query. " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="list_users.css">
    <link rel="stylesheet" href="add_bus.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>
    <header>
        <nav class="navbar">
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
                <li><a href="list_reservations.php">Reservations</a></li>
                <li class="dropdown">
                    <a href="#">Services <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                    <ul class="dropdown-content">
                        <li><a href="list_bus.php">List Buses</a></li>
                        <li><a href="list_location.php">List Location</a></li>
                        <li><a href="list_users.php">List Users</a></li>
                    </ul>
                </li>
                <li><a href="manage_schedule.php">Manage Schedule</a></li>
                <li><a href="list_cancelrequest.php">Cancellation Requests</a></li>
                <li><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h2>Add New User</h2>

            <!-- Success and error messages -->
            <?php if (!empty($errorMessage)): ?>
                <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username"  required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>
                <div class="form-group">
                    <label for="phone_no">Phone Number</label>
                    <input type="text" name="phone_no" id="phone_no" required>
                </div>
                <div class="form-group">
                    <label for="user_role">Role</label>
                    <select class="form-control" id="user_role" name="user_role" required>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Add User</button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <h3 class="dev">Developed By</h3>
        <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
    </footer>

</body>
</html>
