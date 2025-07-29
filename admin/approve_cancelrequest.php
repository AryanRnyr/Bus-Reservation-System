<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"] == "customer") {
        header('Location: home/');
        exit();
    }
} else {
    header("Location: ../login");
    exit();
}

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Check for missing GET parameters
if (!isset($_GET['ticket_number'], $_GET['seats'], $_GET['schedule_id'], $_GET['user_id'])) {
    die("Missing required parameters.");
}

// Get parameters from the URL and validate them
$ticket_number = $_GET['ticket_number'];
$seats = intval($_GET['seats']);
$schedule_id = intval($_GET['schedule_id']);
$user_id = intval($_GET['user_id']);

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
        $successMessage = "Cancellation request approved successfully.";
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

// Close the database connection
$conn->close();
?>
