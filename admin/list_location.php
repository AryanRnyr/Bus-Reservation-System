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
$sql = "SELECT id, city, state, status FROM location";
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
    <h2>List of Locations</h2>
    <button name="add"  type="button" onclick="window.location.href='add_location.php'">Add New <i class="fa fa-plus"></i></button>

</div>
<table>
    <thead>
        <tr>
            <th style="width:5%;">No.</th>
            <th>City</th>
            <th>State</th>
            <th>Status</th>
            <th style="width:15%;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if ($result->num_rows > 0): 
            $index = 1;
            while ($row = $result->fetch_assoc()):
                // Check if location is used in schedule_list
                $locationId = $row['id'];
                $checkQuery = "SELECT COUNT(*) AS count FROM schedule_list WHERE from_location = ? OR to_location = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->bind_param("ii", $locationId, $locationId);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();

                $isDeletable = ($count == 0);
        ?>
                <tr>
                    <!-- Column 1: Serial Number -->
                    <td><?= $index++; ?></td>

                    <!-- Column 2: City -->
                    <td><?= htmlspecialchars($row["city"]); ?></td>

                    <!-- Column 3: State -->
                    <td><?= htmlspecialchars($row["state"]); ?></td>

                    <!-- Column 4: Status -->
                    <td class="<?= $row["status"] ? 'status-active' : 'status-inactive' ?>">
                        <?= $row["status"] ? 'Active' : 'Inactive' ?>
                    </td>

                    <!-- Column 5: Action -->
                    <td>
                        <a class="action-btn-edit" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['city']); ?>', '<?= htmlspecialchars($row['state']); ?>', <?= $row['status']; ?>)">Edit</a>
                        
                        <?php if ($isDeletable): ?>
                            <a href="delete_location.php?id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to delete this location?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
        <?php 
            endwhile;
        else: 
        ?>
            <tr>
                <td colspan="5">No Location found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2>Edit Location Details</h2>
        <form id="editBusForm" method="post" action="edit_location.php" onsubmit="return validateForm()">
            <input type="hidden" id="busId" name="bus_id">
            
            <label for="bus_name">City:</label>
            <input type="text" id="bus_name" name="city" required><br><br>
            
            <label for="bus_number">State:</label>
            <input type="text" id="bus_number" name="state" required><br><br>
            
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select><br><br>

            <button type="submit">Update Location</button>
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

 // Validate city and state to ensure they don't contain numeric characters
 function validateForm() {
        var city = document.getElementById("bus_name").value;
        var state = document.getElementById("bus_number").value;

        // Check if city or state contains numbers
        if (/\d/.test(city)) {
            alert("City cannot contain numeric characters.");
            return false; // Prevent form submission
        }

        if (/\d/.test(state)) {
            alert("State cannot contain numeric characters.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission if validation passes
    }
</script>
<script src="script.js"></script>
</body>
</html>
