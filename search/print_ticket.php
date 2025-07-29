<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
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

// Retrieve the parameters from the URL
$schedule_id = $_GET['schedule_id'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$ticket_no = $_GET['ticket_no'] ?? '';

// Validate the input
if (empty($schedule_id) || empty($user_id) || empty($ticket_no)) {
    echo "Invalid request. Missing schedule ID, user ID, or ticket number.";
    exit();
}

// Step 1: Query the orders table
$order_query = "SELECT * FROM orders WHERE schedule_id = '$schedule_id' AND user_id = '$user_id' AND ticket_no = '$ticket_no'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) > 0) {
    $order = mysqli_fetch_assoc($order_result);

    // Extract order details
    $ticket_number = $order['ticket_no'];
    $full_name = $order['name'];
    $age = $order['age'];
    $gender = $order['gender'];
    $id_proof = $order['id_proof'];
    $id_details = $order['id_details'];
    $seats = $order['seats'];
    $fare = $order['fare'];

    // Step 2: Query the schedule_list table
    $schedule_query = "SELECT 
                            s.departure_time, 
                            s.eta AS arrival_time, 
                            b.name AS bus_name, 
                            b.bus_number, 
                            l1.city AS departure_city, 
                            l2.city AS arrival_city 
                        FROM 
                            schedule_list s
                        INNER JOIN 
                            bus b ON s.bus_id = b.id
                        INNER JOIN 
                            location l1 ON s.from_location = l1.id
                        INNER JOIN 
                            location l2 ON s.to_location = l2.id
                        WHERE 
                            s.id = '$schedule_id'";
    $schedule_result = mysqli_query($conn, $schedule_query);

    if (mysqli_num_rows($schedule_result) > 0) {
        $schedule = mysqli_fetch_assoc($schedule_result);

        // Extract schedule details
        $departure_time = $schedule['departure_time'];
        $arrival_time = $schedule['arrival_time'];
        $departure_city = $schedule['departure_city'];
        $arrival_city = $schedule['arrival_city'];
        $bus_name = $schedule['bus_name'];
        $bus_number = $schedule['bus_number'];

       

        $state=1;
    } else {
        echo "No schedule details found for the provided schedule ID.";
        $state=0;
    }
} else {
    $state=0;
    echo "No ticket details found for the provided schedule ID, user ID, and ticket number.";
}

mysqli_close($conn);

$formatted_departure_time = new DateTime($departure_time);
$formatted_departure_time = $formatted_departure_time->format('l, F j, Y');

// Create a new DateTime object to format in the new style
$formatted_departure_time_custom = new DateTime($departure_time);

// Get the time in "5 AM" format
$time_part = $formatted_departure_time_custom->format('g A');

// Get the day with suffix (e.g., 27th)
$day_part = $formatted_departure_time_custom->format('j');
$day_suffix = 'th'; // Default suffix
if ($day_part == 1 || $day_part == 21 || $day_part == 31) {
    $day_suffix = 'st';
} elseif ($day_part == 2 || $day_part == 22) {
    $day_suffix = 'nd';
} elseif ($day_part == 3 || $day_part == 23) {
    $day_suffix = 'rd';
}
$day_part .= $day_suffix;

// Get the month name (e.g., November)
$month_part = $formatted_departure_time_custom->format('F');

// Combine them as "5 AM, 27th November"
$formatted_departure_time_custom = $time_part . ', ' . $day_part . ' ' . $month_part;


// Create a new DateTime object to format in the new style
$formatted_arrival_time_custom = new DateTime($arrival_time);

// Get the time in "5 AM" format
$time_part = $formatted_arrival_time_custom->format('g A');

// Get the day with suffix (e.g., 27th)
$day_part = $formatted_arrival_time_custom->format('j');
$day_suffix = 'th'; // Default suffix
if ($day_part == 1 || $day_part == 21 || $day_part == 31) {
    $day_suffix = 'st';
} elseif ($day_part == 2 || $day_part == 22) {
    $day_suffix = 'nd';
} elseif ($day_part == 3 || $day_part == 23) {
    $day_suffix = 'rd';
}
$day_part .= $day_suffix;

