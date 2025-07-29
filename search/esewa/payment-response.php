<?php
session_start();

if($_SERVER["REQUEST_METHOD"]=="GET" && isset($_GET['ticket_number']) && isset($_GET['user_id']) && isset($_GET['schedule_id'])){
    $amount = $_SESSION['bookingdetails_amount']*100;
    $purchase_order_id = $_SESSION['bookingdetails_purchaseid'];
    $name = $_SESSION['bookingdetails_name'];
    $age = $_SESSION['bookingdetails_age'];
    $gender = $_SESSION['bookingdetails_gender'];
    $id_proof = $_SESSION['bookingdetails_idproof'];
    $id_details = $_SESSION['bookingdetails_iddetails'];
    $payment_method = $_SESSION['bookingdetails_paymentmethod'];
    $email = $_SESSION['bookingdetails_email'];
    $phone = $_SESSION['bookingdetails_phone'];

    // Get selected seat details from the session
    $selected_seats = $_SESSION['selected_seats'];


         // Insert booking information into the orders table
                $db = new mysqli('localhost', 'root', '', 'rn_bus_db');
                if ($db->connect_error) {
                    die("Connection failed: " . $db->connect_error);
                }

                // Insert into orders table
                $stmt = $db->prepare("INSERT INTO orders (ticket_no, fare, name, age, gender, id_proof, id_details, payment_method, email, phone, user_id, schedule_id, seats, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $status = 'confirmed'; // Set the booking status to 'confirmed'
                $stmt->bind_param('ssssssssssssss', $purchase_order_id, $amount, $name, $age, $gender, $id_proof, $id_details, $payment_method, $email, $phone, $_SESSION['id'], $_SESSION['schedule_id'], $_SESSION["seats"], $status);

                if ($stmt->execute()) {
                    // Payment was successful and order was inserted successfully
                    
                    // Redirect to the complete_booking.php page with necessary details
                    header("Location: ../complete_booking.php?purchase_order_id=$purchase_order_id&name=$name&selected_seats=$selected_seats&amount=$amount");
                    exit();
                } 
}
?>
