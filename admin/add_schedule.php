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
    $busId = $_POST['bus_id'] ?? '';
    $fromLocation = $_POST['from_location'] ?? '';
    $toLocation = $_POST['to_location'] ?? '';
    $departureTime = $_POST['departure_time'] ?? '';
    $eta = $_POST['eta'] ?? '';
    // $availability = $_POST['availability'] ?? '';
    $availability="45";
    $price = $_POST['price'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validation: Check if required inputs are empty
    if (empty($busId) || empty($fromLocation) || empty($toLocation) || empty($departureTime) || empty($eta) || empty($availability) || empty($price) || empty($status)) {
        $errorMessage = "Please fill all fields.";
    } else {
        // Check if departure time is in the past
        $currentTime = date("Y-m-d\TH:i"); // Current time in "YYYY-MM-DDTHH:MM" format (matching the input format)
        if ($departureTime < $currentTime) {
            $errorMessage = "Departure time cannot be set in the past.";
        }

        // Check if ETA is before departure time
        if ($eta < $departureTime) {
            $errorMessage = "Arrival time cannot be set before the departure time.";
        }

        if (empty($errorMessage)) {
            // Check if the bus number already exists in the schedule
            $checkQuery = "SELECT id FROM schedule_list WHERE bus_id = ? AND from_location = ? AND to_location = ? AND departure_time = ? AND eta = ?";
            if ($stmt = $conn->prepare($checkQuery)) {
                $stmt->bind_param("sssss", $busId, $fromLocation, $toLocation, $departureTime, $eta);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    // Schedule with the same bus number already exists for this route and time
                    $errorMessage = "This bus is already scheduled for the selected route and time.";
                } else {
                    // Insert query to add new schedule
                    $sql = "INSERT INTO schedule_list (bus_id, from_location, to_location, departure_time, eta, availability, price, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("sssssiis", $busId, $fromLocation, $toLocation, $departureTime, $eta, $availability, $price, $status);

                        if ($stmt->execute()) {
                            $successMessage = "New schedule added successfully!";
                            // echo "<script>alert('New schedule added successfully!');</script>";
                            header("refresh:2;url=manage_schedule.php");
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
    <link rel="stylesheet" href="add_schedule.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script>
        // Disable 'To Location' option if it matches 'From Location'
        function disableToLocation() {
            var fromLocation = document.getElementById("from_location").value;
            var toLocation = document.getElementById("to_location");
            for (var i = 0; i < toLocation.options.length; i++) {
                if (toLocation.options[i].value === fromLocation) {
                    toLocation.options[i].disabled = true;  // Disable the matching "To Location"
                } else {
                    toLocation.options[i].disabled = false; // Enable all other options
                }
            }
        }

        // Call this function when the page loads to disable matching options
        window.onload = function() {
            disableToLocation();
        }
    </script>
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
            <h2>Add New Schedule</h2>

            <!-- Success and error messages -->
            <?php if (!empty($errorMessage)): ?>
                <p class="message error"><?= htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <p class="message success"><?= htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>

            <!-- Schedule Form -->
            <!-- <form action="" method="post">
                <div class="form-group">
                    <label for="bus_id">Bus</label>
                    <select name="bus_id" id="bus_id" required>
                        <option value="">Select Bus</option>
                        <?php
                        // Fetch available buses from the database
                        $busQuery = "SELECT id, name, bus_number FROM bus WHERE status = 1";
                        $result = $conn->query($busQuery);
                        while ($bus = $result->fetch_assoc()) {
                            echo "<option value=\"{$bus['id']}\">{$bus['name']} ({$bus['bus_number']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="from_location">From Location</label>
                    <select name="from_location" id="from_location" onchange="disableToLocation()" required>
                        <option value="">Select a Location</option>
                        <?php
                        $locationResult = $conn->query("SELECT id, city FROM location WHERE status = 1");
                        while ($location = $locationResult->fetch_assoc()) {
                            echo "<option value=\"{$location['id']}\">{$location['city']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="to_location">To Location</label>
                    <select name="to_location" id="to_location" required>
                        <option value="">Select a Location</option>
                        <?php
                        // Reload the locations to ensure the "To Location" list is complete
                        $locationResult->data_seek(0); // Reset the result pointer
                        while ($location = $locationResult->fetch_assoc()) {
                            echo "<option value=\"{$location['id']}\">{$location['city']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="departure_time">Departure Time</label>
                    <input type="datetime-local" name="departure_time" id="departure_time" required>
                </div>

                <div class="form-group">
                    <label for="eta">ETA (Arrival Time)</label>
                    <input type="datetime-local" name="eta" id="eta" required>
                </div>

                <div class="form-group">
                    <label for="availability">Availability</label>
                    <input type="number" name="availability" id="availability" required min="1">
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" required min="0">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <button type="submit">Add Schedule</button>
            </form> -->
            <form action="" method="post">
    <div class="form-group">
        <label for="bus_id">Bus</label>
        <select name="bus_id" id="bus_id" onchange="populateBusInfo()" required>
            <option value="">Select Bus</option>
            <?php
            // Fetch available buses from the database
            $busQuery = "SELECT id, name, bus_number FROM bus WHERE status = 1";
            $result = $conn->query($busQuery);
            while ($bus = $result->fetch_assoc()) {
                echo "<option value=\"{$bus['id']}\" data-name=\"{$bus['name']}\" data-number=\"{$bus['bus_number']}\">{$bus['name']} ({$bus['bus_number']})</option>";
            }
            ?>
        </select>
    </div>

    <!-- Hidden fields to store bus name and bus number -->
    <input type="hidden" name="bus_name" id="bus_name">
    <input type="hidden" name="bus_number" id="bus_number">

    <!-- Other form fields -->
    <div class="form-group">
        <label for="from_location">From Location</label>
        <select name="from_location" id="from_location" onchange="disableToLocation()" required>
            <option value="">Select a Location</option>
            <?php
            $locationResult = $conn->query("SELECT id, city FROM location WHERE status = 1");
            while ($location = $locationResult->fetch_assoc()) {
                echo "<option value=\"{$location['id']}\">{$location['city']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="to_location">To Location</label>
        <select name="to_location" id="to_location" required>
            <option value="">Select a Location</option>
            <?php
            // Reload the locations to ensure the "To Location" list is complete
            $locationResult->data_seek(0); // Reset the result pointer
            while ($location = $locationResult->fetch_assoc()) {
                echo "<option value=\"{$location['id']}\">{$location['city']}</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label for="departure_time">Departure Time</label>
        <input type="datetime-local" name="departure_time" id="departure_time" required>
    </div>

    <div class="form-group">
        <label for="eta">ETA (Arrival Time)</label>
        <input type="datetime-local" name="eta" id="eta" required>
    </div>

    <!-- <div class="form-group">
        <label for="availability">Availability</label>
        <input type="number" name="availability" id="availability" required min="1">
    </div> -->

    <div class="form-group">
        <label for="price">Price</label>
        <input type="number" name="price" id="price" required min="0">
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <button type="submit">Add Schedule</button>
</form>
        </div>
    </main>
    <script>
// Automatically fill in bus name and bus number when a bus is selected
function populateBusInfo() {
    var busSelect = document.getElementById("bus_id");
    var selectedOption = busSelect.options[busSelect.selectedIndex];

    var busName = selectedOption.getAttribute("data-name");
    var busNumber = selectedOption.getAttribute("data-number");

    document.getElementById("bus_name").value = busName;
    document.getElementById("bus_number").value = busNumber;
}
    // Disable past times for departure
    // window.onload = function() {
    //     // Get the current date and time in the correct format for datetime-local
    //     var now = new Date();
    //     var currentTime = now.toISOString().slice(0, 16);  // Format it as "yyyy-MM-ddTHH:mm"
    //     document.getElementById("departure_time").setAttribute("min", currentTime); // Set min for departure time

    //     // Handle ETA (Arrival Time) validation
    //     document.getElementById("departure_time").addEventListener("change", function() {
    //         var departureTime = document.getElementById("departure_time").value;
    //         if (departureTime) {
    //             document.getElementById("eta").setAttribute("min", departureTime); // Set min for ETA to be same as departure
    //         }
    //     });
    // };
    window.onload = function() {
    // Get the current date and time
    var now = new Date();

    // Add 12 hours to the current time
    now.setHours(now.getHours() + 12);

    // Format the new time as "yyyy-MM-ddTHH:mm" (datetime-local format)
    var currentTimePlus12Hours = now.toISOString().slice(0, 16);  // Format it as "yyyy-MM-ddTHH:mm"

    // Set min for departure time to 12 hours later
    document.getElementById("departure_time").setAttribute("min", currentTimePlus12Hours); 

    // Handle ETA (Arrival Time) validation
    document.getElementById("departure_time").addEventListener("change", function() {
        var departureTime = document.getElementById("departure_time").value;
        if (departureTime) {
            // Set min for ETA to be same as departure time
            document.getElementById("eta").setAttribute("min", departureTime); 
        }
    });
};
</script>
      
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

</body>
</html>
