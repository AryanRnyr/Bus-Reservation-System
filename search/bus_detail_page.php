<?php
session_start();
if (!isset($_SESSION['loggedin']) && !$_SESSION['loggedin'] === true) {
     header('Location: ../login/');
}elseif(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    if($_SESSION["s_role"]=="admin"){
        header("Location:../admin/");
    }
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

// Try and connect using the info above.
$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

// Check for connection errors
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
if (isset($_GET['source']) && isset($_GET['destination']) && isset($_GET['date'])) {
    $sourceCity = $_GET['source'];
    $destinationCity = $_GET['destination'];
    $selectedDate = $_GET['date'];
} else {
    // If not set, fallback values or error handling can go here
    $sourceCity = $destinationCity = $selectedDate = '';
}

// Get the schedule_id from URL
if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];
} else {
    echo "Schedule ID not found.";
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    echo "User ID not found.";
    exit;
}

$checkExpirationQuery = "SELECT * FROM seat_reservation WHERE schedule_id = ? AND user_id = ? AND temporary = 1";
$stmt = $conn->prepare($checkExpirationQuery);
$stmt->bind_param("ii", $schedule_id, $user_id);
$stmt->execute();
$reservationResult = $stmt->get_result();

while ($reservationRow = $reservationResult->fetch_assoc()) {
    if ($reservationRow['expiration_time'] < time()) {
        // Booking expired, handle accordingly
        echo "The booking has expired. Please try again.";
        exit; // Stop further processing
    }
}


// Fetch booked seats for the selected schedule
$bookedSeats = [];
$seatQuery = "SELECT seat_number FROM seat_reservation WHERE schedule_id = ?";
$seatStmt = $conn->prepare($seatQuery);
$seatStmt->bind_param("i", $schedule_id);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();
while ($seatRow = $seatResult->fetch_assoc()) {
    $bookedSeats[] = $seatRow['seat_number'];
}
$seatStmt->close();



// Query to fetch schedule and bus details
$sql = "
    SELECT 
        s.id AS schedule_id,
        b.name AS bus_name,
        b.bus_number,
        l1.city AS from_city,
        l2.city AS to_city,
        s.departure_time,
        s.eta,
        s.availability,
        s.price
    FROM 
        schedule_list s
    JOIN 
        bus b ON s.bus_id = b.id
    JOIN 
        location l1 ON s.from_location = l1.id
    JOIN 
        location l2 ON s.to_location = l2.id
    WHERE 
        s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// If no data found, exit
if ($row === null) {
    echo "No schedule found for the selected ID.";
    exit;
}


