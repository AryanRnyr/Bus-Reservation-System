<?php
session_start();

// Check if the user is logged in and not an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION["s_role"] == "admin") {
    header("Location:../admin/");
    exit();
}

// if (isset($_SESSION['transaction_msg'])) {
//     echo $_SESSION['transaction_msg']; // Display the alert message
//     unset($_SESSION['transaction_msg']); // Clear the session message after displaying
// }

// Database connection
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Get schedule_id and user_id from the URL
if (!isset($_GET['schedule_id']) || !isset($_GET['user_id'])) {
    echo "Invalid data. Please try again.";
    exit();
}

$schedule_id = $_GET['schedule_id'];
$user_id = $_GET['user_id'];
unset($_SESSION['schedule_id']);
unset($_SESSION['user_id']);
$_SESSION["schedule_id"]=$schedule_id;
$_SESSION["user_id"]=$user_id;
$temporary_booking_time=$_SESSION['temporary_booking_time'];

$ticketNumber = $user_id.$schedule_id.time();  // Ensures a unique ticket ID based on user and time.

unset($_SESSION['ticket_no']);
$_SESSION["ticket_no"]=$ticketNumber;
// Fetch booked seats for the selected schedule

// Get the temporary reservation details
$query = "SELECT * FROM seat_reservation WHERE schedule_id = '$schedule_id' AND user_id = '$user_id' AND status = 'temporary'";
$result = mysqli_query($conn, $query);

$reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);
if ($reservations) {
    // Display seat numbers for confirmation
    $seat_numbers = array_map(function($reservation) {
        return $reservation['seat_number'];
    }, $reservations);
    $seat_numbers_str = implode(', ', $seat_numbers);
    $_SESSION['selected_seats']=$seat_numbers_str;

    $total_fare_numbers=array_map(function($reservation) {
        return $reservation['total_fare'];
    }, $reservations);
    $total_fare_numbers_str = implode(', ', $total_fare_numbers);

    $reservation_id = array_map(function($reservation) {
        return $reservation['id'];
    }, $reservations);
    $reservation_id_str = implode(', ', $reservation_id);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bus Booking System</title>
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="bus_details.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/enc-base64.min.js"></script>
    <style>
        /* Center the container */
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: auto;
    width: 100%;
}

form#confirmForm {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    margin: 0 auto; /* Center the form in the container */
    border-bottom: 0px solid #ccc;

}

form#esewaform {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px 0;
    /* border: 1px solid #ccc; */
    border-radius: 8px;
    background-color: #f9f9f9;

    margin: 0 auto; /* Center the form in the container */
}
form#esewaform:hover{
    background-color: #e6e6e6;
}
form#khaltiform:hover{
    background-color: #e6e6e6;
}

form#khaltiform {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px 0;
    /* border: 1px solid #ccc; */
    border-radius: 8px;
    background-color: #f9f9f9;
    margin: 0 auto; /* Center the form in the container */
}

form#confirmForm label {
    text-align: left;
    margin-bottom: 5px;
    font-weight: bold;
    width: 90%;
    max-width: 100%; 
    margin-left: 60px;
    margin-right: 60px;
}


form#confirmForm select,
form#confirmForm input[type="text"],
form#confirmForm button {
    padding: 8px;
    margin-bottom: 10px;
    box-sizing: border-box;
    padding-left: 20px;
    padding-right: 20px;
    margin-left: 60px;
    margin-right: 60px;
    
}

form#confirmForm input[type="number"]{
    padding: 8px;
    margin-bottom: 10px;
    box-sizing: border-box;
    padding-left: 20px;
    padding-right: 20px;
    margin-left: 60px;
    margin-right: 60px;
    
}

#timer {
    font-size: 18px;
    font-weight: bold;
    color: #ff0000;
    margin-bottom: 10px;
    margin-top: 10px;
}

form#cancelForm {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
}

form#cancelForm {
    display: flex;
    justify-content: center;
    /* margin-top: 10px; */
}

form#cancelForm button {
    width: 60%;
    padding: 8px;
    margin-bottom: 10px;
    margin: 0 auto;
    box-sizing: border-box;

    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545; 
    border-radius: 0.25rem;
}

form#cancelForm button:hover {
    background-color: #94212d;
    cursor: pointer;
}

.payment_container{
    display: flex; 
    justify-content: center; 
    gap: 20px; 
    border: 1px solid #ccc; 
    border-radius: 8px; 
    background-color: #f9f9f9; 
    width:40%; 
    border-top: 0px solid #ccc; 
    border-bottom: 0px solid #ccc; 
    border-radius:0px;
}

