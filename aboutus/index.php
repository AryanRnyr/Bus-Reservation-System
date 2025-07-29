<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: ../home/aboutus.php');
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    
</head>
<body>
<header>
        <nav class="navbar">
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <button class="hamburger" aria-label="Toggle menu">
            ☰
        </button>
            <ul class="nav-links">
                <li><a href="../contactus/">Contact</a></li>
                <li><a href="../aboutus/">About Us</a></li>
                <li><a href="../policies/">Policies</a></li>
                <li><a href="../login/" class="login-btn">Login</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>About Us</h1>
        <section class="about-us">
       
    <div class="about-us-content">
        <div class="about-us-logo">
            <img src="logo2.png" alt="RN Bus Yatayat Logo">
        </div>
        <div class="about-us-description">
            
            <p>
                RN Bus Pvt. Ltd. was founded by Aryan Rauniyar with the vision to revolutionize bus transportation in Nepal. 
                Our company is committed to providing safe, comfortable, and affordable travel solutions to all our passengers. 
                With a modern fleet of buses and a customer-centric approach, we strive to ensure a seamless and enjoyable journey every time. 
                At RN Bus Yatayat, we take pride in our dedication to punctuality, reliability, and the well-being of our travelers. 
                Join us and experience a new standard in bus travel!
            </p>
        </div>
    </div>
</section>   
    </main>
    
    <footer class="footer">
    <h3 style="color:#f8b600">Developed By</h3>
    <p><a class="mylink" href="https://aryanrauniyar.com.np">&copy; Aryan Rauniyar</a></p>

    
</footer>

<script>
    document.querySelector('.hamburger').addEventListener('click', () => {
    document.querySelector('.nav-links').classList.toggle('active');
});

</script>
</body>
</html>