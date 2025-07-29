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

// Retrieve values from the URL (GET method)
$ticket_no = isset($_GET['ticket_number']) ? $_GET['ticket_number'] : '';
$schedule_id = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Check if the necessary parameters are available
if (empty($ticket_no) || empty($schedule_id) || empty($user_id)) {
    echo "Invalid parameters.";
    exit();
}

// Start the transaction
mysqli_begin_transaction($conn);

try {
    // 1. Delete the cancellation request
    $deleteQuery = "DELETE FROM cancel_request WHERE ticket_no = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, 's', $ticket_no);  // Assuming ticket_no is a string
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error deleting from cancel_request table: " . mysqli_error($conn));
    }

    // 2. Update the status to confirmed in the orders table
    $updateQuery = "UPDATE orders SET status = 'confirmed' WHERE ticket_no = ?";
    $stmt2 = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt2, 's', $ticket_no);  // Assuming ticket_no is a string
    if (!mysqli_stmt_execute($stmt2)) {
        throw new Exception("Error updating orders table: " . mysqli_error($conn));
    }

    // Commit the transaction if both queries are successful
    mysqli_commit($conn);

    // Redirect to a success page or show success message
    // echo "Cancellation request denied. The ticket status has been updated.";
    header("refresh:1;url=list_cancelrequest.php");
    // Optionally, you can redirect the user back to a page (e.g., admin panel) after success
    // header('Location: some_page.php');

} catch (Exception $e) {
    // Rollback the transaction if something goes wrong
    mysqli_rollback($conn);
    echo "Error: " . $e->getMessage();
}

// Close the database connection
mysqli_close($conn);
?>