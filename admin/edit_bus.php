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


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $busId = $_POST['bus_id'];
//     $busName = $_POST['name'];
//     $busNumber = $_POST['bus_number'];
//     $status = $_POST['status'];

//     // Check if the bus number already exists (except for the current bus)
//     $sql = "SELECT * FROM bus WHERE bus_number = ? AND id != ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("si", $busNumber, $busId);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows > 0) {
//         // Bus number already exists, show an error message
//         echo "Bus number already exists!";
//     } else {
//         // Update the bus details
//         $updateSql = "UPDATE bus SET name = ?, bus_number = ?, status = ? WHERE id = ?";
//         $stmt = $conn->prepare($updateSql);
//         $stmt->bind_param("ssii", $busName, $busNumber, $status, $busId);
//         $stmt->execute();
//         // Redirect to the list of buses page or show a success message
//         echo "<script>alert('Bus Updated successfully!');</script>";
//         header("refresh:1;url=list_bus.php");
//         exit();
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busId = $_POST['bus_id'];
    $busName = $_POST['name'];
    $busNumber = $_POST['bus_number'];
    $status = $_POST['status'];

    // Validation: Check if bus name contains numbers
    if (preg_match('/\d/', $busName)) {
        echo "Bus name should not contain numbers!";
    } else {
        // Check if the bus number already exists (except for the current bus)
        $sql = "SELECT * FROM bus WHERE bus_number = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $busNumber, $busId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Bus number already exists, show an error message
            echo "Bus number already exists!";
        } else {
            // Update the bus details
            $updateSql = "UPDATE bus SET name = ?, bus_number = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ssii", $busName, $busNumber, $status, $busId);
            $stmt->execute();
            // Redirect to the list of buses page or show a success message
            echo "<script>alert('Bus Updated successfully!');</script>";
            header("refresh:1;url=list_bus.php");
            exit();
        }
    }
}
?>