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


$sql = "
    SELECT 
        s.id, 
        b.id AS bus_id,          -- Select the bus_id explicitly
        b.name AS bus_name, 
        b.bus_number, 
        l1.city AS from_location,
        l1.id AS from_locationid,
        l2.city AS to_location,
        l2.id AS to_locationid,
        s.departure_time, 
        s.eta AS arrival_time, 
        s.availability, 
        s.price, 
        s.status 
    FROM 
        schedule_list s
    JOIN 
        bus b ON s.bus_id = b.id
    JOIN 
        location l1 ON s.from_location = l1.id
    JOIN 
        location l2 ON s.to_location = l2.id
    ORDER BY 
        s.departure_time ASC
";
$result = $conn->query($sql);

$location_sql = "SELECT id, city FROM location WHERE status = 1";
$location_result = $conn->query($location_sql);
$locations = [];
while ($row = $location_result->fetch_assoc()) {
    $locations[] = $row;
}

// Fetch active buses
$bus_sql = "SELECT id, name, bus_number FROM bus WHERE status = 1";
$bus_result = $conn->query($bus_sql);
$buses = [];
while ($row = $bus_result->fetch_assoc()) {
    $buses[] = $row;
}
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
            <h2>List of Schedules</h2>
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
        <input type="radio" name="scheduleFilter" value="all" id="allSchedules"> All Schedules
    </label>
    </label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="scheduleFilter" value="past" id="pastSchedules"> Past Schedules
    </label>
    <label for="">&nbsp;&nbsp;</label>
    <label>
        <input type="radio" name="scheduleFilter" value="upcoming" id="upcomingSchedules"> Upcoming Schedules
    </label>
