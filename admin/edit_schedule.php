<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form input
    $schedule_id = $_POST['schedule_id'];
    $from_location = $_POST['from_location'];
    $to_location = $_POST['to_location'];
    $bus_id = $_POST['bus_id'];
    $departure_time = $_POST['departure_time'];
    $eta = $_POST['eta'];
    $availability = $_POST['availability'];
    $price = $_POST['price'];
    $status = $_POST['status'];

    // Validate input (you can add more validation if necessary)
    if (empty($schedule_id) || empty($from_location) || empty($to_location) || empty($bus_id) ||
        empty($departure_time) || empty($eta) || empty($availability) || empty($price) || empty($status)) {
        echo "All fields are required!";
        exit;
    }

    // Prepare the SQL query to update the schedule
    $sql = "UPDATE schedule_list SET 
                from_location = ?, 
                to_location = ?, 
                bus_id = ?, 
                departure_time = ?, 
                eta = ?, 
                availability = ?, 
                price = ?, 
                status = ? 
            WHERE id = ?";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param('iiissssii', $from_location, $to_location, $bus_id, $departure_time, $eta, $availability, $price, $status, $schedule_id);

        // Execute the query
        if ($stmt->execute()) {
            // echo "<script>alert('Schedule Updated successfully!');</script>";
        header("refresh:0.6;url=manage_schedule.php");
        } else {
            echo "Error updating schedule: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>