// Get the month name (e.g., November)
$month_part = $formatted_arrival_time_custom->format('F');

// Combine them as "5 AM, 27th November"
$formatted_arrival_time_custom = $time_part . ', ' . $day_part . ' ' . $month_part;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Ticket <?= htmlspecialchars($ticket_number) ?></title>
    <link rel="stylesheet" href="ticket.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
</head>
<body>
<?php if($state===1): ?>
    <div class="ticket-container">
        <div class="header">
            <img src="../images/logo2.png" alt="RN Bus Logo" class="logo">
            <h2 class="company_name">RN Bus Pvt. Ltd.</h2>
            <div class="contact">
                <p>Travel related queries</p>
                <p>+977-9840594031</p>
            </div>
        </div>

        <div class="journey-details">
            <div>
                <h3><?= htmlspecialchars($departure_city) ?> to <?= htmlspecialchars($arrival_city) ?></h3>
            </div>
            <div>
                <h3><?= htmlspecialchars($formatted_departure_time) ?></h3>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Passenger Name</th>
                    <th>Ticket Number</th>
                    <th>Seat Numbers</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($full_name) ?></td>
                    <td><?= htmlspecialchars($ticket_number) ?></td>
                    <td class="highlight"><?= htmlspecialchars($seats) ?></td>
                </tr>
            </tbody>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th style="width:30%">Bus Type</th>
                    <th style="width:25%">Source</th>
                    <th style="width:25%">Destination</th>
                    <th style="width:20%">Total Fare</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width:30%"><?= htmlspecialchars($bus_name) ?></td>
                    <td style="width:25%">
                    <span style="display:block; margin-bottom:10px;"><?= htmlspecialchars($departure_city) ?></span>
                    <span><?= htmlspecialchars($formatted_departure_time_custom) ?></span>
                    </td>
                    <td style="width:25%">
                    <span style="display:block; margin-bottom:10px;"><?= htmlspecialchars($arrival_city) ?></span>
                    <span><?= htmlspecialchars($formatted_arrival_time_custom) ?></span>
                    </td>
                    <td style="width:20%">Rs. <?= htmlspecialchars($fare)/100 ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p class="termsconditions">Terms and Conditions:</p>
            <ol style="text-align: left; padding-left: 20px; margin-left: 0;">
            <li>Passengers must carry a valid ID proof.</li>
            <li>Reporting time is 30 minutes before departure.</li>
            <li>The departure time mentioned on the ticket are only tentative timings .
                However the bus will not leave the source before the time that is mentioned on
                the ticket.</li>
            <li>Passengers are required to furnish the following at the time of boarding the bus:
                <ol style="list-style-type: lower-roman; text-align: left; padding-left: 20px;">
                <li>A copy of the ticket (A print out of the ticket or the print out of the ticket e-mail).</li>
                <li>A valid identity proof</li>
                    
                    </ol>
                    Failing to do so, they may not be allowed to board the bus.
                </li>
            <li>Cancellation Policy: Between 0 hours to 12 hours before journey, the
                cancellation charge is 100.0%.Between 12 hours to 24 hours before journey, the
                cancellation charge is 50.0%. And, above cancellation charge is 10.0%</l>
            <li>In case one needs the refund to be credited back to his/her bank account, please write
            your details to support@rnbus.com.np</li>
            </ol>
            <p style=" width: fit-content;margin: 0 auto; "><strong>For support, contact us at:</strong> +977-9840594031</p>
        </div>

        <!-- <button class="ticket-print" onclick="window.print()">Print Ticket</button> -->
       
    </div>
         <div class="ticket-print" style=" width: fit-content;margin: 0 auto; ">
            <button onclick="printTicket()">Print Ticket</button>
            <button style="margin-left:10px;" onclick="window.location.href='../index.php';">Back to Home</button>
        </div>

 
   

    <?php else: ?>
        <?php echo "No Details Found"; ?>
        <?php endif;?>


        <script>
        function printTicket() {
            window.print();
        }
        </script>
</body>
</html>