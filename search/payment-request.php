
<?php
session_start();

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"]=="POST") {
    // Gather data
    $amount = $_POST['inputAmount']*100; // convert the amount to paisa
    // $amount = 10 * 100; // convert the amount to paisa
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
    $_SESSION["seats"]=$_POST["inputSeats"];

    // Store session data
    $_SESSION["bookingdetails_amount"] = $amount;
    unset($_SESSION["bookingdetails_purchaseid"]);
    $_SESSION["bookingdetails_purchaseid"] = $purchase_order_id;
    $_SESSION["bookingdetails_name"] = $name;
    $_SESSION["bookingdetails_age"] = $age;
    $_SESSION["bookingdetails_gender"] = $gender;
    $_SESSION["bookingdetails_idproof"] = $id_proof;
    $_SESSION["bookingdetails_iddetails"] = $id_details;
    $_SESSION["bookingdetails_paymentmethod"] = $payment_method;
    $_SESSION["bookingdetails_email"] = $email;
    $_SESSION["bookingdetails_phone"] = $phone;

    // Validate data
    if (empty($amount) || empty($purchase_order_id) || empty($purchase_order_name) || empty($name) || empty($email) || empty($phone) || empty($age) || empty($id_details) || $id_proof=="" || $gender=="") {
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

    // Prepare POST data
    $postFields = array(
        "return_url" => "http://localhost/RNBus/search/payment-response.php",
        "website_url" => "http://localhost/RNBus/",
        "amount" => $amount,
        "purchase_order_id" => $purchase_order_id,
        "purchase_order_name" => $purchase_order_name,
        "customer_info" => array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone
        )
    );

    $jsonData = json_encode($postFields);

    // cURL setup
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Authorization: key 56595a553dab4dddb575ecae4ec358f2',
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        echo 'cURL Error: ' . curl_error($curl);
    } else {
        $responseArray = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON Error: ' . json_last_error_msg();
        } elseif (isset($responseArray['error'])) {
            echo 'Error: ' . $responseArray['error'];
        } elseif (isset($responseArray['payment_url'])) {
            header('Location: ' . $responseArray['payment_url']);
            exit();
        } else {
            echo 'Unexpected response: ' . $response;
        }
    }

    curl_close($curl);
}
?>