@media (max-width:768px){
    h3{
        text-align: center;
    }

    .container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    /* height: 100%; */
    width: 100%;
}
.payment_container{
    display: flex; 
    justify-content: center; 
    gap: 20px; 
    border: 1px solid #ccc; 
    border-radius: 8px; 
    background-color: #f9f9f9; 
    width:90%; 
    border-top: 0px solid #ccc; 
    border-bottom: 0px solid #ccc; 
    border-radius:0px;
}


form#confirmForm {
    display: flex;
    flex-direction: column;
    width: 90%;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    margin: 0 auto; /* Center the form in the container */
    border-bottom: 0px solid #ccc;

}

form#esewaform {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px 0;
    /* border: 1px solid #ccc; */
    border-radius: 8px;
    background-color: #f9f9f9;

    margin: 0 auto; /* Center the form in the container */
}
form#esewaform:hover{
    background-color: #e6e6e6;
}
form#khaltiform:hover{
    background-color: #e6e6e6;
}

form#khaltiform {
    display: flex;
    flex-direction: column;
    width: 40%;
    padding: 20px 0;
    /* border: 1px solid #ccc; */
    border-radius: 8px;
    background-color: #f9f9f9;
    margin: 0 auto; /* Center the form in the container */
}



form#confirmForm select,
form#confirmForm input[type="text"],
form#confirmForm button {
    padding: 8px;
    margin-bottom: 10px;
    box-sizing: border-box;
    padding-left: 20px;
    padding-right: 20px;
    margin-left: 60px;
    margin-right: 60px;
    
}

form#confirmForm input[type="number"]{
    padding: 8px;
    margin-bottom: 10px;
    box-sizing: border-box;
    padding-left: 20px;
    padding-right: 20px;
    margin-left: 60px;
    margin-right: 60px;
    
}

#timer {
    font-size: 18px;
    font-weight: bold;
    color: #ff0000;
    margin-bottom: 10px;
    margin-top: 10px;
}

form#cancelForm {
    display: flex;
    flex-direction: column;
    width: 90%;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
}

form#cancelForm {
    display: flex;
    justify-content: center;
    /* margin-top: 10px; */
}

form#cancelForm button {
    width: 60%;
    padding: 8px;
    margin-bottom: 10px;
    margin: 0 auto;
    box-sizing: border-box;

    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545; 
    border-radius: 0.25rem;
}

form#cancelForm button:hover {
    background-color: #94212d;
    cursor: pointer;
}

.search-form{
        display: none;
    }


}

    </style>
</head>

    <body>
    <header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <button class="hamburger" aria-label="Toggle menu">
            ☰
        </button>
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
    <?php if(isset($_SESSION["source"])):?>
        value="<?= htmlspecialchars($_SESSION["source"], ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('departure')" oninput="fetchSuggestions('departure')" 
    onblur="validateInput('departure')">
    <div id="suggestions-departure" class="dep-suggestions-list"></div>
</div>

<div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" id="arrival" name="destination" placeholder="Enter Destination"
    <?php if(isset($_SESSION["destination"])):?>
        value="<?= htmlspecialchars($_SESSION["destination"], ENT_QUOTES) ?>"
    <?php endif; ?>
    onfocus="showSuggestions('arrival')" oninput="fetchSuggestions('arrival')" 
    onblur="validateInput('arrival')">
    <div id="suggestions-arrival" class="arr-suggestions-list"></div>
</div>

                <input class="date" type="date" id="dateInput" name="date" 
                <?php if(isset($_SESSION["selecteddate"])):?>
                value="<?=htmlspecialchars($_SESSION["selecteddate"], flags: ENT_QUOTES)?>" 
                <?php endif; ?>
                >
                <button type="submit">Search</button>
                
            </form>
    </main>

    <?php if (isset($_SESSION["bus_name"])): ?>
        <div class="bus-result">
            <div class="bus-details">
                <h3><?= htmlspecialchars($_SESSION["bus_name"]); ?></h3>
                <!-- <p>Tourist Sofa Seater</p> -->
                <!-- <p>NPR <?= number_format($_SESSION["price"]); ?></p> -->
            </div>
            <div class="time-location">
                <div class="departure">
                    <p class="time"><?= date("h:i A", strtotime($_SESSION["departure_time"])); ?></p>
                    <p class="location"><?= htmlspecialchars($_SESSION["source"]); ?></p>
                </div>

                <?php
                    // Create DateTime objects for departure and arrival times
                    $departure_time = new DateTime($_SESSION["departure_time"]);
                    $arrival_time = new DateTime($_SESSION["eta"]);

                    // Calculate the difference between departure and arrival times
                    $interval = $departure_time->diff($arrival_time);

                    // Format the duration as hours and minutes
                    $duration = $interval->format('%h Hours %i Minutes');
                ?>

                <div class="duration">
                    <p>--------- <?= $duration; ?> ---------</p>
                </div>

                <div class="arrival">
                    <p class="time"><?= date("h:i A", strtotime($_SESSION["eta"])); ?></p>
                    <p class="location"><?= htmlspecialchars($_SESSION["destination"]); ?></p>
                </div>
            </div>
            <div class="seats">
                <p class="price">Per Seat from</p>
                <p class="price"><strong>NPR <?= number_format($_SESSION["price"]); ?></strong></p>
                <p><br></p>
            </div>
            <div>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
            </div>
        </div>
        <?php else: ?>
    <div class="emptyresults">No Route Found For Given Date. Please Try Again With Different Date.</div>
