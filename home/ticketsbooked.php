<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION["s_role"] == "admin") {
    header("Location:../admin/");
    exit();
}
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';
$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// We don't have the password or email info stored in sessions, so instead, we can get the results from the database.
$stmt = $conn->prepare('SELECT firstname, lastname, phoneno, email, user_image FROM users WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $phoneno, $email, $user_image);
$stmt->fetch();
$stmt->close();


$user_id = $_SESSION['id'];



?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">


		<title>Profile Page</title>
		<link href="ticketsbooked.css" rel="stylesheet" type="text/css">
		<link rel="icon" type="images/x-icon" href="../images/favicon.ico">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
		<style>
			.action-btn-print {
            margin: 0 5px;
            width: 40px;
            text-decoration: none;
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
            padding: 5px;
			padding-left: 28px;
			padding-right: 28px;
            border-radius: 0.25rem;
        }

        table{
            width:100%;
        }

        .cancelconfirmation{
            background-color:white; 
            width: 50%; 
            margin: 100px auto; 
            padding: 20px; 
            border-radius: 5px;
        }

        @media (max-width: 768px) {
    .cancelconfirmation {
        background-color: white;
        width: unset;
        margin: 100px auto;
        padding: 20px;
        border-radius: 5px;
    }
}
		</style>
	</head>
	
	<body class="loggedin">
	<header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <button class="hamburger" aria-label="Toggle menu">
            ☰
        </button>
            <ul class="nav-links">
				<li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
				<li><a href="profile.php"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
				<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

	
		
		<div class="content">
			<h2>Profile Page</h2>

			<img src="../images/users/<?php echo $user_image;?>" width="200" class="img-circle" alt="Profile"> 
	
			<div class="tab">

			<div class="buttonss">
            <button class="tablinks" style="width: 33%" onclick="document.location='profile.php'">Personal Details</button>
            <button class="tablinks-active" style="width: 33%" onclick="document.location='ticketsbooked.php'">Tickets Booked</button>
            <button class="tablinks" style="width: 33%"  onclick="document.location='editdetails.php'">Edit Details</button>
			</div>
			
			<hr style="border-color: rgb(168, 168, 168); width:50%; margin:0 auto; opacity: 20%; margin-top:60px; margin-bottom:15px">
            <div class="filter-options">
            <!-- <select id="sortSchedules" onchange="sortSchedules()">
        <option value="">Sort by...</option>
        <option value="departure_time">By Departure</option>
        <option value="arrival_time">By Arrival</option>
        <option value="availability">By Availability</option>
        <option value="price">By Price</option>
        <option value="status">By Status</option>
    </select> -->
    <label for="">&nbsp;&nbsp;</label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="reservationFilter" value="all" id="allReservations" checked> All Reservations
    </label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="reservationFilter" value="past" id="pastReservations"> Past Reservations
    </label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="reservationFilter" value="present" id="presentReservations"> Present Reservations
    </label>
    
</div>
<div class="table-container">
			<table border="1px solid black" cellspacing="0">
            <thead>
                <tr>
                    <th>Ticket No.</th>
                    <th>Full Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>ID Proof</th>
                    <th>Seats</th>
                    <th>Fare</th>
                    <th>Bus</th>
                    <th>Departure City</th>
                    <th>Arrival City</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
			<tbody>
<?php
			// Step 1: Query the orders table to fetch all orders by the logged-in user
$order_query = "SELECT * FROM orders WHERE user_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

// Step 2: Check if there are any orders and fetch them
if (mysqli_num_rows($order_result) > 0) {
    while ($order = mysqli_fetch_assoc($order_result)) {
        // Extract order details
        $ticket_number = $order['ticket_no'];
        $full_name = $order['name'];
        $age = $order['age'];
        $gender = $order['gender'];
        $id_proof = $order['id_proof'];
        $id_details = $order['id_details'];
        $seats = $order['seats'];
        $fare = $order['fare'];
        $status=$order['status'];
        $order_id=$order['id'];
        $phone=$order['phone'];
        $schedule_id = $order['schedule_id']; // This is the schedule ID from the order

        // Step 3: Query the schedule_list table based on the schedule ID
        $schedule_query = "SELECT bus_id, from_location, to_location, departure_time, eta FROM schedule_list WHERE id = '$schedule_id'";
        $schedule_result = mysqli_query($conn, $schedule_query);

        if (mysqli_num_rows($schedule_result) > 0) {
            $schedule = mysqli_fetch_assoc($schedule_result);
            $bus_id = $schedule['bus_id'];
            $from_location = $schedule['from_location'];
            $to_location = $schedule['to_location'];
            $departure_time = $schedule['departure_time']; // Departure time
            $arrival_time = $schedule['eta']; // Arrival time

            // Step 4: Query the bus table to get bus details
            $bus_query = "SELECT name AS bus_name, bus_number FROM bus WHERE id = '$bus_id'";
            $bus_result = mysqli_query($conn, $bus_query);
            if (mysqli_num_rows($bus_result) > 0) {
                $bus = mysqli_fetch_assoc($bus_result);
                $bus_name = $bus['bus_name'];
                $bus_number = $bus['bus_number'];
            }

            // Step 5: Query the location table for departure and arrival cities
            $departure_query = "SELECT city AS departure_city FROM location WHERE id = '$from_location'";
            $arrival_query = "SELECT city AS arrival_city FROM location WHERE id = '$to_location'";

            $departure_result = mysqli_query($conn, $departure_query);
            $arrival_result = mysqli_query($conn, $arrival_query);

            if (mysqli_num_rows($departure_result) > 0) {
                $departure = mysqli_fetch_assoc($departure_result);
                $departure_city = $departure['departure_city'];
            }

            if (mysqli_num_rows($arrival_result) > 0) {
                $arrival = mysqli_fetch_assoc($arrival_result);
                $arrival_city = $arrival['arrival_city'];
            }

            // Step 6: Output the results in the table
            ?>
            <tr>
                <td><?= htmlspecialchars($ticket_number); ?></td>
                <td><?= htmlspecialchars($full_name); ?></td>
                <td><?= htmlspecialchars($age); ?></td>
                <td><?= htmlspecialchars(ucfirst($gender)); ?></td>
                <td><?= htmlspecialchars($id_proof); ?>: <?= htmlspecialchars($id_details); ?></td>
                <td><?= htmlspecialchars($seats); ?></td>
                <td><?= htmlspecialchars($fare/100); ?></td>
                <td><?= htmlspecialchars($bus_name); ?>: <?= htmlspecialchars($bus_number); ?></td>
                <!-- <td><?= htmlspecialchars($bus_number); ?></td> -->
                <td><?= htmlspecialchars($departure_city); ?></td>
                <td><?= htmlspecialchars($arrival_city); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($departure_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($departure_time))); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($arrival_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($arrival_time))); ?></td>
                <td><?= htmlspecialchars(strtoupper($status)); ?></td>
				<td>
                        <?php if($status=="confirmed" || $status=="Cancellation Requested"): ?>
                        <?= '<a style="padding-left:32%; padding-right:32%;" class="action-btn-print" href="../search/print_ticket.php?schedule_id='.$schedule_id.'&user_id='.$user_id.'&ticket_no='.$ticket_number.'">Print</a>' ?>
                        <?php elseif($status=="CANCELED"): ?>
                        <?= '<a style=" display: inline-block; text-align: center; vertical-align: middle; width:80%; padding:5px;" class="action-btn-print" href="../search/print_cancelticket.php?schedule_id='.$schedule_id.'&user_id='.$user_id.'&ticket_no='.$ticket_number.'">Print Canceled Ticket</a>' ?>
                            <?php endif; ?>

						<?php
						// Assuming $departure_time is in a format like 'Y-m-d H:i:s'
						$current_time = new DateTime(); // Get current date and time
						$departure_time = new DateTime($departure_time); // Convert departure_time to DateTime object

						// Convert departure_time to total hours
						$departure_year = $departure_time->format('Y');
						$departure_month = $departure_time->format('m');
						$departure_day = $departure_time->format('d');
						$departure_hour = $departure_time->format('H');

						$departure_total_hours = ($departure_year * 365 * 24) + (($departure_month / 12) * 365 * 24) + ($departure_day * 24) + $departure_hour;
						// var_dump("Departure".$departure_total_hours);

						// Convert current_time to total hours
						$current_year = $current_time->format('Y');
						$current_month = $current_time->format('m');
						$current_day = $current_time->format('d');
						$current_hour = $current_time->format('H');

						$current_total_hours = ($current_year * 365 * 24) + (($current_month / 12) * 365 * 24) + ($current_day * 24) + $current_hour;
						// var_dump("Current".$current_total_hours);
                        // Calculate the difference in hours
						$difference = $current_total_hours - $departure_total_hours;

						// If the difference is greater than 8 hours, show the delete button
						if ($difference < -8 && $status=="confirmed") {
							?>
							<span style="display:block; margin-top:8px;"></span>
                           
                            <form id="cancelForm" action="cancel_request.php" method="POST">
                                <input type="hidden" value="<?= $ticket_number; ?>" name="ticket_number">
                                <input type="hidden" value="<?= $full_name; ?>" name="full_name">
                                <input type="hidden" value="<?= $schedule_id; ?>" name="schedule_id">
                                <input type="hidden" value="<?= $user_id; ?>" name="user_id">
                                <input type="hidden" value="<?= $order_id; ?>" name="order_id">
                                <input type="hidden" value="<?= $bus_name." : ".$bus_number; ?>" name="bus_name">
                                <input type="hidden" value="<?= $seats; ?>" name="seats">
                                <input type="hidden" value="<?= $fare/100; ?>" name="fare">
                                <input type="hidden" value="<?= $phone; ?>" name="phone">
                                <input type="hidden" value="<?= $departure_city; ?>" name="departure_city">
                                <input type="hidden" value="<?= $schedule['departure_time']; ?>" name="departure_time">
                                <input type="hidden" value="<?= $arrival_city; ?>" name="arrival_city">
                                <input type="hidden" value="<?= $schedule['eta']; ?>" name="arrival_time">

                                <!-- <button style="font-size: 14px; padding:4px;" name="submit" type="submit" class="action-btn-cancel" onclick="return confirm('Are you sure you want to request for cancellation?');">Request for Cancellation</button> -->
                                <button style="font-size: 14px; padding:4px;" type="button" class="action-btn-cancel" onclick="showCancelModal(this)" data-ticket="<?= $ticket_number; ?>" data-fullname="<?= $full_name; ?>" data-schedule="<?= $schedule_id; ?>" data-user="<?= $user_id; ?>" data-order="<?= $order_id; ?>" data-bus="<?= $bus_name . ' : ' . $bus_number; ?>" data-seats="<?= $seats; ?>" data-fare="<?= $fare/100; ?>" data-phone="<?= $phone; ?>" data-departure="<?= $departure_city; ?>" data-departuretime="<?= $schedule['departure_time']; ?>" data-arrival="<?= $arrival_city; ?>" data-arrivaltime="<?= $schedule['eta']; ?>">
    Request for Cancellation
</button>


                            </form>
							<!-- <a href="cancelrequest_bus.php?ticket_number=<?= $ticket_number; ?>" class="action-btn-cancel" onclick="return confirm('Are you sure you want to request for cancellation?');">Cancel</a> -->
							<?php
						}
						?>





					</td>
            </tr>
            <?php
        } else {
            // echo "No schedule details found for schedule ID: $schedule_id.";
        }
    }
} else {
    echo "<tr><td colspan='14' style='text-align:center;'>No previous tickets have been booked!</td></tr>";
}
?>

    </tbody>
        </table>
        </div>
        <div class="note" style="margin: 0 auto;">
        <h3>Note: A 'Cancellation Requested' status does not mean that your bus ticket has been canceled!</h3>
        </div>
			</div>
		</div>
