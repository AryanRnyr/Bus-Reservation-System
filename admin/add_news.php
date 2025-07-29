<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION["s_role"] == "customer") {
        header('Location: home/');
        exit();
    }
} else {
    header("Location: ../login");
    exit();
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
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';

    // Validation: Check if inputs are empty
    if (empty($title) || empty($description) || empty($date)) {
        $errorMessage = "Please enter title, description and date.";
    } elseif (preg_match('/\d/', $title)) { // Check if title contains numbers
        $errorMessage = "Title should not contain numbers.";
    } else {
        // Prepare and bind
        $sql = "INSERT INTO news (title, description, date) VALUES (?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $title, $description, $date);

            // Execute query
            if ($stmt->execute()) {
                $successMessage = "News added successfully!";
                header("refresh:2;url=list_news.php"); // Redirect after 2 seconds
                exit();
            } else {
                $errorMessage = "Error: Could not execute query. " . $stmt->error;
            }
            
            // Close the statement
            $stmt->close();
        } else {
            $errorMessage = "Error: Could not prepare query. " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
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
    <style>
        #description, #date{
            width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
        }
    </style>
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
            <h2>Add News or Updates</h2>

            <!-- Success and error messages -->
            <?php if (!empty($errorMessage)): ?>
                <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <!-- <input type="text" name="description" id="description" required> -->
                    <textarea id="description" name="description" rows="4" cols="50"></textarea>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" name="date" id="date" required>
                </div>
                <button type="submit">Add</button>
            </form>
        </div>
    </main>
      <script>
        // Get today's date
    var today = new Date().toISOString().split('T')[0];

// Set the min attribute of the date input to today's date
document.getElementById("date").setAttribute("min", today);

// Function to validate that a field is not empty or just spaces
function validateNotEmptyOrSpaces(field) {
        if (field.value.trim() === "") {
            return false;
        }
        return true;
    }

    // Attach the validation to the form submission or button click event
    document.querySelector("form").addEventListener("submit", function(event) {
        var title = document.getElementById("title");
        var description = document.getElementById("description");
        var valid = true;

        // Validate title
        if (!validateNotEmptyOrSpaces(title)) {
            alert("Title cannot be empty or just spaces.");
            valid = false;
        }

        // Validate description
        if (!validateNotEmptyOrSpaces(description)) {
            alert("Description cannot be empty or just spaces.");
            valid = false;
        }

        // Prevent form submission if validation fails
        if (!valid) {
            event.preventDefault();
        }
    });
      </script>
    <footer class="footer">
        <h3 class="dev">Developed By</h3>
        <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
    </footer>
</body>
</html>
