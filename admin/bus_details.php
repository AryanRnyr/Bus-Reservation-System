<?php
session_start();
if (!isset($_SESSION['loggedin']) && !$_SESSION['loggedin'] === true) {
     header('Location: ../login/');
}elseif(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    if($_SESSION["s_role"]=="customer"){
        header("Location:../home/");
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
    $_SESSION["source"]=$sourceCity;
    $_SESSION["destination"]=$destinationCity;
    $_SESSION["selecteddate"]=$selectedDate;
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

unset($_SESSION["bus_name"]);
unset($_SESSION["price"]);
unset($_SESSION["departure_time"]);
unset($_SESSION["eta"]);
unset($_SESSION["availability"]);


$_SESSION["bus_name"]=$row["bus_name"];
$_SESSION["price"]=$row["price"];
$_SESSION["departure_time"]=$row["departure_time"];
$_SESSION["eta"]=$row["eta"];
$_SESSION["availability"]=$row["availability"];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bus_details.css">
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        .next_button {
            margin: 0 5px;
            text-decoration: none;
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
            padding: 5px 10px;
            border-radius: 0.25rem;
            font-size: 18px;
        }

        .next_button:hover{
            background-color: #117483;
            cursor: pointer;
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
                <li><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="search-section" style="margin-top: 0px;">
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
            
<div class="bus_details">

        <div class="seat-layout">
        <h2 class="seatlay">Seat Layout</h2>
    <div class="row-driver">
        <div class="seat-gap1"></div>
        <div class="seat-gap"></div>
        <div class="seat-gap"></div>
        <!-- <div class="seat-gap"></div> -->
        <div class="driver"><img src="../images/ss.svg"></div>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 1; $seatNumber <= 2; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 3; $seatNumber <= 4; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>

    <div class="row">
    <?php
    for ($seatNumber = 5; $seatNumber <= 6; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 7; $seatNumber <= 8; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>

    <div class="row">
    <?php
    for ($seatNumber = 9; $seatNumber <= 10; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 11; $seatNumber <= 12; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>

    <div class="row">
    <?php
    for ($seatNumber = 13; $seatNumber <= 14; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 15; $seatNumber <= 16; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 17; $seatNumber <= 18; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 19; $seatNumber <= 20; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 21; $seatNumber <= 22; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 23; $seatNumber <= 24; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 25; $seatNumber <= 26; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 27; $seatNumber <= 28; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 29; $seatNumber <= 30; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 31; $seatNumber <= 32; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 33; $seatNumber <= 34; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 35; $seatNumber <= 36; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 37; $seatNumber <= 38; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    echo "<div class='seat-gap'></div>";
    for ($seatNumber = 39; $seatNumber <= 40; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
    ?>
    </div>
    <div class="row">
    <?php
    for ($seatNumber = 41; $seatNumber <= 42; $seatNumber++) { 
    $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
    echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
    }
    for ($seatNumber = 43; $seatNumber <= 43; $seatNumber++) { 
        $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
        echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
        }
        for ($seatNumber = 44; $seatNumber <= 45; $seatNumber++) { 
            $isBooked = in_array($seatNumber, $bookedSeats) ? 'booked' : '';
            echo "<div class='seat $isBooked' id='seat-$seatNumber'>$seatNumber</div>";
            }
    ?>
    </div>




    </div>
    <div class="color-index">
    <p><span class="grey"></span> Booked</p>
    <p><span class="blue"></span> Available</p>
    <p><span class="orange"></span> Selected</p>
</div>
    </div>
    <div class="usr_details">
    <form action="process_seat_selection.php" method="POST">
    <!-- <form action="bus_detail_page.php" method="POST"> -->
<input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
<input type="hidden" name="user_id" value="<?= $_SESSION['id']; ?>">

<label for="selected_seats">Seat(s):</label>
<input type="text" id="selected_seats" name="selected_seats" readonly>
<br><br>
<label for="total_fare">Total Fare:</label>
<input type="text" id="total_fare" name="total_fare" readonly>
<br><br>
<?php
if (isset($_SESSION['validate_msg'])) {
    echo "<li style='list-style: none; color:red; '>".$_SESSION['validate_msg']."</li>"; // Display the alert message
    unset($_SESSION['validate_msg']); // Clear the session message after displaying
}
?>

<br>
<button type="submit" id="confirm_seats" class="next_button">Next</button>
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

   
    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});
</script>


    <script src="script.js"></script>
</body>
</html>
