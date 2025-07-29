<?php
session_start();

// Check if the user is logged in and not a customer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION["s_role"] == "customer") {
    header("Location:../home/");
    exit();
}

// Database connection
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (!$conn) {
    exit();
}

// Process the booking confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve booking details
    $seat_numbers = $_POST["selected_seats"];
    $seat_numbers_array = explode(', ', $seat_numbers);

    // Retrieve user details
    $full_name = $_POST["full_name"];
    $age = $_POST["age"];
    $gender = $_POST["gender"];
    $id_proof = $_POST["id_proof"];
    $id_details = $_POST["id_details"];
    $payment_method = $_POST["payment_method"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $total_fare = $_POST["total_fare"] * 100;

    $schedule_id = $_POST["schedule_id"];
    $user_id = $_POST["user_id"];
    $ticket_no = $_POST["ticket_no"];

    // Validate data
    if (empty($full_name) || empty($age) || empty($gender) || empty($id_details) || empty($id_proof) || empty($email) || empty($phone) || !isset($seat_numbers)) {
        $_SESSION["validate_msg"] = 'All fields are required';
        echo '<script>
        window.history.back(); // Go back to the previous page
        </script>';
        exit();
    }

    // Trim and validate name
    $full_name = trim($full_name);
    if (empty($full_name) || !preg_match("/^[a-zA-Z ]*$/", $full_name)) {
        $_SESSION["validate_msg"] = 'Name must contain only letters and cannot be empty';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    // Validate amount (assuming it's a POST parameter)
    if (!is_numeric($total_fare) || $total_fare <= 0) {
        $_SESSION["validate_msg"] = 'Amount must be a positive number';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    // Validate age
    if (!is_numeric($age) || $age < 0) {
        $_SESSION["validate_msg"] = 'Age must be a non-negative number';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    // Validate phone number
if (!is_numeric($phone)) {
    $_SESSION["validate_msg"] = 'Phone number must be a number';
    echo '<script>
    window.history.back();
    </script>';
    exit();
}

// Check if the phone number is a positive number
if ($phone < 0) {
    $_SESSION["validate_msg"] = 'Phone number cannot be a negative number';
    echo '<script>
    window.history.back();
    </script>';
    exit();
}

// Check if the phone number is exactly 10 digits long
if (strlen($phone) != 10) {
    $_SESSION["validate_msg"] = 'Phone number must be exactly 10 digits';
    echo '<script>
    window.history.back();
    </script>';
    exit();
}




    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["validate_msg"] = 'Email is not valid';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    // Loop through seat numbers to mark them as confirmed
    foreach ($seat_numbers_array as $seat_number) {
        $query = "UPDATE seat_reservation 
                  SET status = 'confirmed', full_name = '$full_name', age = '$age', gender = '$gender', id_proof = '$id_proof', id_details = '$id_details', payment_method = '$payment_method', ticket_no = '$ticket_no' 
                  WHERE seat_number = '$seat_number' AND status = 'temporary'";
        mysqli_query($conn, $query);
    }

    // Insert into the `orders` table
    $stmt = $conn->prepare("INSERT INTO orders (ticket_no, fare, name, age, gender, id_proof, id_details, payment_method, email, phone, user_id, schedule_id, seats, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $status = 'confirmed';
    $seats = implode(', ', $seat_numbers_array);

    $stmt->bind_param('ssssssssssssss', $ticket_no, $total_fare, $full_name, $age, $gender, $id_proof, $id_details, $payment_method, $email, $phone, $user_id, $schedule_id, $seats, $status);
    $stmt->execute();

    // Redirect to print ticket page
    header("Location: print_ticket.php?schedule_id=$schedule_id&user_id=$user_id&ticket_no=$ticket_no");
    exit();
}
?>
