<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "rn_bus_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search term and type (departure or arrival) from the AJAX request
$query = $_POST['query'];
$type = $_POST['type']; // Not strictly necessary, but useful if handling them differently

// Prepare and execute the SQL statement
$sql = "SELECT city FROM location WHERE city LIKE ? LIMIT 15";
$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Generate the suggestions list
while ($row = $result->fetch_assoc()) {


    echo '<div onclick="selectSuggestion(\'' . htmlspecialchars($type) . '\', \'' . htmlspecialchars($row['city']) . '\')">' . htmlspecialchars($row['city']) . '</div>';
}

$stmt->close();
$conn->close();
?>
