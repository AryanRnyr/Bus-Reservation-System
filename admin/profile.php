<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/profile.php');
    }
}else{
    header("Location: ../login");
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// We don't have the password or email info stored in sessions, so instead, we can get the results from the database.
$stmt = $con->prepare('SELECT username, firstname, lastname, phoneno, email, user_image FROM users WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($username,$firstname, $lastname, $phoneno, $email, $user_image);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="profile.css" rel="stylesheet" type="text/css">
		<link rel="icon" type="images/x-icon" href="../images/favicon.ico">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
	</head>
	
	<body class="loggedin">
	<header>
        <nav class="navbar">
           
            <a class="linklogo" href="../"><img class="logo" src="../images/logo2.png" style="height: inherit; margin: 0px 6px;">RN Bus Pvt. Ltd.</a>
            <ul class="nav-links">
				<li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
				<li><a href="profile.php"><i class="fas fa-user-circle"></i> <?=$_SESSION['firstname']?></a></li>
				<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

	
		
		<div class="content">
			<h2>Profile Page</h2>

			<img src="../images/users/<?php echo $user_image;?>" width="200" class="img-circle" alt="Profile"> 
	
			<div class="tab">

			<div class="buttonss">
            <button class="tablinks-active" style="width: 50%" onclick="document.location='profile.php'">Personal Details</button>
            <button class="tablinks" style="width: 50%"  onclick="document.location='editdetails.php'">Edit Details</button>
			</div>

			<hr style="border-color: rgb(168, 168, 168); width:50%; margin:0 auto; opacity: 20%; margin-top:60px; margin-bottom:15px">
				<p>Your account details are below:</p>
				<table style="text-align: left;">
					<tr>
						<td>Username:</td>
						<td><?=htmlspecialchars($username, ENT_QUOTES)?></td>
					</tr>
					<tr>
						<td>First Name:</td>
						<td><?=htmlspecialchars($firstname, ENT_QUOTES)?></td>
					</tr>
					<tr>
						<td>Last Name:</td>
						<td><?=htmlspecialchars($lastname, ENT_QUOTES)?></td>
					</tr>
					<tr>
						<td>Phone No.:</td>
						<td><?=htmlspecialchars($phoneno, ENT_QUOTES)?></td>
					</tr>
					<tr>
						<td>Email:</td>
						<td><?=htmlspecialchars($email, ENT_QUOTES)?></td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>