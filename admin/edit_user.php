<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $id = intval($_POST["id"]);
    $firstname = htmlspecialchars($_POST["firstname"]);
    $lastname = htmlspecialchars($_POST["lastname"]);
    $phoneno = htmlspecialchars($_POST["phoneno"]);
    $user_role = htmlspecialchars($_POST["user_role"]);
    $status = htmlspecialchars($_POST["status"]);

    // Update query without modifying the username and email
    $sql = "UPDATE users SET firstname = ?, lastname = ?, phoneno = ?, user_role = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $firstname, $lastname, $phoneno, $user_role, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User updated successfully.";
        header("Location: list_users.php");
    } else {
        $_SESSION['error_message'] = "Error updating user: " . $stmt->error;
        header("Location: list_users.php");
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
