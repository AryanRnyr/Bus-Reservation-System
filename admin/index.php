<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}
$currentDate = date("D - M/d/Y"); // Format: Sun/May/20/2018

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include "../includes/cleanup_temp_bookings.php";
cleanupExpiredBookings($conn);

// Query for number of users with status = 1
$query_users = "SELECT COUNT(*) AS number_of_users FROM users WHERE status = 1";
$result_users = mysqli_query($conn, $query_users);
$row_users = mysqli_fetch_assoc($result_users);
$number_of_users = $row_users['number_of_users'];

// Query for number of buses
$query_buses = "SELECT COUNT(*) AS number_of_buses FROM bus";
$result_buses = mysqli_query($conn, $query_buses);
$row_buses = mysqli_fetch_assoc($result_buses);
$number_of_buses = $row_buses['number_of_buses'];

// Query for number of locations
$query_locations = "SELECT COUNT(*) AS number_of_locations FROM location";
$result_locations = mysqli_query($conn, $query_locations);
$row_locations = mysqli_fetch_assoc($result_locations);
$number_of_locations = $row_locations['number_of_locations'];

// Query for number of confirmed orders
// $query_orders = "SELECT COUNT(*) AS number_of_orders FROM orders WHERE status = 'confirmed' & status = 'Cancellation Requested";
$query_orders = "SELECT COUNT(*) AS number_of_orders FROM orders WHERE status = 'confirmed' OR status = 'Cancellation Requested'";
$result_orders = mysqli_query($conn, $query_orders);
$row_orders = mysqli_fetch_assoc($result_orders);
$number_of_orders = $row_orders['number_of_orders'];

// Query for number of schedules
$query_schedules = "SELECT COUNT(*) AS number_of_schedules FROM schedule_list";
$result_schedules = mysqli_query($conn, $query_schedules);
$row_schedules = mysqli_fetch_assoc($result_schedules);
$number_of_schedules = $row_schedules['number_of_schedules'];

// Query for number of schedules
$query_cancel = "SELECT COUNT(*) AS number_of_cancel_request FROM cancel_request";
$result_cancel = mysqli_query($conn, $query_cancel);
$row_cancel = mysqli_fetch_assoc($result_cancel);
$number_of_cancel_requests = $row_cancel['number_of_cancel_request'];

