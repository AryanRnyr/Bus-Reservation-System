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
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'CANCELED')"; // Default status to CANCELED
                
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiiissssssssss", $ticket_number, $schedule_id, $user_id, $order_id, $full_name, $phone, $fare, $seats, $bus_name, $departure_city, $departure_time, $arrival_city, $arrival_time, $requested_time_str);

        if ($stmt->execute()) {
            // Update the status in orders table
            $updateOrderSql = "UPDATE orders SET status = 'CANCELED' WHERE id = ?";
            if ($updateStmt = $conn->prepare($updateOrderSql)) {
                $updateStmt->bind_param("i", $order_id);

                if ($updateStmt->execute()) {
                    $successMessage = "You cancelled the bus ticket!";
                    header("refresh:2;url=ticketsbooked.php");
                } else {
                    $errorMessage = "Error updating order status: " . $conn->error;
                }
            } else {
                $errorMessage = "Error preparing order update query: " . $conn->error;
            }
        } else {
            $errorMessage = "Error executing cancellation query: " . $conn->error;
        }
    } else {
        $errorMessage = "Error preparing cancellation query: " . $conn->error;
    }
}

// Check for missing GET parameters
// if (!isset($_GET['ticket_number'], $_GET['seats'], $_GET['schedule_id'], $_GET['user_id'])) {
//     die("Missing required parameters.");
// }

// Get parameters from the URL and validate them
$ticket_number = $_POST["ticket_number"];
$seats = intval($_POST['seats']);
$schedule_id = intval($_POST['schedule_id']);
$user_id = intval($_POST['user_id']);

if (empty($ticket_number) || $seats <= 0 || $schedule_id <= 0 || $user_id <= 0) {
    die("Invalid parameters provided.");
}

// Begin transaction
$conn->begin_transaction();

try {
    // Step 1: Delete rows from seat_reservation
    $deleteQuery = "DELETE FROM seat_reservation WHERE user_id = ? AND schedule_id = ? AND ticket_no = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("iis", $user_id, $schedule_id, $ticket_number);
    $deleteStmt->execute();
    $deletedRows = $deleteStmt->affected_rows; // Number of rows deleted
    $deleteStmt->close();

    // Log deleted rows for debugging
    file_put_contents('debug.log', "Step 1: Deleted rows: $deletedRows\n", FILE_APPEND);

    if ($deletedRows > 0) {
        // Step 2: Update availability in schedule_list
        $updateAvailabilityQuery = "UPDATE schedule_list SET availability = availability + ? WHERE id = ?";
        $updateAvailabilityStmt = $conn->prepare($updateAvailabilityQuery);
        $updateAvailabilityStmt->bind_param("ii", $deletedRows, $schedule_id);
        $updateAvailabilityStmt->execute();
        $updateAvailabilityStmt->close();

        // Step 3: Update status in cancel_request
        $updateCancelRequestQuery = "UPDATE cancel_request SET status = 'CANCELED' WHERE ticket_no = ?";
        $updateCancelRequestStmt = $conn->prepare($updateCancelRequestQuery);
        $updateCancelRequestStmt->bind_param("s", $ticket_number);
        $updateCancelRequestStmt->execute();
        $updateCancelRequestStmt->close();

        // Step 4: Update status in orders
        $updateOrdersQuery = "UPDATE orders SET status = 'CANCELED' WHERE ticket_no = ?";
        $updateOrdersStmt = $conn->prepare($updateOrdersQuery);
        $updateOrdersStmt->bind_param("s", $ticket_number);
        $updateOrdersStmt->execute();
        $updateOrdersStmt->close();

        // Commit transaction
        $conn->commit();
        $successMessage = "Bus Ticket Cancelled successfully.";
        file_put_contents('debug.log', "Step 5: Transaction committed successfully.\n", FILE_APPEND);
        header("refresh:1;url=list_cancelrequest.php");
        exit();
    } else {
        throw new Exception("No rows deleted from seat_reservation. Cancellation failed.");
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $errorMessage = "Error: " . $e->getMessage();
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo $errorMessage; // Display error for debugging
    exit(); // Stop further execution
}

// Redirect back to the list of tickets booked page after 2 seconds or show a message
if (!empty($successMessage)) {
    echo "<script>alert(You cancelled the bus ticket!)</script>";
    header("refresh:1;url=list_cancelrequest.php"); // Redirect to list of tickets booked
} elseif (!empty($errorMessage)) {
    echo "<p class='message error'>$errorMessage</p>";
    header("refresh:1;url=list_cancelrequest.php"); // Redirect after showing error
}

?>