<?php endif; ?>


<div class="container">
    <!-- <div id="timer">Time remaining: 5:00</div> -->
    <div id="timer">Time remaining: <span id="timeDisplay">5:00</span></div>
    <!-- <form action="complete_booking.php" method="POST" id="confirmForm"> -->
    <form method="POST" id="confirmForm" style="padding-bottom: 5px; border-bottom-left-radius:0px; border-bottom-right-radius: 0px;">

    <label for="selected_seats" style="margin-top: 20px;">Seat(s):</label>
    <input type="text" id="selected_seats" name="selected_seats" value="<?= $seat_numbers_str; ?>" readonly>
    
    <label for="total_fare">Total Fare:</label>
    <input type="text" id="total_fare" name="total_fare" value="<?= $total_fare_numbers[0]; ?>" readonly>
        <input type="hidden" name="seat_numbers" value="<?= $seat_numbers_str; ?>">

        <label for="name">Full Name:</label>
        <input type="text" id="name" name="full_name" required>

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" required>

        <label for="gender">Gender:</label>
        <select name="gender"  id="gender" required>
            <option value="" selected disabled>Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>

        <label for="id_proof">ID Proof:</label>
        <select name="id_proof" id="id_proof" required>
            <option value="" selected disabled>Select ID Proof</option>
            <option value="Driving License">Driving License</option>
            <option value="National Identity Card">National Identity Card</option>
            <option value="Citizenship">Citizenship</option>
            <option value="Passport">Passport</option>
            <option value="Voters ID">Voters ID</option>
            <option value="Birth Certificate">Birth Certificate</option>
            <option value="Govt. ID">Govt. ID</option>
        </select>

        <label for="id_details">ID Details:</label>
        <input type="text" id="id_details" name="id_details" required>


        <?php
