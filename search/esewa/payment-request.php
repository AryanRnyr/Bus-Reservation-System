<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // var_dump($_POST);

    $purchase_order_id = $_POST['purchase_order_id'];
    $purchase_order_name = $_POST['purchase_order_name'];
    $name = $_POST['inputName'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $id_proof = $_POST['id_proof'];
    $id_details = $_POST['id_details'];
    $payment_method = $_POST['payment_method'];
    $email = $_POST['inputEmail'];
    $phone = $_POST['inputPhone'];
    $amount=$_POST['inputAmount'];
    $_SESSION["seats"]=$_POST["inputSeats"];

    $schedule_id=$_POST["schedule_id"];
    $user_id=$_POST["user_id"];

     // Validate data
     if (empty($amount) || empty($purchase_order_id) || empty($purchase_order_name) || empty($name) || empty($email) || empty($phone) || empty($age) || empty($id_details)  || $id_proof=="" || $gender=="") {
        $_SESSION["validate_msg"] = 'All fields are required';
        echo '<script>
        window.history.back(); // Go back to the previous page
        </script>';
        exit();
    }

    $name = trim($name); 

    if (empty($name) || !preg_match("/^[a-zA-Z ]*$/", $name)) {
        $_SESSION["validate_msg"] = 'Name must contain only letters and cannot be empty';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }


    if (!is_numeric($amount)) {
        $_SESSION["validate_msg"] = 'Amount must be a number';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    if (!is_numeric($age) || $age < 0) {
        $_SESSION["validate_msg"] = 'Age must be a non-negative number';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }
    

    if (!is_numeric($phone)) {
        $_SESSION["validate_msg"] = 'Phone number must be a number';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["validate_msg"] = 'Email is not valid';
        echo '<script>
        window.history.back();
        </script>';
        exit();
    }

    // Store session data
    $_SESSION["bookingdetails_amount"] = $amount;
    $_SESSION["bookingdetails_purchaseid"] = $purchase_order_id;
    $_SESSION["bookingdetails_name"] = $name;
    $_SESSION["bookingdetails_age"] = $age;
    $_SESSION["bookingdetails_gender"] = $gender;
    $_SESSION["bookingdetails_idproof"] = $id_proof;
    $_SESSION["bookingdetails_iddetails"] = $id_details;
    $_SESSION["bookingdetails_paymentmethod"] = $payment_method;
    $_SESSION["bookingdetails_email"] = $email;
    $_SESSION["bookingdetails_phone"] = $phone;
} else {
    die("No data received!");
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/enc-base64.min.js"></script>
</head>
<body>
        <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" id="autoForm" method="POST">
        <input type="hidden" id="amount" name="amount" value="<?= $amount ?>" required>
        <input type="hidden" id="tax_amount" name="tax_amount" value ="0" required>
        <input type="hidden" id="total_amount" name="total_amount" value="<?= $amount ?>" required>
        <input type="hidden" id="transaction_uuid" name="transaction_uuid" value="<?= $purchase_order_id ?>" required>
        <input type="hidden" id="product_code" name="product_code" value ="EPAYTEST" required>
        <input type="hidden" id="product_service_charge" name="product_service_charge" value="0" required>
        <input type="hidden" id="product_delivery_charge" name="product_delivery_charge" value="0" required>
        <input type="hidden" id="success_url" name="success_url" value="<?php echo 'http://localhost/RNBus/search/esewa/payment-response.php?ticket_number='.$purchase_order_id.'&user_id='.$user_id.'&schedule_id='.$schedule_id; ?> " required>
        <input type="hidden" id="failure_url" name="failure_url" value="<?php echo 'http://localhost/RNBus/search/cancel_booking.php?schedule_id='.$schedule_id.'&user_id='.$user_id; ?> " required>
        <input type="hidden" id="signed_field_names" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
        <input type="hidden" id="signature" name="signature" value="i94zsd3oXF6ZsSr/kGqT4sSzYQzjj1W/waxjWyRwaME=" required>
        <input value="Submit" type="hidden">
        </form>

        <script>
        function generateSignature() {
        // Retrieve values from the form
        var total_amount = document.getElementById("total_amount").value;
        var transaction_uuid = document.getElementById("transaction_uuid").value;
        var product_code = document.getElementById("product_code").value;
        var secret = "8gBm/:&EnhH.1/q"; // Replace with your actual secret key

        // Generate the signature
        var hash = CryptoJS.HmacSHA256(`total_amount=${total_amount},transaction_uuid=${transaction_uuid},product_code=${product_code}`, secret);
        var hashInBase64 = CryptoJS.enc.Base64.stringify(hash);

        // Set the signature value in the form
        document.getElementById("signature").value = hashInBase64;
        }

        // Call the function once during page load
        generateSignature();

        // Automatically submit the form when the page loads
        document.getElementById("autoForm").submit();

        
        </script>
</body>
</html>