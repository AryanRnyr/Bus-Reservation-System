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

if(isset($_GET['id']) && isset($_GET['from']) && isset($_GET['to']) && isset($_GET['fromdate']) && isset($_GET['todate']) && isset($_GET['bus'])){
    $id=$_GET['id'];
    $from=$_GET['from'];
    $to=$_GET['to'];
    $fromdate=$_GET['fromdate'];
    $todate=$_GET['todate'];
    $bus=$_GET['bus'];

    $departure_time=$fromdate;
// Fetch all buses
$status="confirmed";
$sql = "SELECT * FROM orders where schedule_id=$id AND status='$status'";
$result = $conn->query($sql);
}else{
    echo "<script>window.history.back();</script>";
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
    <link rel="stylesheet" href="schedule_reservations.css">
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
                <li><a href="profile.php" class="profile-btn" style="margin-left: 100px;"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>                <li><a href="logout.php" class="login-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="listtop">
            <h2></h2>
            <!-- <div class="filter-options">
            <select id="sortSchedules" onchange="sortSchedules()">
        <option value="">Sort by...</option>
        <option value="departure_time">By Departure</option>
        <option value="arrival_time">By Arrival</option>
        <option value="availability">By Availability</option>
        <option value="price">By Price</option>
        <option value="status">By Status</option>
    </select>
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
</div> -->
            <button name="add" type="button" onclick="printTicket()" class="ticket-print">Print <i class="fa fa-print"></i></button>
        </div>
        <div class="header">
            <img src="../images/logo2.png" alt="RN Bus Logo" class="logo">
            <h2 class="company_name">RN Bus Pvt. Ltd.</h2>
            <div class="contact">
            <p>Travel related queries</p>
            <p>+977-9840594031</p>
            </div>
        </div>
        <h3 style="text-align:center; margin-bottom:10px;"><?= $bus; ?></h3>
        <!-- <h3 style="text-align:center; margin-bottom:10px;"><?= $from."-".date("Y-m-d    H:i", strtotime($fromdate)); ?></h3> -->
        <div class="journey-details">
            <div>
                <h3><?= $from." ".date("Y-m-d    H:i", strtotime($fromdate)); ?></h3>
            </div>
            <div>
                <h3>to</h3>
            </div>
            <div>
                <h3><?= $to." ".date("Y-m-d    H:i", strtotime($todate)); ?></h3>
            </div>
        </div>
        <h3 style="text-align:center; color:grey;">List of Passengers</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket No.</th>
                    <th>Seats</th>
                    <th>Fare</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>ID</th>
                    <th style="width:8%">Payment Method</th>
                    <th>Phone</th>
                    <!-- <th>Status</th> -->
                    <th class="hide-column">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $index = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                        <td><?= htmlspecialchars($row["ticket_no"]); ?></td>
                        <td><?= htmlspecialchars($row["seats"]); ?></td>
                        <td><?= htmlspecialchars($row["fare"]/100); ?></td>
                        <td><?= htmlspecialchars($row["name"]); ?></td>
                        <td><?= htmlspecialchars($row["age"]); ?></td>
                        <td><?= htmlspecialchars(ucfirst($row["gender"])); ?></td>
                        <td><?= htmlspecialchars($row["id_proof"]) . " (" . htmlspecialchars($row["id_details"]) . ")"; ?></td>
                        <td><?= htmlspecialchars($row["payment_method"]); ?></td>
                        <td><?= htmlspecialchars($row["phone"]); ?></td>
                        <!-- <td data-sort="status" class="<?= $row["status"] ? 'status-active' : 'status-inactive' ?>">
                            <?= $row["status"] ? 'Active' : 'Inactive' ?>
                        </td> -->
                        <td class="hide-column">
                <?php if($status=="confirmed" || $status=="Cancellation Requested"): ?>
                        <?= '<a class="action-btn-edit" style="padding: 5px 12px;" href="../search/print_ticket.php?schedule_id='.$id.'&user_id='.$row["user_id"].'&ticket_no='.$row['ticket_no'].'">Print</a>' ?>
                        <?php
						// Assuming $departure_time is in a format like 'Y-m-d H:i:s'
						$current_time = new DateTime(); // Get current date and time
						$departure_time = new DateTime($fromdate); // Convert departure_time to DateTime object

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
							<!-- <a href="approve_cancelrequest.php?ticket_number=<?= $row['ticket_no']; ?>&seats=<?= $row['seats']; ?>&schedule_id=<?= $id; ?>&user_id=<?= $row["user_id"]; ?>&order_id=<?= $row["id"]; ?>" class="action-btn-delete" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel</a> -->
                            <form id="cancelForm" action="cancel_ticket.php" method="POST">
                                <input type="hidden" value="<?= $row["ticket_no"]; ?>" name="ticket_number">
                                <input type="hidden" value="<?= $row["name"]; ?>" name="full_name">
                                <input type="hidden" value="<?= $row["schedule_id"]; ?>" name="schedule_id">
                                <input type="hidden" value="<?= $row["user_id"]; ?>" name="user_id">
                                <input type="hidden" value="<?= $row["id"]; ?>" name="order_id">
                                <input type="hidden" value="<?= $bus; ?>" name="bus_name">
                                <input type="hidden" value="<?= $row["seats"]; ?>" name="seats">
                                <input type="hidden" value="<?= $row["fare"]/100; ?>" name="fare">
                                <input type="hidden" value="<?= $row["phone"]; ?>" name="phone">
                                <input type="hidden" value="<?= $from; ?>" name="departure_city">
                                <input type="hidden" value="<?= $fromdate; ?>" name="departure_time">
                                <input type="hidden" value="<?= $to; ?>" name="arrival_city">
                                <input type="hidden" value="<?= $todate; ?>" name="arrival_time">

                                <!-- <button style="font-size: 14px; padding:4px;" name="submit" type="submit" class="action-btn-cancel" onclick="return confirm('Are you sure you want to request for cancellation?');">Request for Cancellation</button> -->
                                <button style="font-size: 14px; padding:4px;" type="submit" class="action-btn-delete" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel</button>

                            </form>
							<?php
						}
						?>

                        <?php else: ?>
                        <?php endif; ?>
                        <!-- <a class="action-btn-edit" style="padding-left: 11px; padding-right:11px;" href="../search/print_ticket.php?schedule_id=<?= $id; ?>&user_id=<?= $user_id; ?>&ticket_no=<?= $ticket_number; ?>">Print</a> -->

						





					</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No reservations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <?php
        // Close the database connection
        $conn->close();
        ?>
    </main>
    <br><br>
    <script src="script.js"></script>
    <script>
        function printTicket() {
            window.print();
        }
    </script>
    <footer class="footer">
        <h3 class="dev">Developed By</h3>
        <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
    </footer>
</body>
</html>
