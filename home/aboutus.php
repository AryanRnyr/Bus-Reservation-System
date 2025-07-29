<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="admin"){
	header('Location: ../admin');
    }
}else{
	header('Location: ../aboutus');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="../aboutus/style.css">
    <link rel="icon" type="images/x-icon" href="../images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        .profile-btn {
    background: #ffc162;
    color: white;
    border-radius: 5px;
    padding: 0.5rem 1rem;
}

.logout-btn{
    background: #ffc162;
    color: white;
    border-radius: 5px;
    padding: 0.5rem 1rem;
}
    </style>
</head>
<body>
<header>
        <nav class="navbar">
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
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
        <h1>About Us</h1>
        <section class="about-us">
       
    <div class="about-us-content">
        <div class="about-us-logo">
            <img src="../images/logo2.png" alt="RN Bus Yatayat Logo">
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
</body>
</html>