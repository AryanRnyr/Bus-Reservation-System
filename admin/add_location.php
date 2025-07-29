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

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // Get values from the form inputs
//     $cityName = $_POST['city_name'] ?? '';
//     $stateName = $_POST['state_name'] ?? '';

//     // Validation: Check if inputs are empty
//     if (empty($cityName) || empty($stateName)) {
//         $errorMessage = "Please enter both city name and state.";
//     } else {
//         // Check if the city already exists
//         $checkQuery = "SELECT id FROM location WHERE city = ?";
//         if ($stmt = $conn->prepare($checkQuery)) {
//             $stmt->bind_param("s", $cityName);
//             $stmt->execute();
//             $stmt->store_result();

//             if ($stmt->num_rows > 0) {
//                 // City already exists
//                 $errorMessage = "A location with this city already exists. Please enter a different city.";
//             } else {
//                 // Insert query to add new location
//                 $sql = "INSERT INTO location (city, state, status) VALUES (?, ?, 1)"; // Default status to active (1)
                
//                 if ($stmt = $conn->prepare($sql)) {
//                     $stmt->bind_param("ss", $cityName, $stateName);

//                     if ($stmt->execute()) {
//                         $successMessage = "New location added successfully!";
//                         echo "<script>alert('New location added successfully!');</script>";
//                         header("refresh:0.6;url=list_location.php");
//                         exit();
//                     } else {
//                         $errorMessage = "Error: Could not execute query. " . $conn->error;
//                     }
//                 } else {
//                     $errorMessage = "Error: Could not prepare query. " . $conn->error;
//                 }
//             }

//             // Close the statement after all queries
//             $stmt->close();
//         } else {
//             $errorMessage = "Error: Could not prepare check query. " . $conn->error;
//         }
//     }
// }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from the form inputs
    $cityName = $_POST['city_name'] ?? '';
    $stateName = $_POST['state_name'] ?? '';

    // Validation: Check if inputs are empty
    if (empty($cityName) || empty($stateName)) {
        $errorMessage = "Please enter both city name and state.";
    } elseif (preg_match('/\d/', $cityName)) {
        $errorMessage = "City name cannot contain numeric characters.";
    } elseif (preg_match('/\d/', $stateName)) {
        $errorMessage = "State name cannot contain numeric characters.";
    } else {
        // Check if the city already exists
        $checkQuery = "SELECT id FROM location WHERE city = ?";
        if ($stmt = $conn->prepare($checkQuery)) {
            $stmt->bind_param("s", $cityName);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // City already exists
                $errorMessage = "A location with this city already exists. Please enter a different city.";
            } else {
                // Insert query to add new location
                $sql = "INSERT INTO location (city, state, status) VALUES (?, ?, 1)"; // Default status to active (1)
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $cityName, $stateName);

                    if ($stmt->execute()) {
                        $successMessage = "New location added successfully!";
                        echo "<script>alert('New location added successfully!');</script>";
                        header("refresh:0.6;url=list_location.php");
                        exit();
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
                <li><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
    <div class="container">
    <h2>Add New Location</h2>

    <!-- Success and error messages -->
    <?php if (!empty($errorMessage)): ?>
        <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($successMessage)): ?>
        <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <form action="" method="post" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="city_name">City Name</label>
            <input type="text" name="city_name" id="bus_name" >
        </div>
        <div class="form-group">
            <label for="state_name">State</label>
            <input type="text" name="state_name" id="bus_number" >
        </div>
        <button type="submit">Add Location</button>
    </form>
</div>
    </main>

    <script>
    function validateForm() {
        var cityName = document.getElementById("bus_name").value;
        var stateName = document.getElementById("bus_number").value;

        // Check if city or state contains numbers
        if (/\d/.test(cityName)) {
            // alert("City name cannot contain numeric characters.");
            return false; // Prevent form submission
        }

        if (/\d/.test(stateName)) {
            // alert("State name cannot contain numeric characters.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission if validation passes
    }

    // Add this to the form tag
    document.querySelector("form").onsubmit = validateForm;
</script>
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

</body>
</html>