</div>
            <button name="add" type="button" onclick="window.location.href='add_schedule.php'">Add New <i class="fa fa-plus"></i></button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Bus Name (Bus No)</th>
                    <th>Departure Time</th>
                    <th>Arrival Time</th>
                    <th>Availability</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $index = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-sort="departure_time">
                        <td><?= $index++; ?></td>
                        <td data-sort="from_location"><?= htmlspecialchars($row["from_location"]); ?></td>
                        <td data-sort="to_location"><?= htmlspecialchars($row["to_location"]); ?></td>
                        <td data-sort="bus_name"><?= htmlspecialchars($row["bus_name"]) . " (" . htmlspecialchars($row["bus_number"]) . ")"; ?></td>
                        <td data-sort="departure_time"><?= htmlspecialchars(date("Y-m-d    H:i", strtotime($row["departure_time"]))); ?></td>
                        <td data-sort="arrival_time"><?= htmlspecialchars(date("Y-m-d    H:i", strtotime($row["arrival_time"]))); ?></td>
                        <td data-sort="availability"><?= htmlspecialchars($row["availability"]); ?></td>
                        <td data-sort="price">रू<?= htmlspecialchars($row["price"]); ?></td>
                        <td data-sort="status" class="<?= $row["status"] ? 'status-active' : 'status-inactive' ?>">
                            <?= $row["status"] ? 'Active' : 'Inactive' ?>
                        </td>
                            <td style="width:20%">
                            <a href="#" class="action-btn-edit" 
                        data-id='<?= $row["id"]; ?>' 
                        data-from='<?= $row["from_location"]; ?>'
                        data-to='<?= $row["to_location"]; ?>'
                        data-fromid='<?= $row["from_locationid"]; ?>'
                        data-toid='<?= $row["to_locationid"]; ?>'
                        data-bus='<?= $row["bus_id"]; ?>' 
                        data-departure='<?= $row["departure_time"]; ?>'
                        data-eta='<?= $row["arrival_time"]; ?>'
                        data-availability='<?= $row["availability"]; ?>'
                        data-price='<?= $row["price"]; ?>'
                        data-status='<?= $row["status"]; ?>'>Edit</a>

                                <a href="delete_schedule.php?id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Deleting this schedule will delete all the reservation details of it. Are you sure?');">Delete</a>
                                <a href="schedule_reservations.php?id=<?= $row["id"]; ?>&from=<?= $row["from_location"]; ?>&to=<?= $row["to_location"]; ?>&fromdate=<?= $row["departure_time"]; ?>&todate=<?= $row["arrival_time"]; ?>&bus=<?= $row["bus_name"]." (".$row["bus_number"].")"; ?>" class="action-btn-reservations" >Reservations</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No schedules found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditModal">&times;</span>
        <h2>Edit Schedule</h2>
        <form id="editScheduleForm" method="post" action="edit_schedule.php" style="display: flex; flex-direction: column; align-items: flex-end;">
            <input type="hidden" id="scheduleId" name="schedule_id">

            <!-- From Location -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="fromLocation" style="width: 40%;">From Location:</label>
        <select id="fromLocation" name="from_location" style="width: 60%" required>
            <option value="13">Kathmandu</option>
            <option value="15">Birgunj</option>
            <option value="16">Pokhara</option>
            <option value="19">Bhaktapur</option>
        </select>
    </div>

             <!-- To Location -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="toLocation" style="width: 40%;">To Location:</label>
        <select id="toLocation" name="to_location" style="width: 60%" required>
            <option value="13">Kathmandu</option>
            <option value="15">Birgunj</option>
            <option value="16">Pokhara</option>
            <option value="19">Bhaktapur</option>
        </select>
    </div>

    <!-- Bus -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="bus" style="width: 40%;">Bus:</label>
        <select id="bus" name="bus_id" style="width: 60%" required>
            <!-- Options will be populated from JavaScript -->
            <option value="1">RN Deluxe AC Bus (BA 1 PA 4031)</option>
            <option value="12">RN Deluxe Non-AC Bus (BA 1 PA 4030)</option>
        </select>
    </div>

    <!-- Departure Time -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="departureTime" style="width: 40%;">Departure Time:</label>
        <input type="datetime-local" id="departureTime" name="departure_time" style="width: 60%" required>
    </div>

    <!-- ETA (Estimated Time of Arrival) -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="eta" style="width: 40%;">ETA (Arrival Time):</label>
        <input type="datetime-local" id="eta" name="eta" style="width: 60%" required>
    </div>

    <!-- Availability -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 0px;">
        <label for="availability" style="width: 40%;">Availability:</label>
        <input type="number" id="availability" name="availability" min="1" style="width: 60%" required disabled>
        
    </div>
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
    <label style="width: 40%;"></label>
    <label style="color:gray; font-size: 10px;">Availability cannot be changed!</label>
    </div>
    

    <!-- Price -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 20px;">
        <label for="price" style="width: 40%;">Price:</label>
        <input type="number" id="price" name="price" min="0" style="width: 60%" required>
    </div>

    <!-- Status -->
    <div style="display: flex; justify-content: flex-start; width: 100%; margin-bottom: 10px;">
        <label for="status" style="width: 40%;">Status:</label>
        <select id="status" name="status" style="width: 60%" required>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <button type="submit" style="width: 60%; margin-top: 10px;">Update Schedule</button>
        </form>
    </div>
</div>


        <?php
        // Close the database connection
        $conn->close();
        ?>
    </main>
    <br><br>
    <script src="script.js"></script>
    <script>
     document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.action-btn-edit');
    
    if (editButtons.length > 0) {
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const fromLocationId = this.getAttribute('data-fromid');
                const toLocationId = this.getAttribute('data-toid');
                const busId = this.getAttribute('data-bus');
                const departureTime = this.getAttribute('data-departure');
                const eta = this.getAttribute('data-eta');
                const availability = this.getAttribute('data-availability');
                const price = this.getAttribute('data-price');
                const status = this.getAttribute('data-status');
                const scheduleId = this.getAttribute('data-id');
                
                // console.log("Data from clicked button: ");
                // console.log("From Location ID:", fromLocationId);
                // console.log("To Location ID:", toLocationId);
                // console.log("Bus ID:", busId);

                // Set the values in the modal form
                document.getElementById('scheduleId').value = scheduleId;
                document.getElementById('departureTime').value = departureTime;
                document.getElementById('eta').value = eta;
                document.getElementById('availability').value = availability;
                document.getElementById('price').value = price;
                document.getElementById('status').value = status;

                // Populate the location and bus select dropdowns before setting values
                populateDropdowns();

                // Set the selected values for location and bus
                setSelectValue('fromLocation', fromLocationId);
                setSelectValue('toLocation', toLocationId);
                setSelectValue('bus', busId);
                
                // Open the modal
                document.getElementById('editScheduleModal').style.display = 'block';
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Get the current time
    const currentTime = new Date();

    // Get all schedule rows
    const scheduleRows = document.querySelectorAll('table tbody tr');

    // Function to filter schedules based on departure time
    function filterSchedules() {
        const selectedFilter = document.querySelector('input[name="scheduleFilter"]:checked');
        if (!selectedFilter) return; // No filter selected, show all

        const filterValue = selectedFilter.value;

        scheduleRows.forEach(row => {
            const departureTimeCell = row.querySelector('td:nth-child(5)'); // Departure time column (adjust if necessary)
            const departureTime = new Date(departureTimeCell.textContent);

            // Show or hide the row based on the filter
            if (filterValue === 'all') {
                row.style.display = '';  // Show all schedules
            } else if (filterValue === 'past' && departureTime < currentTime) {
                row.style.display = '';  // Show past schedules
            } else if (filterValue === 'upcoming' && departureTime >= currentTime) {
                row.style.display = '';  // Show upcoming schedules
            } else {
                row.style.display = 'none';  // Hide the schedule if it doesn't match the filter
            }
        });
    }

    // Set the initial state of schedules (show all by default)
    filterSchedules();

    // Add event listener for radio buttons to apply filtering
    const filterRadios = document.querySelectorAll('input[name="scheduleFilter"]');
    filterRadios.forEach(radio => {
        radio.addEventListener('change', filterSchedules);
    });
});

