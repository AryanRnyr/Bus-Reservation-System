<?php
session_start();

// Check if the user is logged in and not an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION["s_role"] == "admin") {
    header("Location: ../admin/");
    exit();
}

// Database connection
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['schedule_id']) && isset($_GET['user_id'])) {
    // Get the user ID and schedule ID from the form
    $user_id = $_GET['user_id'];
    $schedule_id = $_GET['schedule_id'];

    // Find the count of seats being canceled
    $count_query = "SELECT COUNT(*) AS seat_count FROM seat_reservation 
                    WHERE user_id = '$user_id' AND schedule_id = '$schedule_id' AND status = 'temporary'";
    $count_result = mysqli_query($conn, $count_query);
    $row = mysqli_fetch_assoc($count_result);
    $seat_count = $row['seat_count'];

    // Delete all temporary reservations for this user and schedule
    $delete_query = "DELETE FROM seat_reservation 
                     WHERE user_id = '$user_id' AND schedule_id = '$schedule_id' AND status = 'temporary'";
    
    if (mysqli_query($conn, $delete_query)) {
        // Update the availability in the schedule_list table
        $update_availability_query = "UPDATE schedule_list SET availability = availability + $seat_count WHERE id = '$schedule_id'";
        if (mysqli_query($conn, $update_availability_query)) {
            // echo "All selected seats have been canceled, and availability has been updated.";
            // echo "<script>alert(All selected seats have been canceled, and availability has been updated.)</script>";
            header("refresh:2;url=bus_details.php?schedule_id=$schedule_id");
        } else {
            echo "Error updating availability: " . mysqli_error($conn);
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