// Query for number of schedules
$query_news = "SELECT COUNT(*) AS number_of_news_request FROM news";
$result_news = mysqli_query($conn, $query_news);
$row_news = mysqli_fetch_assoc($result_news);
$number_of_news_requests = $row_news['number_of_news_request'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        html, body {
    height: 100%;  /* Ensure full page height */
    margin: 0;     /* Remove default margin */
}

body {
    display: flex;
    flex-direction: column; /* Arrange content vertically */
}

.footer {
    margin-top: auto; /* Push footer to the bottom */
    background-color: #1E1C26;  /* Dark background */
    text-align: center;
    padding: 20px;
}

.dev{
    color: #f8b600;
}

.mylink {
    text-decoration: none;
    color: #039be5;
}

.mylink:hover {
    text-decoration: underline;
}

.msg{
    margin-bottom: 50px;
}

.dashboard-container {
    display: flex; /* Use flexbox layout */
    flex-wrap: wrap; /* Allow cards to wrap to next line */
    justify-content: center; /* Center the cards horizontally */
    gap: 20px; /* Space between cards */
    padding: 20px;
}


.dashboard-card {
    background-color: white; /* White background for the card */
    color: #333; /* Dark text color for general text */
    border: 1px solid #e0e0e0; /* Light border for card */
    border-radius: 8px; /* Rounded corners for the card */
    padding-top: 10px;
    text-align: center;
    width: calc(25% - 75px); /* 4 cards per row with some space */
    box-sizing: border-box; /* Ensure padding is included in the width */
    transition: all 0.3s ease; /* Smooth transition for hover effect */
}

.dashboard-card:hover {
    background-color: #f8f8f8; /* Light grey on hover */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Shadow on hover */
}

.dashboard-card .icon {
    font-size: 40px; /* Icon size */
    margin-bottom: 10px; /* Space between icon and value */
    /* color: #f44336; */
}

.dashboard-card .value {
    font-size: 24px; /* Font size for the value */
    font-weight: bold;
    margin-bottom: 10px; /* Space between value and label */
    /* color: #f44336; */
    /* background-color: #f8f8f8;  */
    padding: 10px;
    border-radius: 5px; /* Rounded corners for value background */
    cursor: pointer;
}

.dashboard-card .label {
    font-size: 14px; /* Font size for the label */
    color: white; 
    /* background-color: #f44336;  */
    background-color: #333; 
    padding: 5px 10px;
    border-radius: 5px; /* Rounded corners for label background */
    margin-top: 5px; /* Space between value and label */
    cursor: pointer;
}

/* Color Classes */
.red {
    background-color: #f44336; /* Red background */
}

.orange {
    background-color: #ff9800; /* Orange background */
}

.green {
    background-color: #4caf50; /* Green background */
}

.blue {
    background-color: #2196f3; /* Blue background */
}
/* Optional: Adjust hover effect for individual colors */
.red:hover {
    background-color: #d32f2f;
}

.orange:hover {
    background-color: #fb8c00;
}

.green:hover {
    background-color: #388e3c;
}

.blue:hover {
    background-color: #1976d2;
}


/* neew */
/* Color Classes */
.red .icon, .red .value, .red .label {
    background-color: #f44336; /* Red background */
    color: white; /* White text */
}

.orange .icon, .orange .value, .orange .label {
    background-color: #ff9800; /* Orange background */
    color: white; /* White text */
}

.green .icon, .green .value, .green .label {
    background-color: #4caf50; /* Green background */
    color: white; /* White text */
}

.blue .icon, .blue .value, .blue .label {
    background-color: #2196f3; /* Blue background */
    color: white; /* White text */
}

/* Optional: Adjust hover effect for individual colors */
.red:hover .icon, .red:hover .value, .red:hover .label {
    background-color: #d32f2f; /* Darker red on hover */
}

.orange:hover .icon, .orange:hover .value, .orange:hover .label {
    background-color: #fb8c00; /* Darker orange on hover */
}

.green:hover .icon, .green:hover .value, .green:hover .label {
    background-color: #388e3c; /* Darker green on hover */
}

.blue:hover .icon, .blue:hover .value, .blue:hover .label {
    background-color: #1976d2;
}

/* New Color Classes */
.purple {
    background-color: #9c27b0; /* Purple background */
}

.pink {
    background-color: #e91e63; /* Pink background */
}

.yellow {
    background-color: #ffeb3b; /* Yellow background */
}

.teal {
    background-color: #009688; /* Teal background */
}

.cyan {
    background-color: #00bcd4; /* Cyan background */
}

.brown {
    background-color: #795548; /* Brown background */
}

/* Optional: Adjust hover effect for new colors */
.purple:hover {
    background-color: #7b1fa2; /* Darker purple on hover */
}

.pink:hover {
    background-color: #c2185b; /* Darker pink on hover */
}

.yellow:hover {
    background-color: #fbc02d; /* Darker yellow on hover */
}

.teal:hover {
    background-color: #00796b; /* Darker teal on hover */
}

.cyan:hover {
    background-color: #0097a7; /* Darker cyan on hover */
}

.brown:hover {
    background-color: #6d4c41; /* Darker brown on hover */
}

/* New - Color Classes for icon, value, and label */
.purple .icon, .purple .value, .purple .label {
    background-color: #9c27b0; /* Purple background */
    color: white; /* White text */
}

.pink .icon, .pink .value, .pink .label {
    background-color: #e91e63; /* Pink background */
    color: white; /* White text */
}

.yellow .icon, .yellow .value, .yellow .label {
    background-color: #ffeb3b; /* Yellow background */
    color: white; /* White text */
}

.teal .icon, .teal .value, .teal .label {
    background-color: #009688; /* Teal background */
    color: white; /* White text */
}

.cyan .icon, .cyan .value, .cyan .label {
    background-color: #00bcd4; /* Cyan background */
    color: white; /* White text */
}

.brown .icon, .brown .value, .brown .label {
    background-color: #795548; /* Brown background */
    color: white; /* White text */
}

/* Optional: Adjust hover effect for individual colors */
.purple:hover .icon, .purple:hover .value, .purple:hover .label {
    background-color: #7b1fa2; /* Darker purple on hover */
}

.pink:hover .icon, .pink:hover .value, .pink:hover .label {
    background-color: #c2185b; /* Darker pink on hover */
}

.yellow:hover .icon, .yellow:hover .value, .yellow:hover .label {
    background-color: #fbc02d; /* Darker yellow on hover */
}

.teal:hover .icon, .teal:hover .value, .teal:hover .label {
    background-color: #00796b; /* Darker teal on hover */
}

.cyan:hover .icon, .cyan:hover .value, .cyan:hover .label {
    background-color: #0097a7; /* Darker cyan on hover */
}

.brown:hover .icon, .brown:hover .value, .brown:hover .label {
    background-color: #6d4c41; /* Darker brown on hover */
}
    </style>
    
</head>
<body>
    <header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
                <li ><a href="list_reservations.php">Reservations</a></li>
                
                <li class="dropdown">
                        <a href="#">Services <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                         <ul class="dropdown-content">
                            <li><a href="list_bus.php">List Buses</a></li>
                             <li><a href="list_location.php">List Location</a></li>
                             <li><a href="list_users.php">List Users</a></li>
                        </ul>
                </li>
                <li><a class="navbreak" href="manage_schedule.php">Manage Schedule</a></li>
                <li><a class="navbreak" href="list_cancelrequest.php">Cancellation Requests</a></li>
                <!-- <li><a class="navbreak" href="list_tempbookings.php" >Temporary Bookings</a></li> -->
                <li ><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
    <div class="msg">
        <h2>Hello, <?=$_SESSION['firstname']?>!</h2>
      </div>
      <div class="dashboard-container">
    <!-- Repeat the dashboard card structure for other sections like Bookings, Buses, etc. -->
    <div class="dashboard-card blue" id="bluecard" onclick="window.location.href='list_bus.php';">
        <div class="icon">
        <i class="fa fa-bus"></i>
        </div>
        <div class="value">
        <?php echo $number_of_buses; ?>
        </div>
        <div class="label">
            Buses
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card red" onclick="window.location.href='list_reservations.php';">
        <div class="icon">
        <i class="fa-solid fa-book"></i>
        </div>
        <div class="value">
        <?php echo $number_of_orders; ?>
        </div>
        <div class="label">
            Bookings
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card green">
        <div class="icon">
        <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="value">
        <?php echo $currentDate; ?>
        </div>
        <div class="label">
            Date
        </div>
    </div>
    <!-- Add more cards here -->
     
    <div class="dashboard-card orange" onclick="window.location.href='list_users.php';">
        <div class="icon">
        <i class="fas fa-users"></i>
        </div>
        <div class="value">
        <?php echo $number_of_users; ?>
        </div>
        <div class="label">
            Accounts
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card pink" onclick="window.location.href='list_location.php';">
        <div class="icon">
        <i class="fa-solid fa-location-dot"></i>
        </div>
        <div class="value">
        <?php echo $number_of_locations; ?>
        </div>
        <div class="label">
            Locations
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card teal" onclick="window.location.href='manage_schedule.php';">
        <div class="icon">
        <i class="fas fa-calendar-check"></i>
        </div>
        <div class="value">
        <?php echo $number_of_schedules; ?>
        </div>
        <div class="label">
            Schedules
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card brown" onclick="window.location.href='list_cancelrequest.php';">
        <div class="icon">
        <i class="fas fa-times"></i>
        </div>
        <div class="value">
        <?php echo $number_of_cancel_requests; ?>
        </div>
        <div class="label">
            Cancellation Requests
        </div>
    </div>
    <!-- Add more cards here -->
    <div class="dashboard-card purple" onclick="window.location.href='list_news.php';">
        <div class="icon">
        <i class="fa fa-newspaper-o"></i>
        </div>
        <div class="value">
        <?php echo $number_of_news_requests; ?>
        </div>
        <div class="label">
            News & Updates
        </div>
    </div>
   

    </div>


    </main>

<hr><br>
     
<footer class="footer">
    <h3 class="dev">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

</body>
</html>
