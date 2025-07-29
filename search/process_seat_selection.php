<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION['s_role'] == "admin") {
    header("Location:../admin/");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schedule_id = $_POST['schedule_id'];
    $user_id = $_POST['user_id'];
    $selected_seats = $_POST['selected_seats'];
    $total_fare = $_POST['total_fare'];

    if (!$_POST['selected_seats']) {
        $_SESSION["validate_msg"] = 'You must select at least one seat!';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

            // If you want to set the time zone dynamically, you can retrieve it from a configuration or user input
        $timezone = 'Asia/Kathmandu'; // Replace this with a variable or dynamic value if needed

        // Set the time zone
        date_default_timezone_set($timezone);
    // Destroy the old $temporary_booking_time, if it exists
        unset($_SESSION['temporary_booking_time']);

        // Set a new $temporary_booking_time
        $temporary_booking_time = date("Y-m-d H:i:s");
        $_SESSION['temporary_booking_time'] = $temporary_booking_time;
        

    // Insert each selected seat as a temporary booking
    $seats = explode(',', $selected_seats);  // Assuming the selected seats are comma-separated
    foreach ($seats as $seat) {
        $query = "INSERT INTO seat_reservation (schedule_id, seat_number, user_id, total_fare, status, temporary_booking_time) 
                  VALUES ('$schedule_id', '$seat', '$user_id', '$total_fare', 'temporary', '$temporary_booking_time')";

        if (!mysqli_query($conn, $query)) {
            echo "Error: " . mysqli_error($conn);
            exit();
        }
    }

    // Deduct seats from availability in schedule_list
    $seat_count = count($seats);
    $update_availability_query = "UPDATE schedule_list SET availability = availability - $seat_count WHERE id = '$schedule_id'";
    if (!mysqli_query($conn, $update_availability_query)) {
        echo "Error updating availability: " . mysqli_error($conn);
        exit();
    }

    // Redirect to confirm booking page with the schedule_id and user_id
    header("Location: confirm_booking.php?schedule_id=$schedule_id&user_id=$user_id");
    exit();
}
?>