function convert12To24HourFormat($time) {
    $time = strtotime($time);
    return date('H:i', $time);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bus_details.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<div>
    <header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
    <li><a href="../home/contactus.php">Contact</a></li>
    <li><a href="../home/aboutus.php">About Us</a></li>
    <li><a href="../policies/">Policies</a></li>
    <li><a href="../home/profile.php" class="profile-btn"><i class="fas fa-user-circle"></i> <?php echo $_SESSION["firstname"]; ?></a></li>
    <li><a href="../home/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
<?php else: ?>
    <li><a href="../login/" class="login-btn">Login</a></li>
<?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="search-section">
            <!-- <h1>Namaste! Tell us where you want to Travel</h1><br> -->
            <!-- <hr><br> -->
            <form class="search-form" method="get" action="index.php">
          
            <div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" name="source" id="departure" placeholder="Enter Source City"
    <?php if(isset($sourceCity)):?>
        value="<?= htmlspecialchars($sourceCity, ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('departure')" oninput="fetchSuggestions('departure')" 
    onblur="validateInput('departure')">
    <div id="suggestions-departure" class="dep-suggestions-list"></div>
</div>

<div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" id="arrival" name="destination" placeholder="Enter Destination"
    <?php if(isset($destinationCity)):?>
        value="<?= htmlspecialchars($destinationCity, ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('arrival')" oninput="fetchSuggestions('arrival')" 
    onblur="validateInput('arrival')">
    <div id="suggestions-arrival" class="arr-suggestions-list"></div>
</div>

                <input class="date" type="date" id="dateInput" name="date" 
                <?php if(isset($selectedDate)):?>
                value="<?=htmlspecialchars($selectedDate, flags: ENT_QUOTES)?>" 
                <?php endif; ?>
                >
                <button type="submit">Search</button>
                
            </form>
            
          
       
    </main>


<!-- Results Section -->


<?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="bus-result">
            <div class="bus-details">
                <h3><?= htmlspecialchars($row["bus_name"]); ?></h3>
                <!-- <p>Tourist Sofa Seater</p> -->
                <!-- <p>NPR <?= number_format($row["price"]); ?></p> -->
            </div>
            <div class="time-location">
                <div class="departure">
                    <p class="time"><?= date("h:i A", strtotime($row["departure_time"])); ?></p>
                    <p class="location"><?= htmlspecialchars($row["from_city"]); ?></p>
                </div>

                <?php
                    // Create DateTime objects for departure and arrival times
                    $departure_time = new DateTime($row["departure_time"]);
                    $arrival_time = new DateTime($row["eta"]);

                    // Calculate the difference between departure and arrival times
                    $interval = $departure_time->diff($arrival_time);

                    // Format the duration as hours and minutes
                    $duration = $interval->format('%h Hours %i Minutes');
                ?>

                <div class="duration">
                    <p>--------- <?= $duration; ?> ---------</p>
                </div>

                <div class="arrival">
                    <p class="time"><?= date("h:i A", strtotime($row["eta"])); ?></p>
                    <p class="location"><?= htmlspecialchars($row["to_city"]); ?></p>
                </div>
            </div>
            <div class="seats">
                <p class="price">Per Seat from</p>
                <p class="price"><strong>NPR <?= number_format($row["price"]); ?></strong></p>
                <p><br></p>
                <p><?= $row["availability"]; ?> Seats Available</p>
            </div>
            <div>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
            </div>
        </div>
        <div class="container">
    <div class="usr_details">
    <form action="process_seat_selection.php" method="POST">
<input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
<div id="timer" style="font-size: 18px; color: red; font-weight: bold;">
    <!-- Timer will be displayed here -->
</div>

<label for="selected_seats">Seat(s):</label>
<input type="text" id="selected_seats" name="selected_seats" readonly>
<br><br>
<label for="total_fare">Total Fare:</label>
<input type="text" id="total_fare" name="total_fare" readonly>
<br><br>

<label>Booking For:</label>&nbsp;
<input type="radio" name="bookingfor" value="myself" id="myself">
<label for="myself">Myself</label>&nbsp;&nbsp;
<input type="radio" name="bookingfor" value="others" id="others">
<label for="others">Others</label><br><br>

<label for="fullname">Name:</label>
<input type="text" name="fullname" id="fullname"><br><br>

<label for="age">Age:</label>
<input type="number" name="age"><br><br>

<label for="gender">Gender:</label>
<select name="gender">
  <option value="male">Male</option>
  <option value="female">Female</option>
</select><br><br>

<label for="idproof">ID Proof:</label>
<select name="idproof">
    <option value="Driving License">Driving License</option>
	<option value="Citizenship">Citizenship</option>
	<option value="Passport">Passport</option>
	<option value="Aadhar Card">Aadhar Card</option>
	<option value="Voters ID">Voters ID</option>
	<option value="School ID">School ID</option>
	<option value="Birth Certificate">Birth Certificate</option>
	<option value="Govt. ID">Govt. ID</option>
</select><br><br>

<label for="iddetails">ID Details:</label>
<input type="text" name="iddetails"><br><br>

<hr style="width: auto;"><br>
<label>Payment Method:</label>&nbsp;
<input type="radio" name="paymentmethod" value="esewa">
<label for="esewa">E-sewa</label>&nbsp;&nbsp;
<input type="radio" name="paymentmethod" value="khalti">
<label for="khalti">Khalti</label><br><br>

<br>
<button type="submit" id="confirm_seats">Confirm</button>
</form>
</div>
<?php else: ?>
    <div class="emptyresults">No Route Found For Given Date. Please Try Again With Different Date.</div>
<?php endif; ?>


</div>

<footer class="footer">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

<script>
    let selectedSeats = [];

    // Assuming the price per seat is passed as a PHP variable
    const pricePerSeat = <?php echo $row['price']; ?>;

    // Function to update total fare
    function updateTotalFare() {
        // Calculate the total fare by multiplying the number of selected seats with the price per seat
        const totalFare = selectedSeats.length * pricePerSeat;
        // Update the total fare display
        document.getElementById('total_fare').value = totalFare;
    }

    // Function to toggle seat selection
    function toggleSeatSelection(seatNumber) {
        const seatElement = document.getElementById(`seat-${seatNumber}`);
        if (seatElement.classList.contains('selected')) {
            // Deselect the seat
            seatElement.classList.remove('selected');
            selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
        } else {
            // Select the seat
            seatElement.classList.add('selected');
            selectedSeats.push(seatNumber);
        }
        // Update the input field with selected seats
        document.getElementById('selected_seats').value = selectedSeats.join(', ');
        // Update the total fare
        updateTotalFare();
    }

    // Loop through each seat element
    document.querySelectorAll('.seat').forEach(seat => {
        const seatNumber = parseInt(seat.id.split('-')[1]); // Extract seat number from ID

        // Check if the seat is booked
        if (seat.classList.contains('booked')) {
            seat.style.pointerEvents = 'none'; // Disable click for booked seats
        } else {
            // Add click event for selectable seats
            seat.addEventListener('click', function () {
                toggleSeatSelection(seatNumber);
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
    // Function to fill the name field with session values when "Myself" is selected
    function fillNameForMyself() {
        const firstname = "<?php echo $_SESSION['firstname']; ?>";
        const lastname = "<?php echo $_SESSION['lastname']; ?>";
        const fullname = firstname + " " + lastname;

        const nameInput = document.getElementById('fullname');
        nameInput.value = fullname;  // Set the value to the full name
        nameInput.disabled = true;   // Disable the input field to prevent changes
    }

    // Function to enable the name input when "Others" is selected
    function enableNameInput() {
        const nameInput = document.getElementById('fullname');
        nameInput.disabled = false;  // Enable the input field so the user can enter a name
    }

    // Attach event listeners to the radio buttons after the page has loaded
    document.getElementById('myself').addEventListener('change', fillNameForMyself);
    document.getElementById('others').addEventListener('change', enableNameInput);
});

// JavaScript for handling the countdown timer for the temporary booking

let expirationTime = <?php echo $row['expiration_time']; ?>; // Get expiration time from PHP
let currentTime = Math.floor(Date.now() / 1000); // Get current time in seconds

// Calculate remaining time in seconds
let remainingTime = expirationTime - currentTime;

if (remainingTime > 0) {
    // Start the countdown timer
    let timerInterval = setInterval(function() {
        remainingTime--;
        
        if (remainingTime <= 0) {
            // If the time has expired, clear the interval and notify the user
            clearInterval(timerInterval);
            document.getElementById('timer').innerHTML = "Booking expired!";
            // Optionally, change the status of the reservation to 'expired' in the database
        } else {
            // Update the timer display
            let minutes = Math.floor(remainingTime / 60);
            let seconds = remainingTime % 60;
            document.getElementById('timer').innerHTML = `Time left: ${minutes}m ${seconds}s`;
        }
    }, 1000);
} else {
    // If the time is already expired, notify the user
    document.getElementById('timer').innerHTML = "Booking expired!";
}

</script>


    <script src="script.js"></script>
</body>
</html>