<!-- Custom Modal -->
<!-- <div id="cancelModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
    <div class="cancelconfirmation">
        <h3>Cancellation Confirmation</h3>
        <p>Are you sure you want to request for cancellation?</p>
        <p><strong>Cancellation charges will apply as follows:</strong></p>
        <ol style="list-style-type: lower-roman; text-align: left; padding-left: 20px;">
            <li>8-12 hours before journey: <strong>80% cancellation charge</strong></li>
            <li>12-24 hours before journey: <strong>50% cancellation charge</strong>.</li>
            <li>More than 24 hours before journey: <strong>10% cancellation charge</strong>.</li>
        </ol>
        <button class="action-btn-cancel" onclick="confirmCancellation()">Yes, Cancel</button>
        <button class="action-btn-print" style="width:max-content;" onclick="closeModal()">Cancel</button>
    </div>
</div> -->
<div id="cancelModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000;">
    <div class="cancelconfirmation">
        <h3>Cancellation Confirmation</h3>
        <p>Are you sure you want to request for cancellation?</p>
        <p><strong>Cancellation charges will apply as follows:</strong></p>
        <ol style="list-style-type: lower-roman; text-align: left; padding-left: 20px;">
            <li>8-12 hours before journey: <strong>80% cancellation charge</strong></li>
            <li>12-24 hours before journey: <strong>50% cancellation charge</strong>.</li>
            <li>More than 24 hours before journey: <strong>10% cancellation charge</strong>.</li>
        </ol>
        
        <form id="cancelForm" action="cancel_request.php" method="POST">
            <input type="hidden" name="ticket_number">
            <input type="hidden" name="full_name">
            <input type="hidden" name="schedule_id">
            <input type="hidden" name="user_id">
            <input type="hidden" name="order_id">
            <input type="hidden" name="bus_name">
            <input type="hidden" name="seats">
            <input type="hidden" name="fare">
            <input type="hidden" name="phone">
            <input type="hidden" name="departure_city">
            <input type="hidden" name="departure_time">
            <input type="hidden" name="arrival_city">
            <input type="hidden" name="arrival_time">
            <button type="button" class="action-btn-cancel" onclick="confirmCancellation()">Yes, Cancel</button>
            <button type="button" class="action-btn-print" style="width:max-content;" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

        <script>
             function showCancelModal(button) {
        // Get modal and form
        const modal = document.getElementById('cancelModal');
        const form = document.getElementById('cancelForm');

        // Populate hidden fields with button data attributes
        form.ticket_number.value = button.getAttribute('data-ticket');
        form.full_name.value = button.getAttribute('data-fullname');
        form.schedule_id.value = button.getAttribute('data-schedule');
        form.user_id.value = button.getAttribute('data-user');
        form.order_id.value = button.getAttribute('data-order');
        form.bus_name.value = button.getAttribute('data-bus');
        form.seats.value = button.getAttribute('data-seats');
        form.fare.value = button.getAttribute('data-fare');
        form.phone.value = button.getAttribute('data-phone');
        form.departure_city.value = button.getAttribute('data-departure');
        form.departure_time.value = button.getAttribute('data-departuretime');
        form.arrival_city.value = button.getAttribute('data-arrival');
        form.arrival_time.value = button.getAttribute('data-arrivaltime');

        // Show modal
        modal.style.display = 'block';
    }

    function closeModal() {
        document.getElementById('cancelModal').style.display = 'none';
    }

    function confirmCancellation() {
        document.getElementById('cancelForm').submit();
        closeModal();
    }


    //          document.addEventListener('DOMContentLoaded', function() {
    //     const allReservationsRadio = document.getElementById('allReservations');
    //     const pastReservationsRadio = document.getElementById('pastReservations');
    //     const presentReservationsRadio = document.getElementById('presentReservations');

    //     // Set up event listeners for radio buttons
    //     allReservationsRadio.addEventListener('change', filterReservations);
    //     pastReservationsRadio.addEventListener('change', filterReservations);
    //     presentReservationsRadio.addEventListener('change', filterReservations);

    //     function filterReservations() {
    //         const selectedFilter = document.querySelector('input[name="reservationFilter"]:checked').value;
    //         const currentTime = new Date();

    //         // Get all rows in the reservations table
    //         const reservationRows = document.querySelectorAll('table tbody tr');

    //         reservationRows.forEach(row => {
    //             // Parse departure date and time from the table row
    //             const departureDateText = row.cells[10].innerText.split('\n')[0] || '';
    //             const departureTimeText = row.cells[10].innerText.split('\n')[1] || '';
    //             const departureDateTime = new Date(`${departureDateText} ${departureTimeText}`);

    //             // Show or hide row based on filter selection
    //             if (selectedFilter === 'all') {
    //                 row.style.display = ''; // Show all reservations
    //             } else if (selectedFilter === 'past') {
    //                 row.style.display = departureDateTime < currentTime ? '' : 'none'; // Show past reservations only
    //             } else if (selectedFilter === 'present') {
    //                 row.style.display = departureDateTime >= currentTime ? '' : 'none'; // Show present/upcoming reservations only
    //             }
    //         });
    //     }

    //     // Initial filter application based on the default selected filter
    //     filterReservations();
    // });
    document.addEventListener('DOMContentLoaded', function () {
    const allReservationsRadio = document.getElementById('allReservations');
    const pastReservationsRadio = document.getElementById('pastReservations');
    const presentReservationsRadio = document.getElementById('presentReservations');

    // Set up event listeners for radio buttons
    allReservationsRadio.addEventListener('change', filterReservations);
    pastReservationsRadio.addEventListener('change', filterReservations);
    presentReservationsRadio.addEventListener('change', filterReservations);

    function filterReservations() {
        const selectedFilter = document.querySelector('input[name="reservationFilter"]:checked').value;
        const currentTime = new Date();

        // Get all rows in the reservations table
        const reservationRows = document.querySelectorAll('table tbody tr');

        // First, reset all rows to be visible
        reservationRows.forEach(row => row.style.display = '');

        reservationRows.forEach(row => {
            // Parse departure date and time from the table row
            const departureDateText = row.cells[10].innerText.split('\n')[0] || '';
            const departureTimeText = row.cells[10].innerText.split('\n')[1] || '';
            const departureDateTime = new Date(`${departureDateText} ${departureTimeText}`);

            // Get the status of the ticket (assuming status is in column index 12)
            const ticketStatus = row.cells[12].innerText.trim().toLowerCase(); // Example: "Cancelled"

            // Show or hide row based on filter selection
            if (selectedFilter === 'all') {
                row.style.display = ''; // Show all reservations
            } else if (selectedFilter === 'past') {
                row.style.display = (departureDateTime < currentTime || ticketStatus === 'canceled') ? '' : 'none'; // Show past reservations or cancelled ones
            } else if (selectedFilter === 'present') {
                row.style.display = (departureDateTime >= currentTime && ticketStatus !== 'canceled') ? '' : 'none'; // Show present reservations, excluding cancelled ones
            }
        });
    }

    // Initial filter application based on the default selected filter
    filterReservations();
});




    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});

        </script>
	</body>
</html>