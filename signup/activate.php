<?php
// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';
$successMessage = "";
$errorMessage="";
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// First we check if the email and code exists...
if (isset($_GET['email'], $_GET['code'])) {
	if ($stmt = $con->prepare('SELECT * FROM users WHERE email = ? AND activation_code = ?')) {
		$stmt->bind_param('ss', $_GET['email'], $_GET['code']);
		$stmt->execute();
		// Store the result so we can check if the account exists in the database.
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			// Account exists with the requested email and code.
			if ($stmt = $con->prepare('UPDATE users SET activation_code = ?, status = ? WHERE email = ? AND activation_code = ?')) {
				// Set the new activation code to 'activated', this is how we can check if the user has activated their account.
                $status=1;
				$newcode = 'activated';
				$stmt->bind_param('siss', $newcode, $status, $_GET['email'], $_GET['code']);
				$stmt->execute();
				// echo 'Your account is now activated!';
				echo '<script>alert("Your account is now activated!");</script>';
                echo "<script>setTimeout(function(){ window.location.href = '../login'; }, 1000);</script>";

			}
		} else {
			echo '<script>alert("The account is already activated or doesn\'t exist!");</script>';
            echo "<script>setTimeout(function(){ window.location.href = '../login'; }, 1000);</script>";
		}
	}
}
?>