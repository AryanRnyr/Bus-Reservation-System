<?php
session_start();


$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

// Check for connection errors
if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input from the form
    // if(isset($sourceCity) && isset($destinationCity) && isset($selectedDate)){
    $sourceCity = $_POST['from_location'];
    $destinationCity = $_POST['to_location'];
    $selectedDate = $_POST['departure_time'];
    // $_SESSION["source"]=$sourceCity;
    // $_SESSION["destination"]=$destinationCity;
    // $_SESSION["selectedDate"]=$selectedDate;
    $date = date('Y-m-d');

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
        l1.city = ? 
        AND l2.city = ? 
        AND DATE(s.departure_time) = ? 
        AND b.status = 1  -- Active bus
        AND s.status = 1  -- Active schedule
        AND s.availability > 0  -- Ensure availability
        AND (DATE(s.departure_time) > CURDATE() OR (DATE(s.departure_time) = CURDATE() AND TIME(s.departure_time) > CURTIME())) -- Filter for past times
    ORDER BY 
        s.departure_time
";


    if ($stmt = $con->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param('sss', $sourceCity, $destinationCity, $selectedDate);

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();
    }
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
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
       .dep-suggestions-list {

position: absolute;
top: 100%; /* Aligns the dropdown right below the input field */
left: 0;
width: 100%; /* Full width of the input */
/* border: 1px solid #ccc; */
max-height: 130px;
overflow-y: auto;
background-color: white;
z-index: 1000;
box-shadow: 0 2px 5px rgba(0,0,0,0.1);
border-radius: 2.5px;
}
.dep-suggestions-list div {
padding: 2px;
padding-left: 5px;
cursor: pointer;
background-color: #007ab8;
border: 1px solid #ccc;
color: white;
margin:4px;
border-radius: 5px;
text-align: left;
}
.dep-suggestions-list div:hover {
background-color: #01608f;
}


.arr-suggestions-list {

position: absolute;
top: 100%; /* Aligns the dropdown right below the input field */
left: 0;
width: 100%; /* Full width of the input */
/* border: 1px solid #ccc; */
max-height: 130px;
overflow-y: auto;
background-color: white;
z-index: 1000;
box-shadow: 0 2px 5px rgba(0,0,0,0.1);
border-radius: 2.5px;
}
.arr-suggestions-list div {
padding: 2px;
padding-left: 5px;
cursor: pointer;
background-color: #007ab8;
border: 1px solid #ccc;
color: white;
margin:4px;
border-radius: 5px;
text-align: left;
}
.arr-suggestions-list div:hover {
    background-color: #01608f;
}


        
        /* .bus-results {
            margin-top: 20px;
        }
        
        .res-table{
            margin-left: auto; 
            margin-right: auto; 
            width: 80%; 
            border-spacing: 0px;
        }

        td{
            padding-left: 5px;
        }

        .first-col, .sec-col{
            border-left:none;
        }*/

        .emptyresults{
    text-align: center;
    border:1px solid black;
    margin-top: 30px;
    margin-bottom: 30px;
    margin-left:10px;
    margin-right: 10px;
    background-color: #f8d7da;
    border-color: #f5c6cb;
    border-radius: .25rem;
    padding: .75rem 1.25rem;
    color: #343a40 !important;
} 

.bus-result {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #ddd;
    margin: 10px 8px;
    border-radius: 5px;
    background-color: whitesmoke;
    box-shadow: 5px 5px 5px rgb(209, 209, 209);
    
}

.bus-details {
    flex: 1;
}

.time-location {
    flex: 2;
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.departure, .arrival {
    text-align: center;
}

.duration {
    /* font-weight: bold; */
    text-align: center;
}

.time {
    font-size: 1.2em;
    font-weight: bold;
}

.location {
    font-size: 1em;
    color: gray;
}

.seats {
    flex: 1;
    text-align: center;
}

.view-seats {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.view-seats:hover {
    background-color: #0056b3;
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
            <hr><br>
            <form class="search-form" method="post">
          
            <div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" name="from_location" id="departure" placeholder="Enter Source City"
    <?php if(isset($sourceCity)):?>
        value="<?= htmlspecialchars($sourceCity, ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('departure')" oninput="fetchSuggestions('departure')" 
    onblur="validateInput('departure')">
    <div id="suggestions-departure" class="dep-suggestions-list"></div>
</div>

<div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" id="arrival" name="to_location" placeholder="Enter Destination"
    <?php if(isset($destinationCity)):?>
        value="<?= htmlspecialchars($destinationCity, ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('arrival')" oninput="fetchSuggestions('arrival')" 
    onblur="validateInput('arrival')">
    <div id="suggestions-arrival" class="arr-suggestions-list"></div>
</div>


                <input class="date" type="date" id="dateInput" name="departure_time" min="<?= $date; ?>" 
                <?php if(isset($selectedDate)):?>
                value="<?=htmlspecialchars($selectedDate, flags: ENT_QUOTES)?>" 
                <?php endif; ?>
                >
                <!-- <select name="shift">
                    <option value="both">Both</option>
                    <option value="morning">Morning</option>
                    <option value="evening">Evening</option>
                </select> -->
                <button type="submit">Search</button>
                
            </form>
            
          
       
    </main>


<!-- Results Section -->


<?php if (isset($result) && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bus-result">
            <div class="bus-details">
                <h3 class="h3"><?= htmlspecialchars($row["bus_name"]); ?></h3>
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
                <button class="view-seats" onclick="window.location.href='bus_details.php?schedule_id=<?= $row['schedule_id']; ?>&source=<?= urlencode($sourceCity); ?>&destination=<?= urlencode($destinationCity); ?>&date=<?= urlencode($selectedDate); ?>'">View Seats</button>
                

            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <br>
    <div class="emptyresults">No Route Found For Given Date. Please Try Again With Different Date.</div>
    <br>
<?php endif; ?>




<!-- <footer class="footer" style="margin-top: 17px;">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer> -->


    <!-- <script src="../js/script.js"></script> -->
    <script>
    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});

</script>
    <script src="script.js"></script>
</body>
</html>