if (isset($_SESSION['validate_msg'])) {
    echo "<p style='list-style: none; color:red; text-align:center;'>".$_SESSION['validate_msg']."</p>"."<br>"; // Display the alert message
    unset($_SESSION['validate_msg']); // Clear the session message after displaying
}
?>

        <label for="payment_method">Payment Method:</label>
        <!-- <input type="text" id="payment_method" name="payment_method" required><br><br> -->
 
        <!-- <button type="submit" name="confirm">Confirm Booking</button><br><br> -->
        <!-- <button type="submit" name="confirm">Confirm Booking for Seats: <?= $seat_numbers_str; ?></button><br><br> -->

       
    </form>
    

        
        

    <div class="payment_container">
        <form action="payment-request.php" method="POST" class="khaltiform" id="khaltiform" style="width:max-content">
        <input type="hidden" id="inputSeats" name="inputSeats" value="<?= $seat_numbers_str; ?>" required>
        <input type="hidden" id="inputName" name="inputName" required>
        <input type="hidden" id="inputEmail" name="inputEmail" value="<?= $_SESSION['email'];?>" required>
        <input type="hidden" id="inputPhone" name="inputPhone" value="<?= $_SESSION['phoneno'];?>" required>
        <input type="hidden" id="agehidden" name="age" required>
        <input type="hidden" id="genderhidden" name="gender" required>
        <input type="hidden" id="idproofhidden" name="id_proof" required>
        <input type="hidden" id="iddetailshidden" name="id_details" required>
        <input type="hidden" id="paymentmethod" name="payment_method" value="Khalti" required>


            <input id="inputAmount" name="inputAmount" value="<?= $total_fare_numbers[0]; ?>" type="hidden" required>
            <input id="purchase_order_id" name="purchase_order_id" value="<?= $ticketNumber; ?>" type="hidden" required>
            <input id="purchase_order_name" name="purchase_order_name" value ="<?= 'Bus'.$user_id.$schedule_id; ?>" type="hidden" required>
            <!-- <input id="website_url" name="website_url" value="https://esewa.com.np" type="hidden" required>
            <input id="return_url" name="return_url" value="https://google.com" type="hidden" required> -->
            <!-- <input id="signed_field_names" name="signed_field_names" value="total_amount,transaction_uuid,product_code" type="hidden" required>
            <input id="signature" name="signature" value="i94zsd3oXF6ZsSr/kGqT4sSzYQzjj1W/waxjWyRwaME=" type="hidden" required> -->
            <input value="Submit" value="submit" type="image" src="../images/payment/khalti.png" style="height:50px; width:120px;  ">
            <!-- <button type="submit" value="Submit"><img src="../images/payment/esewa.jpg" alt="Esewa"></button> -->
        </form>

        <form action="esewa/payment-request.php" method="POST" class="esewaform" id="esewaform" style="width:max-content">
        <input type="hidden" id="inputSeats" name="inputSeats" value="<?= $seat_numbers_str; ?>" required>
        <input type="hidden" id="inputName" name="inputName" required>
        <input type="hidden" id="inputEmail" name="inputEmail" value="<?= $_SESSION['email'];?>" required>
        <input type="hidden" id="inputPhone" name="inputPhone" value="<?= $_SESSION['phoneno'];?>" required>
        <input type="hidden" id="agehidden" name="age" required>
        <input type="hidden" id="genderhidden" name="gender" required>
        <input type="hidden" id="idproofhidden" name="id_proof" required>
        <input type="hidden" id="iddetailshidden" name="id_details" required>
        <input type="hidden"  name="schedule_id" value="<?= $schedule_id; ?>" required>
        <input type="hidden" name="user_id" value="<?= $user_id; ?>" required>
        <input type="hidden" id="paymentmethod" name="payment_method" value="Esewa" required>

        
        <input id="inputAmount" name="inputAmount" value="<?= $total_fare_numbers[0]; ?>" type="hidden" required>
        <input id="purchase_order_id" name="purchase_order_id" value="<?= $ticketNumber; ?>" type="hidden" required>
        <input id="purchase_order_name" name="purchase_order_name" value ="<?= 'Bus'.$user_id.$schedule_id; ?>" type="hidden" required>

        <input value="Submit" value="submit" type="image" src="../images/payment/esewa.png" style="height:38px; width:120px; ">
        </form>
    </div>
        <form action="cancel_booking.php" method="GET" id="cancelForm" style="border-top: 0px solid #ccc; border-bottom: 0px solid #ccc; border-top-left-radius:0px; border-top-right-radius: 0px;">
        <input type="hidden" name="schedule_id" value="<?= $schedule_id; ?>">
        <input type="hidden" name="user_id" value="<?= $user_id; ?>">
        <button type="submit" class="cancelButton" name="cancel" >Cancel Booking</button>
    </form>
    </div>
    </div>
    
    <script>
            document.addEventListener("DOMContentLoaded", function () {
    function syncFormValues() {
        // Retrieve values from user input fields
        const nameValue = document.getElementById("name").value || '';
        const ageValue = document.getElementById("age").value || '';
        const genderValue = document.getElementById("gender").value || '';
        const idProofValue = document.getElementById("id_proof").value || '';
        const idDetailsValue = document.getElementById("id_details").value || '';

        // Log for debugging
        console.log("Syncing Values:", {
            name: nameValue,
            age: ageValue,
            gender: genderValue,
            idProof: idProofValue,
            idDetails: idDetailsValue,
        });

        // Update hidden fields in esewaform
        document.querySelector("#esewaform #inputName").value = nameValue;
        document.querySelector("#esewaform #agehidden").value = ageValue;
        document.querySelector("#esewaform #genderhidden").value = genderValue;
        document.querySelector("#esewaform #idproofhidden").value = idProofValue;
        document.querySelector("#esewaform #iddetailshidden").value = idDetailsValue;

        // Update hidden fields in khaltiform
        document.querySelector("#khaltiform #inputName").value = nameValue;
        document.querySelector("#khaltiform #agehidden").value = ageValue;
        document.querySelector("#khaltiform #genderhidden").value = genderValue;
        document.querySelector("#khaltiform #idproofhidden").value = idProofValue;
        document.querySelector("#khaltiform #iddetailshidden").value = idDetailsValue;
    }

    // Add event listeners to forms
    document.getElementById("khaltiform").addEventListener("submit", function (event) {
        syncFormValues();
    });

    document.getElementById("esewaform").addEventListener("submit", function (event) {
        syncFormValues();
    });
});



    //                function syncFormValues() {
    //     const nameValue = document.getElementById("name").value || '';
    //     const ageValue = document.getElementById("age").value || '';
    //     const genderValue = document.getElementById("gender").value || '';
    //     const idProofValue = document.getElementById("id_proof").value || '';
    //     const idDetailsValue = document.getElementById("id_details").value || '';

    //     console.log("Syncing Values:", nameValue, ageValue, genderValue, idProofValue, idDetailsValue);

    //     document.getElementById("inputName").value = nameValue;
    //     document.getElementById("agehidden").value = ageValue;
    //     document.getElementById("genderhidden").value = genderValue;
    //     document.getElementById("idproofhidden").value = idProofValue;
    //     document.getElementById("iddetailshidden").value = idDetailsValue;
    // }

    // // Trigger the sync before submitting the Khalti form
    // document.getElementById("khaltiform").addEventListener("submit", function(event) {
    //     syncFormValues();  // Sync the values before submitting
    // });
    // document.getElementById("esewaform").addEventListener("submit", function(event) {
    //     syncFormValues();  // Sync the values before submitting
    // });




     // Assuming $temporary_booking_time is passed from PHP in 'Y-m-d H:i:s' format
    var temporaryBookingTime = "<?php echo $temporary_booking_time; ?>"; // PHP variable
    // console.log(temporaryBookingTime);

