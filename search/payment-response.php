<?php
session_start();
// Get the pidx from the URL
$pidx = $_GET['pidx'] ?? null;

if ($pidx) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: key 56595a553dab4dddb575ecae4ec358f2',
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        $responseArray = json_decode($response, true);
        switch ($responseArray['status']) {
            case 'Completed':
                // Payment was successful, update the database and booking status
                // Retrieve session details
                $amount = $_SESSION['bookingdetails_amount'];
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
                $selected_seats = $_SESSION['selected_seats']; // Assume this contains selected seats (comma-separated)

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
                    $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "success",
                            title: "Transaction successful. Your booking is confirmed.",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';

                    // Redirect to the complete_booking.php page with necessary details
                    header("Location: complete_booking.php?purchase_order_id=$purchase_order_id&name=$name&selected_seats=$selected_seats&amount=$amount");
                    exit();
                } else {
                    // Handle error if insertion fails
                    $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error occurred while confirming your booking. Please try again.",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>';
                    header("Location: confirm_booking.php?schedule_id=" . $_SESSION['schedule_id'] . "&user_id=" . $_SESSION['id']);
                    exit();
                }
                break;

            case 'Expired':
            case 'User canceled':
                // Payment failed or was canceled, handle accordingly
                $_SESSION['transaction_msg'] = '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Payment was canceled or expired. Please try again.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
                header("Location: cancel_booking.php?schedule_id=" . $_SESSION['schedule_id'] . "&user_id=" . $_SESSION['id']);
                exit();
                break;

            default:
                // In case of any other error, handle the failure
                $_SESSION['transaction_msg'] = '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Transaction failed. Please try again.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';
                header("Location: confirm_booking.php?schedule_id=" . $_SESSION['schedule_id'] . "&user_id=" . $_SESSION['id']);
                exit();
                break;
        }
    } else {
        // Handle the case where the API response is empty or failed
        $_SESSION['transaction_msg'] = '<script>
            Swal.fire({
                icon: "error",
                title: "Failed to retrieve payment details. Please try again.",
                showConfirmButton: false,
                timer: 1500
            });
        </script>';
        header("Location: confirm_booking.php?schedule_id=" . $_SESSION['schedule_id'] . "&user_id=" . $_SESSION['id']);
        exit();
    }
} else {
    // Handle the case where pidx is not present
    $_SESSION['transaction_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "Invalid payment ID.",
            showConfirmButton: false,
            timer: 1500
        });
    </script>';
    header("Location: confirm_booking.php?schedule_id=" . $_SESSION['schedule_id'] . "&user_id=" . $_SESSION['id']);
    exit();
}
?>
