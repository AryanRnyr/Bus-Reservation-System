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

// Fetch all buses
$sql = "SELECT id, name, bus_number, status FROM bus";
$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="list_bus.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">

</head>
<body>
    <header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
                <li><a href="list_reservations.php">Reservations</a></li>
                
                <li class="dropdown">
                        <a href="#">Services <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                         <ul class="dropdown-content">
                            <li><a href="list_bus.php">List Buses</a></li>
                            <li><a href="list_location.php">List Location</a></li>
                            <li><a href="list_users.php">List Users</a></li>
                        </ul>
                </li>
                <li><a href="manage_schedule.php">Manage Schedule</a></li>
                <li><a href="list_cancelrequest.php">Cancellation Requests</a></li>
                 <!-- <li><a class="navbreak" href="list_tempbookings.php" >Temporary Bookings</a></li> -->
                 <li ><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="listtop">
    <h2>List of Reservations</h2>
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
    <form action="add_reservation.php" method="post" name="add">
    <button name="add"  type="button" onclick="window.location.href='add_reservation.php'">Book <i class="fa fa-plus"></i></button>
</form>
</div>
<table>
    <thead>
        <tr>
                    <th>Ticket No.</th>
                    <th>Full Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>ID Proof</th>
                    <th>Seats</th>
                    <th>Payment</th>
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
$order_query = "SELECT * FROM orders";
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
        $user_id=$order['user_id'];
        $schedule_id = $order['schedule_id']; // This is the schedule ID from the order
        $status=$order['status'];
        $order_id=$order['id'];
        $payment_method=$order['payment_method'];
        $phone=$order["phone"];

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
                <td><?= htmlspecialchars($payment_method)." - ".htmlspecialchars($fare/100); ?></td>
                <td><?= htmlspecialchars($bus_name); ?>: <?= htmlspecialchars($bus_number); ?></td>
                <!-- <td><?= htmlspecialchars($bus_number); ?></td> -->
                <td><?= htmlspecialchars($departure_city); ?></td>
                <td><?= htmlspecialchars($arrival_city); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($departure_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($departure_time))); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($arrival_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($arrival_time))); ?></td>
                <td><?= htmlspecialchars( strtoupper($status)); ?></td>
				<td>
                <?php if($status=="confirmed" || $status=="Cancellation Requested"): ?>
                        <?= '<a class="action-btn-edit" style="padding: 5px 12px;" href="../search/print_ticket.php?schedule_id='.$schedule_id.'&user_id='.$user_id.'&ticket_no='.$ticket_number.'">Print</a>' ?>
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
						
						// Convert current_time to total hours
						$current_year = $current_time->format('Y');
						$current_month = $current_time->format('m');
						$current_day = $current_time->format('d');
						$current_hour = $current_time->format('H');

						$current_total_hours = ($current_year * 365 * 24) + (($current_month / 12) * 365 * 24) + ($current_day * 24) + $current_hour;
						// Calculate the difference in hours
						$difference = $current_total_hours - $departure_total_hours;

						// If the difference is greater than 8 hours, show the delete button
						if ($difference < 8 && $status=="confirmed" ) {
							?>
							<span style="display:block; margin-top:8px;"></span>
							<!-- <a href="approve_cancelrequest.php?ticket_number=<?= $ticket_number; ?>&seats=<?= $seats; ?>&schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&order_id=<?= $order_id; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel</a> -->
                            <form id="cancelForm" action="cancel_ticket.php" method="POST">
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
                                <button style="font-size: 14px; padding:4px;" type="submit" class="action-btn-delete" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel</button>

                            </form>
							<?php
						}
						?>

                        <?php else: ?>
                        <?php endif; ?>
                        <!-- <a class="action-btn-edit" style="padding-left: 11px; padding-right:11px;" href="../search/print_ticket.php?schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&ticket_no=<?= $ticket_number; ?>">Print</a> -->

						





					</td>
            </tr>
            <?php
        } else {
            echo "No schedule details found for schedule ID: $schedule_id.";
        }
    }
} else {
    // echo "No ticket details found for the logged-in user.";
}
?>

    </tbody>
</table>

<!-- Edit Bus Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Edit Bus Details</h2>
        
        <form id="editBusForm" method="post" action="edit_bus.php" onsubmit="return validateBusName()">
            <input type="hidden" id="busId" name="bus_id">
            
            <label for="bus_name">Bus Name:</label>
            <input type="text" id="bus_name" name="name" required><br><br>
            
            <label for="bus_number">Bus Number:</label>
            <input type="text" id="bus_number" name="bus_number" required><br><br>
            
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select><br><br>

            <button type="submit">Update Bus</button>
        </form>
    </div>
</div>


<?php
// Close the database connection
$conn->close();
?>
    </main>
<br><br>
      
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

            reservationRows.forEach(row => {
                // Parse departure date and time from the table row
                const departureDateText = row.cells[10].innerText.split('\n')[0] || '';
                const departureTimeText = row.cells[10].innerText.split('\n')[1] || '';
                const departureDateTime = new Date(`${departureDateText} ${departureTimeText}`);

                // Show or hide row based on filter selection
                if (selectedFilter === 'all') {
                    row.style.display = ''; // Show all reservations
                } else if (selectedFilter === 'past') {
                    row.style.display = departureDateTime < currentTime ? '' : 'none'; // Show past reservations only
                } else if (selectedFilter === 'present') {
                    row.style.display = departureDateTime >= currentTime ? '' : 'none'; // Show present/upcoming reservations only
                }
            });
        }

        // Initial filter application based on the default selected filter
        filterReservations();
    });


    // Get modal and close button
var modal = document.getElementById("editModal");
var closeModal = document.getElementById("closeModal");

// Open the modal when clicking "Edit" button
function openEditModal(busId, busName, busNumber, busStatus) {
    // Set the current bus details in the modal
    document.getElementById("busId").value = busId;
    document.getElementById("bus_name").value = busName;
    document.getElementById("bus_number").value = busNumber;
    document.getElementById("status").value = busStatus;

    // Show the modal
    modal.style.display = "block";
}

// Close the modal when clicking on "X"
closeModal.onclick = function() {
    modal.style.display = "none";
}

// Close the modal if clicked outside of the modal content
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}


// Populate the modal with data when edit button is clicked
function openEditScheduleModal(scheduleId, fromLocation, toLocation, busId, departureTime, eta, availability, price, status) {
    // Set hidden field with schedule ID
    document.getElementById("scheduleId").value = scheduleId;

    // Set select options and other fields with current values
    document.getElementById("fromLocation").value = fromLocation;
    document.getElementById("toLocation").value = toLocation;
    document.getElementById("bus").value = busId;
    document.getElementById("departureTime").value = departureTime;
    document.getElementById("eta").value = eta;
    document.getElementById("availability").value = availability;
    document.getElementById("price").value = price;
    document.getElementById("status").value = status;

    // Show the modal
    document.getElementById("editScheduleModal").style.display = "block";
}

// Close the modal
document.getElementById("closeEditModal").onclick = function() {
    document.getElementById("editScheduleModal").style.display = "none";
};

// Close modal if clicked outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById("editScheduleModal")) {
        document.getElementById("editScheduleModal").style.display = "none";
    }
};

 
</script>
<script src="script.js"></script>
</body>
</html>