// Convert temporaryBookingTime to a Date object
const bookingTime = new Date(temporaryBookingTime.replace(" ", "T")); // Replacing space with 'T' for Date format

// Current time
const currentTime = new Date();

// Calculate the difference in milliseconds between current time and temporary booking time
let timeElapsed = Math.floor((currentTime - bookingTime) / 1000);  // Difference in seconds

let timeRemaining = 300 - timeElapsed; // Total time is 5 minutes (300 seconds)

// Check if the time has already expired
if (timeRemaining < 0) {
    timeRemaining = 0; // Make sure the time doesn't go negative
}

const timerElement = document.getElementById("timer");
const cancelForm = document.getElementById("cancelForm");

// Update the timer every second
const countdown = setInterval(() => {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    document.getElementById("timeDisplay").textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    timeRemaining--; // Decrease time remaining

    // Check if time remaining is 0 or negative and stop the countdown
    if (timeRemaining <= 0) {
        clearInterval(countdown);
        cancelBooking(); // Function to cancel the booking after the timer runs out
    }
}, 1000);
        // let timeRemaining = 300;  // 2 minutes in seconds
        // const timerElement = document.getElementById("timer");
        // const cancelForm = document.getElementById("cancelForm");

        // // Update the timer every second
        // const countdown = setInterval(() => {
        //     const minutes = Math.floor(timeRemaining / 60);
        //     const seconds = timeRemaining % 60;
        //     timerElement.textContent = `Time remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        //     timeRemaining--;

        //     if (timeRemaining < 0) {
        //         clearInterval(countdown);
        //         cancelBooking();
        //     }
        // }, 1000);

        // Function to cancel the booking when time expires
function cancelBooking() {
    // Make an AJAX request to cancel the booking
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "cancel_booking.php?schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&cancel=true", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert("Your booking time has expired. Please start booking again!");
            window.location.href = "bus_details.php?schedule_id=<?= $schedule_id; ?>";
        }
    };
    xhr.send();
}

function cancelBookingRefresh() {
    // Make an AJAX request to cancel the booking
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "cancel_booking.php?schedule_id=<?= $schedule_id; ?>&user_id=<?= $user_id; ?>&cancel=true", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert("Your clicked the refresh button. Please start booking again!");
            window.location.href = "bus_details.php?schedule_id=<?= $schedule_id; ?>";
        }
    };
    xhr.send();
}

                // Define an array of form IDs for the payment methods
            const paymentFormIds = ["khaltiform", "esewaform", "cancelForm"]; // Add more form IDs as needed
            let paymentSubmitted = false;

            // Loop through each form ID and add a submit event listener
            paymentFormIds.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener("submit", function () {
                        paymentSubmitted = true;
                    });
                }
            });

            // Cancel the booking only if the user navigates away and no payment was submitted
            window.addEventListener("beforeunload", function (e) {
                if (!paymentSubmitted) {
                    cancelBookingRefresh();
                }
            });


            document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});
    </script>
    <?php
} else {
    header("Location:bus_details.php?schedule_id=$schedule_id");
    echo "<script>alert(No temporary reservation found. Please try again.)</script>";
    exit();
}
?>
</body>
</html>