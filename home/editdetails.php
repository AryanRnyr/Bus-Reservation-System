<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="admin"){
	header('Location: ../admin/editdetails.php');
    }
}else{
	header('Location: ../login');
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
$stmt = $con->prepare('SELECT username, firstname, lastname, password, phoneno, email, user_image FROM users WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($username,$firstname, $lastname, $password, $phoneno, $email, $user_image);
$stmt->fetch();
$stmt->close();
?>

<?


?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
		<link href="profile.css" rel="stylesheet" type="text/css">
		<link rel="icon" type="images/x-icon" href="../images/favicon.ico">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer">
        <style>
			td input{
				padding: 5px;
			}
		</style>
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
            <button class="tablinks" style="width: 33%" onclick="document.location='profile.php'">Personal Details</button>
            <button class="tablinks" style="width: 33%" onclick="document.location='ticketsbooked.php'">Tickets Booked</button>
            <button class="tablinks-active" style="width: 33%"  onclick="document.location='editdetails.php'">Edit Details</button>
			</div>
			
			<hr style="border-color: rgb(168, 168, 168); width:50%; margin:0 auto; opacity: 20%; margin-top:60px; margin-bottom:15px">
				<p>Edit Details</p>
                <form method="post" action="update.php" enctype="multipart/form-data">
				<table style="text-align: left;">
					<tr>
						<td>Username:</td>
                        <td><input type="text" name="username" value="<?=htmlspecialchars($username, ENT_QUOTES)?>" disabled></td>
				
					</tr>
					<tr>
						<td></td>
						<td style="color:grey; font-weight:lighter; font-size:11px;">Username cannot be changed!</td>
					</tr>
                    <tr>
                        <td>Password:</td>
                       <!-- <td><input value="<?=htmlspecialchars($password, flags: ENT_QUOTES)?>" id="myInput" type="password" name="password"></td> -->
                       <td><input id="myInput" type="password" name="password" autocomplete="new-password"></td>

                    </tr>
					<tr>
						<td></td>
						<td style="font-size:11px; padding-top:0px; color:maroon">Leave blank to keep current password</td>
					</tr>
					<tr>
						<td></td>
						<td style="font-size:14px"><input type="checkbox" onclick="myFunction()"> Show Password</td>
					</tr>
					<tr>
						<td>First Name:</td>
                        <td><input type="text" name="firstname" value="<?=htmlspecialchars($firstname, flags: ENT_QUOTES)?>"></td>

					</tr>
					<tr>
						<td>Last Name:</td>
                        <td><input type="text" name="lastname" value="<?=htmlspecialchars($lastname, flags: ENT_QUOTES)?>"></td>

					</tr>
					<tr>
						<td>Phone No.:</td>
                        <td><input type="text" name="phoneno" value="<?=htmlspecialchars($phoneno, flags: ENT_QUOTES)?>"></td>
					</tr>
					<tr>
						<td>Email:</td>
                        <td><input type="email" name="email" value="<?=htmlspecialchars($email, flags: ENT_QUOTES)?>" disabled></td>
					</tr>
                    <tr>
						<td></td>
                        <td style="color:grey; font-weight:lighter; font-size:11px;">E-mail cannot be changed</td>
                    </tr>
					<tr>
						<td colspan="2" style="text-align:center;">User Image:</td>
					</tr>
					<tr>
						<td><img width="100" src="../images/users/<?php echo $user_image ?>"></td>
                        <td><input type="file" name="file" id="fileUpload" ></td>
						 <input type="text" name="filename" value="<?=htmlspecialchars($user_image, flags: ENT_QUOTES)?>" hidden>
					</tr>
					
					<?php
					if (!empty($_SESSION["validate_msg"])) {
						
						foreach ($_SESSION["validate_msg"] as $msg) {
							echo '<tr >';
							echo "<td colspan='2' style='color: red; font-size: 13px;'><li>$msg</li></td>";
							echo '</tr>';
						}
						
						unset($_SESSION["validate_msg"]); // Clear errors after displaying
					}
					?>
					
					<tr>
					<td colspan="2" id="errorMessages"></td>
					</tr>
					
                    <tr>
                        <td colspan="2" style="text-align:center"><button class="btnupdate" name="update" style="margin-left:35%; background-color: #2379e9;color:white; border-radius:10px;"  onmouseover="this.style.backgroundColor='#13488f'; this.style.color='white';" onmouseout="this.style.backgroundColor='#2379e9'; this.style.color='white';">Update</button></td>
                    </tr>
				</table>
                </form>
			</div>
		</div>

        <script>


            function myFunction() {
        var x = document.getElementById("myInput");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }


function validateForm(event) {
    let errors = [];
    
    // Get form fields
    let firstname = document.querySelector('input[name="firstname"]').value.trim();
    let lastname = document.querySelector('input[name="lastname"]').value.trim();
    let phoneno = document.querySelector('input[name="phoneno"]').value.trim();
    let password = document.querySelector('input[name="password"]').value.trim();
    let fileInput = document.getElementById("fileUpload");

    // First Name Validation
    if (firstname === "") {
        errors.push("First name cannot be empty.");
    } else if (/^\s+$/.test(firstname)) {
        errors.push("First name cannot contain only spaces.");
    } else if (!/^[a-zA-Z ]+$/.test(firstname)) {
        errors.push("First name must contain only letters and spaces.");
    } else if (firstname.length < 3) {
        errors.push("First name must be at least 3 characters long.");
    }

    // Last Name Validation
    if (lastname === "") {
        errors.push("Last name cannot be empty.");
    } else if (/^\s+$/.test(lastname)) {
        errors.push("Last name cannot contain only spaces.");
    } else if (!/^[a-zA-Z ]+$/.test(lastname)) {
        errors.push("Last name must contain only letters and spaces.");
    } else if (lastname.length < 3) {
        errors.push("Last name must be at least 3 characters long.");
    }

    // Phone Number Validation
    if (phoneno === "") {
        errors.push("Phone number cannot be empty.");
    } else if (!/^\d{10}$/.test(phoneno)) {
        errors.push("Phone number must be exactly 10 digits and numeric.");
    }

    // Password Validation (Only if user enters a new password)
    if (password.length > 0) {
        if (password.length < 8) {
            errors.push("Password must be at least 8 characters long.");
        }
        if (!/[A-Z]/.test(password)) {
            errors.push("Password must contain at least one uppercase letter.");
        }
        if (!/\d/.test(password)) {
            errors.push("Password must contain at least one number.");
        }
        if (!/[\W]/.test(password)) {
            errors.push("Password must contain at least one special character (e.g., !@#$%^&*).");
        }
    }

    // Image Validation
    if (fileInput.files.length > 0) {
        let fileName = fileInput.value;
        let fileExt = fileName.split('.').pop().toLowerCase();
        let allowedExtensions = ["jpg", "jpeg", "png", "gif"];

        if (!allowedExtensions.includes(fileExt)) {
            errors.push("Only JPG, JPEG, PNG, and GIF image files are allowed.");
        }
    }

    // Display Errors
    let errorContainer = document.getElementById("errorMessages");
    errorContainer.innerHTML = "";
    
    if (errors.length > 0) {
        event.preventDefault(); // Prevent form submission if there are errors
        errors.forEach(error => {
            let errorItem = document.createElement("li");
            errorItem.style.color = "red";
            errorItem.textContent = error;
            errorContainer.appendChild(errorItem);
        });
    }
}

// Attach event listener to the form
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector("form").addEventListener("submit", validateForm);
});
        </script>
	</body>
</html>