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
                <!-- <li><a href="list_tempbookings.php" >Temporary Bookings</a></li> -->
                <li ><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="listtop">
    <h2>List of Temporary Bookings</h2>
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
                    <th>Schedule ID</th>
                    <th>Seat Number</th>
                    <th>User ID</th>
                    <!-- <th>Seats</th> -->
                    <th>Fare</th>
                    <th>Status</th>
                    <th>Temp Booking Time</th>
                    <!-- <th>Arrival City</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Status</th>
                    <th>Requested Time</th>
                    <th>Refund Amount</th>
                    <th>Actions</th> -->
        </tr>
    </thead>
    <tbody>
    <?php
			// Step 1: Query the orders table to fetch all orders by the logged-in user
$temporary_query = "SELECT * FROM seat_reservation where status='temporary'";
$temporary_result = mysqli_query($conn, $temporary_query);

// Step 2: Check if there are any orders and fetch them
if (mysqli_num_rows($temporary_result) > 0) {
    while ($temporary = mysqli_fetch_assoc($temporary_result)) {
        $schedule_id=$temporary['schedule_id'];
        $seat_number=$temporary['seat_number'];
        $user_id=$temporary['user_id'];
        $fare=$temporary['total_fare'];
        $status=$temporary['status'];
    
        $temp_time=$temporary['temporary_booking_time'];


        //Refund amount calculation
        

            // Step 6: Output the results in the table
            ?>
            <tr>
                <td><?= htmlspecialchars($schedule_id); ?></td>
                <td><?= htmlspecialchars($seat_number); ?></td>
                <td><?= htmlspecialchars($user_id); ?></td>
                <td>Rs. <?= htmlspecialchars($fare); ?></td>
                <td><?= htmlspecialchars($status); ?></td>
                <td><?= htmlspecialchars($temp_time); ?></td>
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
