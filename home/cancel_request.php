<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION["s_role"] == "admin") {
        header('Location: ../admin');
    }
} else {
    header("Location: ../");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_number = $_POST["ticket_number"];
    $full_name = $_POST["full_name"];
    $schedule_id = $_POST["schedule_id"];
    $user_id = $_POST["user_id"];
    $order_id = $_POST["order_id"];
    $seats = $_POST["seats"];
    $fare = $_POST["fare"] / 100;
    $departure_city = $_POST["departure_city"];
    $departure_time = $_POST["departure_time"];
    $arrival_city = $_POST["arrival_city"];
    $arrival_time = $_POST["arrival_time"];
    $phone=$_POST["phone"];
    $bus_name=$_POST["bus_name"];

    $timezone = new DateTimeZone('Asia/Kathmandu');  // Specify your desired timezone
$requested_time = new DateTime('now', $timezone);  // Create DateTime with the specified timezone
    $requested_time_str = $requested_time->format('Y-m-d H:i:s');

    // Insert cancellation request into cancel_request table
    $sql = "INSERT INTO cancel_request (ticket_no, schedule_id, user_id, order_id, name, phone, fare, seats, bus_name, departure_city, departure_time, arrival_city, arrival_time, requested_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')"; // Default status to PENDING
                
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiiissssssssss", $ticket_number, $schedule_id, $user_id, $order_id, $full_name, $phone, $fare, $seats, $bus_name, $departure_city, $departure_time, $arrival_city, $arrival_time, $requested_time_str);

        if ($stmt->execute()) {
            // Update the status in orders table
            $updateOrderSql = "UPDATE orders SET status = 'Cancellation Requested' WHERE id = ?";
            if ($updateStmt = $conn->prepare($updateOrderSql)) {
                $updateStmt->bind_param("i", $order_id);

                if ($updateStmt->execute()) {
                    $successMessage = "You requested for cancellation of the bus ticket!";
                    header("refresh:2;url=ticketsbooked.php");
                } else {
                    $errorMessage = "Error updating order status: " . $conn->error;
                }
            } else {
                $errorMessage = "Error preparing order update query: " . $conn->error;
            }
        } else {
            $errorMessage = "Error executing cancellation request query: " . $conn->error;
        }
    } else {
        $errorMessage = "Error preparing cancellation request query: " . $conn->error;
    }
}

// Redirect back to the list of tickets booked page after 2 seconds or show a message
if (!empty($successMessage)) {
    echo "<script>alert(You requested for cancellation of the bus ticket!)</script>";
    header("refresh:1;url=ticketsbooked.php"); // Redirect to list of tickets booked
} elseif (!empty($errorMessage)) {
    echo "<p class='message error'>$errorMessage</p>";
    header("refresh:1;url=ticketsbooked.php"); // Redirect after showing error
}

?>
