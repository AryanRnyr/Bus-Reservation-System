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

// Initialize error/success messages
$successMessage = "";
$errorMessage = "";

// Check if bus ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $busId = $_GET['id'];

    // SQL query to delete the bus based on the bus ID
    $sql = "DELETE FROM bus WHERE id = ?";

    // Prepare and execute the delete query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $busId); // "i" means integer for bus id

        if ($stmt->execute()) {
            $successMessage = "Bus deleted successfully!";
        } else {
            $errorMessage = "Error: Could not execute delete query. " . $conn->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        $errorMessage = "Error: Could not prepare the delete query. " . $conn->error;
    }
} else {
    $errorMessage = "Invalid bus ID.";
}

// Redirect back to the list of buses page after 2 seconds or show a message
if (!empty($successMessage)) {
    header("refresh:0.6;url=list_bus.php"); // Redirect to list of buses
} elseif (!empty($errorMessage)) {
    echo "<p class='message error'>$errorMessage</p>";
    header("refresh:0.6;url=list_bus.php"); // Redirect after showing error
}

?>