<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

// Check if the ID parameter is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $schedule_id = intval($_GET['id']);

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rn_bus_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // First, delete the reservation details related to the schedule_id
    $deleteReservationsSql = "DELETE FROM seat_reservation WHERE schedule_id = ?";
    $deleteReservationsStmt = $conn->prepare($deleteReservationsSql);
    $deleteReservationsStmt->bind_param("i", $schedule_id);

    if ($deleteReservationsStmt->execute()) {
        // After deleting the reservations, delete the schedule
        $deleteScheduleSql = "DELETE FROM schedule_list WHERE id = ?";
        $deleteScheduleStmt = $conn->prepare($deleteScheduleSql);
        $deleteScheduleStmt->bind_param("i", $schedule_id);

        if ($deleteScheduleStmt->execute()) {
            // Redirect to manage_schedule.php with success message
            header("Location: manage_schedule.php?msg=Schedule and reservations deleted successfully");
        } else {
            // Error deleting the schedule
            header("Location: manage_schedule.php?msg=Error deleting schedule");
        }

        $deleteScheduleStmt->close();
    } else {
        // Error deleting reservations
        header("Location: manage_schedule.php?msg=Error deleting reservations");
    }

    $deleteReservationsStmt->close();
    $conn->close();
} else {
    // Redirect if no valid ID is provided
    header("Location: manage_schedule.php?msg=Invalid schedule ID");
    exit();
}
?>
