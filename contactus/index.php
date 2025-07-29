<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: ../home/contactus.php');
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
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
        <h1>Contact</h1><br>
        <hr>
        <section class="contact">
       
  
        <div class="about-us-description">
            <h2 style="text-align: center;">Aryan Rauniyar</h2>
            <p>
                <li>Phone No.: <a class="phone" href="tel:+9779840594031">+977 9840594031</a></li>
                <li>E-mail: <a class="phone" href="mailto:aryan.rauniyar12@gmail.com">aryan.rauniyar12@gmail.com</a></li>
                
            </p>
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