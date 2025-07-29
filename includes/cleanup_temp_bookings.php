<?php
// Function to clean up expired temporary bookings
function cleanupExpiredBookings($conn) {
    // Set your desired time zone
    date_default_timezone_set('Asia/Kathmandu'); // Replace with your local time zone

    // Get the current time as a DateTime object
    $current_time_exp = new DateTime();

    // Query to select expired temporary bookings
    $query_exp_temp = "SELECT schedule_id AS exp_schedule_id, temporary_booking_time 
                       FROM seat_reservation 
                       WHERE status = 'temporary'";
    $result_exp_temp = mysqli_query($conn, $query_exp_temp);

    if ($result_exp_temp) {
        // Create an array to track expired bookings per schedule
        $expired_bookings_count = [];

        while ($row_exp_temp = mysqli_fetch_assoc($result_exp_temp)) {
            $temporary_booking_time = new DateTime($row_exp_temp['temporary_booking_time']);
            $schedule_id = $row_exp_temp['exp_schedule_id'];

            // Convert temporary_booking_time to total minutes
            $temp_year = $temporary_booking_time->format('Y');
            $temp_month = $temporary_booking_time->format('m');
            $temp_day = $temporary_booking_time->format('d');
            $temp_hour = $temporary_booking_time->format('H');
            $temp_minute = $temporary_booking_time->format('i');

            $temp_total_minutes = ($temp_year * 365 * 24 * 60) + (($temp_month / 12) * 365 * 24 * 60) + ($temp_day * 24 * 60) + ($temp_hour * 60) + $temp_minute;

            // Convert current time to total minutes
            $current_year = $current_time_exp->format('Y');
            $current_month = $current_time_exp->format('m');
            $current_day = $current_time_exp->format('d');
            $current_hour = $current_time_exp->format('H');
            $current_minute = $current_time_exp->format('i');

            $current_total_minutes = ($current_year * 365 * 24 * 60) + (($current_month / 12) * 365 * 24 * 60) + ($current_day * 24 * 60) + ($current_hour * 60) + $current_minute;

            // Calculate the difference in minutes
            $minute_difference = $current_total_minutes - $temp_total_minutes;

            // If the difference is greater than 5 minutes, mark as expired
            if ($minute_difference > 5) {
                // Add the expired booking count for this schedule
                if (!isset($expired_bookings_count[$schedule_id])) {
                    $expired_bookings_count[$schedule_id] = 0;
                }
                $expired_bookings_count[$schedule_id]++;
            }
        }

        // Process expired bookings
        foreach ($expired_bookings_count as $schedule_id => $expired_count) {
            // Delete expired temporary bookings for this schedule
            $delete_query_exp_temp = "DELETE FROM seat_reservation 
                                      WHERE status = 'temporary' 
                                      AND schedule_id = $schedule_id 
                                      AND TIMESTAMPDIFF(MINUTE, temporary_booking_time, NOW()) > 5";

            if (mysqli_query($conn, $delete_query_exp_temp)) {
                // Update the availability in schedule_list
                $update_query_exp_temp = "UPDATE schedule_list 
                                          SET availability = availability + $expired_count 
                                          WHERE id = $schedule_id";

                if (!mysqli_query($conn, $update_query_exp_temp)) {
                    // Handle error for updating availability
                    echo "Error updating availability for schedule_id $schedule_id: " . mysqli_error($conn) . "<br>";
                }
            } else {
                // Handle error for deleting temporary bookings
                echo "Error deleting temporary bookings for schedule_id $schedule_id: " . mysqli_error($conn) . "<br>";
            }
        }
    } else {
        // Handle error for the initial query
        echo "Error querying expired temporary bookings: " . mysqli_error($conn) . "<br>";
    }

    // Optional: Debug message (remove in production)
    // echo "Expired temporary bookings cleaned up and availability updated.";
}
?>
