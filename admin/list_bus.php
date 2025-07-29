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
    <h2>List of Buses</h2>
    <!-- <form action="add_bus.php" method="post" name="add"> -->
    <button name="add"  type="button" onclick="window.location.href='add_bus.php'">Add New <i class="fa fa-plus"></i></button>
<!-- </form> -->
</div>
<table>
    <thead>
        <tr>
            <th style="width:5%;">No.</th>
            <th>Bus Name</th>
            <th>Bus No</th>
            <th>Status</th>
            <th style="width:15%;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php $index = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php 
                    // Check if the bus is used in any schedule
                    $bus_id = $row["id"];
                    $check_schedule_sql = "SELECT COUNT(*) as count FROM schedule_list WHERE bus_id = $bus_id";
                    $schedule_result = $conn->query($check_schedule_sql);
                    $schedule_row = $schedule_result->fetch_assoc();
                    $has_schedule = $schedule_row['count'] > 0;
                ?>
                <tr>
                    <td><?= $index++; ?></td>
                    <td><?= htmlspecialchars($row["name"]); ?></td>
                    <td><?= htmlspecialchars($row["bus_number"]); ?></td>
                    <td class="<?= $row["status"] ? 'status-active' : 'status-inactive' ?>">
                        <?= $row["status"] ? 'Active' : 'Inactive' ?>
                    </td>
                    <td>
                        <a class="action-btn-edit" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['name']); ?>', '<?= htmlspecialchars($row['bus_number']); ?>', <?= $row['status']; ?>)">Edit</a>
                        
                        <?php if (!$has_schedule): ?>
                            <a href="delete_bus.php?id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to delete this bus?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No buses found.</td>
            </tr>
        <?php endif; ?>
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
            <input type="text" id="bus_number" name="bus_number" required disabled><br>
            <label style="color:gray; font-size: 10px;">&nbsp;&nbsp;&nbsp; Bus Number cannot be changed</label>
            
            <br><br>
            
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


function validateBusName() {
    const busName = document.getElementById("bus_name").value; // Correct the ID reference here
    
    // Check if bus name contains numbers
    const regex = /\d/; // Regular expression to check for numbers
    if (regex.test(busName)) {
        alert("Bus name should not contain numbers.");
        return false; // Prevent form submission
    }
    
    return true; // Allow form submission
}
</script>
<script src="script.js"></script>
</body>
</html>
