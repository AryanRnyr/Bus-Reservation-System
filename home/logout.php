<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="admin"){
	header('Location: ../admin/logout.php');
    }
}else{
	header('Location: ../index.php');
}


session_destroy();
// Redirect to the login page:
header('Location: ../index.php');
?>