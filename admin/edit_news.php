<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if($_SESSION["s_role"]=="customer"){
	header('Location: home/');
    }
}else{
    header("Location: ../login");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['news_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    // Validation: Check if bus name contains numbers
    if (preg_match('/\d/', $title)) {
        echo "Title should not contain numbers!";
    } else {
        
            // Update the bus details
            $updateSql = "UPDATE news SET title = ?, description = ?, date = ? WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("sssi", $title, $description, $date, $id);
            $stmt->execute();
            // Redirect to the list of buses page or show a success message
            echo "<script>alert('News Updated successfully!');</script>";
            header("refresh:1;url=list_news.php");
            exit();
        }
    }

?>