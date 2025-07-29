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
    <style>
        .action-btn-deny:hover{
            background-color: #0a5561;
        }
    </style>
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
    <h2>List of Requested Cancellation</h2>
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
        <input type="radio" name="reservationFilter" value="pending" id="pendingReservations"> Pending Reservations
    </label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="reservationFilter" value="canceled" id="canceledReservations"> Canceled Reservations
    </label>
</div>
    <!-- <form action="add_bus.php" method="post" name="add"> -->
    <!-- <button name="add"  type="button" onclick="window.location.href='add_bus.php'">Add New <i class="fa fa-plus"></i></button> -->
<!-- </form> -->
</div>
<table style="width: 100%;" >
    <thead>
        <tr>
                    <th>Ticket No.</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Seats</th>
                    <th>Fare</th>
                    <th>Bus</th>
                    <th>Departure City</th>
                    <th>Arrival City</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Status</th>
                    <th>Requested Time</th>
                    <th>Refund Amount</th>
                    <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
			// Step 1: Query the orders table to fetch all orders by the logged-in user
$cancel_query = "SELECT * FROM cancel_request";
$cancel_result = mysqli_query($conn, $cancel_query);

// Step 2: Check if there are any orders and fetch them
if (mysqli_num_rows($cancel_result) > 0) {
    while ($cancel = mysqli_fetch_assoc($cancel_result)) {
        // Extract order details
        $ticket_number = $cancel['ticket_no'];
        $full_name = $cancel['name'];
        $seats = $cancel['seats'];
        $fare = $cancel['fare']*100;
        $phone=$cancel['phone'];
        $bus_name=$cancel['bus_name'];
        $departure_city=$cancel['departure_city'];
        $arrival_city=$cancel['arrival_city'];
        $departure_time=$cancel['departure_time'];
        $arrival_time=$cancel['arrival_time'];
        $status=$cancel['status'];
        $requested_time=$cancel['requested_time'];

        $order_id=$cancel['order_id'];
        $user_id=$cancel['user_id'];
        $schedule_id = $cancel['schedule_id']; // This is the schedule ID from the order


        //Refund amount calculation
        

            // Step 6: Output the results in the table
            ?>
            <tr>
                <td><?= htmlspecialchars($ticket_number); ?></td>
                <td><?= htmlspecialchars($full_name); ?></td>
                <td><?= htmlspecialchars($phone); ?></td>
                <td><?= htmlspecialchars($seats); ?></td>
                <td>Rs. <?= htmlspecialchars($fare); ?></td>
                <td><?= htmlspecialchars($bus_name); ?></td>
                <td><?= htmlspecialchars($departure_city); ?></td>
                <td><?= htmlspecialchars($arrival_city); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($departure_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($departure_time))); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($arrival_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($arrival_time))); ?></td>
                <td><?= htmlspecialchars($status); ?></td>
                <td><?= htmlspecialchars(date("Y/m/d", strtotime($requested_time))); ?><br><?= htmlspecialchars(date("h:i A", strtotime($requested_time))); ?></td>
                <td>
                    <?php
                                        // Assuming $departure_time is in a format like 'Y-m-d H:i:s'
                    $current_time = new DateTime(); // Get current date and time
                    $requested_time = new DateTime($requested_time); // Convert requested_time to DateTime object
                    $departure_timefor = new DateTime($departure_time); // Convert departure_time to DateTime object

                    // Convert departure_time to total hours
                    $departure_year = $departure_timefor->format('Y');
                    $departure_month = $departure_timefor->format('m');
                    $departure_day = $departure_timefor->format('d');
                    $departure_hour = $departure_timefor->format('H');

                    $departure_total_hours = ($departure_year * 365 * 24) + (($departure_month / 12) * 365 * 24) + ($departure_day * 24) + $departure_hour;

                    // Convert requested_time to total hours
                    $requested_year = $requested_time->format('Y');
                    $requested_month = $requested_time->format('m');
                    $requested_day = $requested_time->format('d');
                    $requested_hour = $requested_time->format('H');

                    $requested_total_hours = ($requested_year * 365 * 24) + (($requested_month / 12) * 365 * 24) + ($requested_day * 24) + $requested_hour;

                    // Calculate the difference in hours
                    $hours_difference =  $departure_total_hours - $requested_total_hours;
                    // Calculate refund based on cancellation policy
                    if ($hours_difference <= 12) {
                        $refund = $fare * 0.20;  // 20% refund, 80% charge
                    } elseif ($hours_difference <= 24) {
                        $refund = $fare * 0.50;  // 50% refund, 50% charge
                    } else {
                        $refund = $fare * 0.90;  // 90% refund, 10% charge
                    }
                    ?>
                    Rs. <?= htmlspecialchars($refund); ?>
                </td>
				<td>
                        <!-- <a class="action-btn-edit" style="padding-left: 11px; padding-right:11px;" href="../search/print_ticket.php?schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&ticket_no=<?= $ticket_number; ?>">Print</a> -->

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
						if ($difference < 8 && $status=="PENDING") {
							?>
                            <a href="approve_cancelrequest.php?ticket_number=<?= $ticket_number; ?>&seats=<?= $seats; ?>&schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&order_id=<?= $order_id; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to cancel this ticket?');">Approve</a>
							<span style="display:block; margin-top:8px;"></span>
							<a style="padding-right: 15px; padding-left: 15px; background-color:#17a2b8;" href="deny_cancelrequest.php?ticket_number=<?= $ticket_number; ?>&seats=<?= $seats; ?>&schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&order_id=<?= $order_id; ?>" class="action-btn-delete" id="action-btn-deny" onclick="return confirm('Are you sure you want to deny this cancellation request?');">Deny</a>
							<?php
						}
						?>





					</td>
            </tr>
            <?php
        
    }
} else {
    
    echo "<tr><td colspan='14' style='text-align:center;'>No cancellation requests found</td></tr>";
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
     document.addEventListener('DOMContentLoaded', function () {
        const allReservationsRadio = document.getElementById('allReservations');
        const pendingReservationsRadio = document.getElementById('pendingReservations');
        const canceledReservationsRadio = document.getElementById('canceledReservations');

        // Set up event listeners for radio buttons
        allReservationsRadio.addEventListener('change', filterReservations);
        pendingReservationsRadio.addEventListener('change', filterReservations);
        canceledReservationsRadio.addEventListener('change', filterReservations);

        function filterReservations() {
            const selectedFilter = document.querySelector('input[name="reservationFilter"]:checked').value;

            // Get all rows in the reservations table
            const reservationRows = document.querySelectorAll('table tbody tr');

            reservationRows.forEach(row => {
                // Parse the status from the table row (adjust cell index if needed)
                const status = row.cells[10]?.innerText.trim().toLowerCase(); // Assuming the 11th cell (index 10) contains the status

                // Show or hide rows based on the selected filter
                if (selectedFilter === 'all') {
                    row.style.display = ''; // Show all rows
                } else if (selectedFilter === 'pending') {
                    row.style.display = status === 'pending' ? '' : 'none'; // Show rows with 'pending' status
                } else if (selectedFilter === 'canceled') {
                    row.style.display = status === 'canceled' ? '' : 'none'; // Show rows with 'canceled' status
                }
            });
        }

        // Apply the initial filter on page load
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