function setSelectValue(selectId, value) {
    const select = document.getElementById(selectId);
    if (select) {
        // console.log(`Setting value for ${selectId}: ${value}`);
        // Loop through the options to match the correct one
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value == value) {
                select.selectedIndex = i; // Set the correct index
                break;
            }
        }
    }
}

// Close the modal when clicking on the close button
document.getElementById('closeEditModal').addEventListener('click', function() {
    document.getElementById('editScheduleModal').style.display = 'none';
});

// Close modal if clicked outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById("editScheduleModal")) {
        document.getElementById("editScheduleModal").style.display = "none";
    }
};

// Populate dropdowns for locations and buses
const locations = <?php echo json_encode($locations); ?>;
const buses = <?php echo json_encode($buses); ?>;
const fromLocationSelect = document.getElementById("fromLocation");
const toLocationSelect = document.getElementById("toLocation");
const busSelect = document.getElementById("bus");

function populateDropdowns() {
    // Clear existing options
    fromLocationSelect.innerHTML = '';
    toLocationSelect.innerHTML = '';

    // Populate the location select options
    locations.forEach(location => {
        // console.log(`Adding location: ${location.city} (ID: ${location.id})`);
        const option = new Option(location.city, location.id);
        fromLocationSelect.add(option.cloneNode(true));
        toLocationSelect.add(option);
    });

    // Populate the bus select options
    buses.forEach(bus => {
        // console.log(`Adding bus: ${bus.name} (ID: ${bus.id})`);
        const option = new Option(`${bus.name} (${bus.bus_number})`, bus.id);
        busSelect.add(option);
    });
}


function sortSchedules() {
    const sortValue = document.getElementById('sortSchedules').value;
    const scheduleRows = Array.from(document.querySelectorAll('table tbody tr'));

    // Function to compare values based on the sort criterion
    function compareSchedules(a, b) {
        let valA = a.querySelector(`td[data-sort="${sortValue}"]`).textContent;
        let valB = b.querySelector(`td[data-sort="${sortValue}"]`).textContent;

        // Convert date and time fields to Date objects for comparison
        if (sortValue === 'departure_time' || sortValue === 'arrival_time') {
            valA = new Date(valA);
            valB = new Date(valB);
        } else if (sortValue === 'price' || sortValue === 'availability') {
            valA = parseFloat(valA.replace('रू', '').trim());
            valB = parseFloat(valB.replace('रू', '').trim());
        }

        if (valA > valB) return 1;
        if (valA < valB) return -1;
        return 0;
    }

    // Sort the rows based on the selected criterion
    scheduleRows.sort(compareSchedules);

    // Reorder the rows in the table
    const tbody = document.querySelector('table tbody');
    tbody.innerHTML = ''; // Clear existing rows
    scheduleRows.forEach(row => tbody.appendChild(row)); // Append sorted rows
}

    </script>
    <footer class="footer">
        <h3 class="dev">Developed By</h3>
        <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
    </footer>
</body>
</html>
