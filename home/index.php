<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="admin"){
	header('Location: ../admin');
    }
}else{
	header('Location: ../index.php');
}

// $conn= new mysqli('localhost','root','','rn_bus_db');

// if (!$conn)
// {
//     error_reporting(0);
//     die("Could not connect to mysql".mysqli_error($conn));
// }


// $bus = $conn->query("SELECT * FROM bus where status = 1");
// $location = $conn->query("SELECT id,Concat(city) as location FROM location where status = 1");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);
include "../includes/cleanup_temp_bookings.php";
cleanupExpiredBookings($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="style.css">
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
                <li><a href="contactus.php">Contact</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="policies.php">Policies</a></li>
                <li><a href="profile.php" class="profile-btn"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="search-section">
            <h1>Namaste! Tell us where you want to Travel</h1><br>
            <hr><br>
            <form class="search-form" method="get" action="../search/index.php">
          
            <div style="position: relative; display: inline-block; width: 200px;">
        <input type="text" name="source" id="departure" placeholder="Enter Source City" onfocus="showSuggestions('departure')" oninput="fetchSuggestions('departure')">
        <div id="suggestions-departure" class="dep-suggestions-list"></div>
        </div>
            
            <div style="position: relative; display: inline-block; width: 200px;">
    <input type="text" id="arrival" name="destination" placeholder="Enter Destination" onfocus="showSuggestions('arrival')" oninput="fetchSuggestions('arrival')">
    <div id="suggestions-arrival" class="arr-suggestions-list"></div>
</div>

                <input class="date" type="date" id="dateInput" name="date">
                <!-- <select name="shift">
                    <option value="both">Both</option>
                    <option value="morning">Morning</option>
                    <option value="evening">Evening</option>
                </select> -->
                <button type="submit">Search</button>
                
            </form>
        </section>
        <section class="available-buses">
            <h2 style="margin-bottom: 1rem;">Available Travel Bus</h2>
            <div class="bus-gallery-container">
            <div class="bus-gallery" id="busGallery">
                <img src="../images/bus0.png" alt="Bus 0">
                
                <img src="../images/bus8.jpg" alt="Bus 8">
                <img src="../images/bus9.png" alt="Bus 10">
                
                
                <img src="../images/bus1.jpg" alt="Bus 1">
                <img src="../images/bus10.png" alt="Bus 9">
                <img src="../images/bus2.jpg" alt="Bus 2">
                <img src="../images/bus7.png" alt="Bus 7">
                <img src="../images/bus3.jpg" alt="Bus 3">
                <img src="../images/bus4.jpg" alt="Bus 4">
                <img src="../images/bus5.avif" alt="Bus 5">
                <img src="../images/bus6.jpg" alt="Bus 6">
                
                
                </div>
            </div>
        </section>
    </main>

<hr><br>
        <div class="news">
    <div class="container-fluid">
        <div class="row">
            <div class="col s12">
                <h4>News &amp; Updates</h4>
            </div>
            
            <!-- Carousel Wrapper -->
            <div class="carousel-wrapper">
                <div class="carousel-container">
                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">Online Ticket Booking System Initiated</div>
                            <div class="date-news">October 1, 2024</div>
                            <div class="content-news">
                                RN Bus Pvt. Ltd. has started online ticket booking system where customers can directly book tickets through online payment gateway.
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">New Kathmandu-Pokhara Route Now Available</div>
                            <div class="date-news">November 12, 2024</div>
                            <div class="content-news">
                            We are excited to announce our new daily service between Kathmandu and Pokhara. Book your seat today!
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">New Bus Fleet Addition</div>
                            <div class="date-news">November 01, 2024</div>
                            <div class="content-news">
                                We have added 5 new luxury buses to our fleet, offering enhanced comfort and safety on all routes.
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">Ensuring Your Safety: New Health Guidelines</div>
                            <div class="date-news"></div>
                            <div class="content-news">
                            We have implemented new health and safety measures, including sanitization of buses after each trip, to ensure your comfort and well-being.
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">Going Green: Eco-Friendly Buses</div>
                            <div class="date-news"></div>
                            <div class="content-news">
                            We’re proud to announce that all our new buses are now eco-friendly, reducing emissions and contributing to a cleaner environment.
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news">Best Travel Operator Award 2024</div>
                            <div class="date-news">November 20, 2024</div>
                            <div class="content-news">
                                RN Bus Pvt. Ltd. was recognized as the Best Travel Operator of the Year at the 2024 Tourism Awards! Thank you for your continued support.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carousel Controls -->
                <button class="carousel-control left" onclick="moveCarousel(-1)">&#10094;</button>
                <button class="carousel-control right" onclick="moveCarousel(1)">&#10095;</button>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>


    <script src="../js/script.js"></script>
    <script src="script.js"></script>
    <script>
    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});

</script>
</body>
</html>
