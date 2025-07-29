<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="admin"){
	header('Location: admin/');
    }else{
        header('Location: home/');
    }
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
$sql = "SELECT id, title, description, date FROM news";
$result = $conn->query($sql);

include "includes/cleanup_temp_bookings.php";
cleanupExpiredBookings($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- <meta name="viewport" content="width=1024, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Bus Booking System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="images/x-icon" href="images/favicon.ico">
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

        .sourcediv,
        .arrivaldiv{
            position: relative; display: inline-block; width: 200px;
        }
    </style>
</head>
<body>
    <header>
    <nav class="navbar">
        <a class="linklogo" href="../RNBUS">
            <img class="logo" src="images/logo2.png" alt="Logo" style="height: inherit; margin: 0px 6px;">
            RN Bus Pvt. Ltd.
        </a>
        <button class="hamburger" aria-label="Toggle menu">
            ☰
        </button>
        <ul class="nav-links">
            <li><a href="contactus/">Contact</a></li>
            <li><a href="aboutus/">About Us</a></li>
            <li><a href="policies/">Policies</a></li>
            <li><a href="login/" class="login-btn">Login</a></li>
        </ul>
    </nav>
    </header>
    <main>
        <section class="search-section">
            <h1>Namaste! Tell us where you want to Travel</h1><br>
            <hr><br>
            <form class="search-form"  method="get" action="search/index.php">
          
            <div class="sourcediv" >
        <input type="text" name="source" id="departure" placeholder="Enter Source City" onfocus="showSuggestions('departure')" oninput="fetchSuggestions('departure')">
        <div id="suggestions-departure" class="dep-suggestions-list"></div>
        </div>
            
            <div class="arrivaldiv" >
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
                <img src="images/bus0.png" alt="Bus 0">
                
                <img src="images/bus8.jpg" alt="Bus 8">
                <img src="images/bus9.png" alt="Bus 10">
                
                
                <img src="images/bus1.jpg" alt="Bus 1">
                <img src="images/bus10.png" alt="Bus 9">
                <img src="images/bus2.jpg" alt="Bus 2">
                <img src="images/bus7.png" alt="Bus 7">
                <img src="images/bus3.jpg" alt="Bus 3">
                <img src="images/bus4.jpg" alt="Bus 4">
                <img src="images/bus5.avif" alt="Bus 5">
                <img src="images/bus6.jpg" alt="Bus 6">
                
                
                </div>
            </div>
        </section>
    </main>

<hr><br>

<div class="news">
<?php if ($result->num_rows > 0): ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col s12">
                <h4>News &amp; Updates</h4>
            </div>
            
            
            <!-- Carousel Wrapper -->
            <div class="carousel-wrapper">
                <div class="carousel-container">

                
            <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="carousel-item">
                        <div class="news-item">
                            <div class="header-news"><?= htmlspecialchars($row["title"]); ?></div>
                            <div class="date-news"><?= htmlspecialchars($row["date"]); ?></div>
                            <div class="content-news">
                            <?= htmlspecialchars($row["description"]); ?>
                            </div>
                        </div>
                    </div>
                
                    <?php endwhile; ?>    
                </div>

                <!-- Carousel Controls -->
                <button class="carousel-control left" onclick="moveCarousel(-1)">&#10094;</button>
                <button class="carousel-control right" onclick="moveCarousel(1)">&#10095;</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>
</footer>

<script>
    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});

</script>
    <script src="js/script.js"></script>
    <script src="home/script.js"></script>
</body>
</html>
