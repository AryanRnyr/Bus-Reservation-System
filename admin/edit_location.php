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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busId = $_POST['bus_id'];
    $busName = $_POST['city'];
    $busNumber = $_POST['state'];
    $status = $_POST['status'];

    // Check if the city already exists (except for the current entry)
    $sql = "SELECT * FROM location WHERE city = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $busName, $busId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // City already exists, show an error message
        echo "Location already exists!";
    } else {
        // Update the location details
        $updateSql = "UPDATE location SET city = ?, state = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssii", $busName, $busNumber, $status, $busId);
        $stmt->execute();

        // Redirect to the list of locations page or show a success message
        // echo "<script>alert('Location Updated successfully!');</script>";
        header("refresh:0.6;url=list_location.php");
        exit();
    }
}

?>