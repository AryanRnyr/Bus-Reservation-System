<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

// Check if the ID parameter is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rn_bus_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL to delete the user
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirect to list_users.php with success message
        header("Location: list_users.php?msg=User deleted successfully");
    } else {
        // Redirect with error message
        header("Location: list_users.php?msg=Error deleting user");
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect if no valid ID is provided
    header("Location: list_users.php?msg=Invalid user ID");
    exit();
}
?>
