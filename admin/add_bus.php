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
    $busName = trim($_POST['bus_name'] ?? '');
    $busNumber = trim($_POST['bus_number'] ?? '');

    // Validation: Check if inputs are empty
    if (empty($busName) || empty($busNumber)) {
        $errorMessage = "Please enter both bus name and bus number.";
    } 
    // Bus Name Validation
    elseif (!preg_match('/^[A-Za-z ]+$/', $busName)) {
        // $errorMessage = "Bus name should contain only letters and spaces, without numbers or special characters.";
        $errorMessage = "Bus name should contain only letters.";
    }
    elseif (ctype_space($busName)) {
        $errorMessage = "Bus name cannot be only spaces.";
    }
    // Bus Number Validation
    elseif (!preg_match('/^[A-Za-z0-9 ]+$/', $busNumber)) {
        $errorMessage = "Bus number should not contain special characters except letters and numbers.";
    }
    elseif (strpos($busNumber, '-') !== false) {
        $errorMessage = "Bus number cannot contain a hyphen (-).";
    }
    elseif (ctype_space($busNumber)) {
        $errorMessage = "Bus number cannot be only spaces.";
    }
    else {
        // Check if the bus number already exists
        $checkQuery = "SELECT id FROM bus WHERE bus_number = ?";
        if ($stmt = $conn->prepare($checkQuery)) {
            $stmt->bind_param("s", $busNumber);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = "A bus with this number already exists. Please enter a different bus number.";
            } else {
                // Insert query to add new bus
                $sql = "INSERT INTO bus (name, bus_number, status) VALUES (?, ?, 1)"; // Default status to active (1)
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $busName, $busNumber);

                    if ($stmt->execute()) {
                        $successMessage = "New bus added successfully!";
                        header("refresh:2;url=list_bus.php");
                    } else {
                        $errorMessage = "Error: Could not execute query. " . $conn->error;
                    }
                } else {
                    $errorMessage = "Error: Could not prepare query. " . $conn->error;
                }
            }

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


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="list_bus.css">
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
    <h2>Add New Bus</h2>

    <!-- Success and error messages -->
    <?php if (!empty($errorMessage)): ?>
        <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($successMessage)): ?>
        <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <form action="" method="post">
        <div class="form-group">
            <label for="bus_name">Bus Name</label>
            <input type="text" name="bus_name" id="bus_name" >
        </div>
        <div class="form-group">
            <label for="bus_number">Bus Number</label>
            <input type="text" name="bus_number" id="bus_number" >
        </div>
        <button type="submit">Add Bus</button>
    </form>
</div>
    </main>

      
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

</body>
</html>
