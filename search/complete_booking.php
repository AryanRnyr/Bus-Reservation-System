<?php
session_start();
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';
require '../tcpdf/tcpdf.php'; // Include TCPDF

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in and not an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login/');
    exit();
} elseif ($_SESSION["s_role"] == "admin") {
    header("Location:../admin/");
    exit();
}

// Database connection
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'rn_bus_db';

$conn = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Process the booking confirmation
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Retrieve booking details
    $seat_numbers = $_GET["selected_seats"];
    $seat_numbers_array = explode(', ', $seat_numbers); // Convert to array

    // Retrieve user details
    $full_name = $_SESSION['bookingdetails_name'];
    $age = $_SESSION['bookingdetails_age'];
    $gender = $_SESSION['bookingdetails_gender'];
    $id_proof = $_SESSION['bookingdetails_idproof'];
    $id_details = $_SESSION['bookingdetails_iddetails'];
    $payment_method = $_SESSION['bookingdetails_paymentmethod'];

    $schedule_id = $_SESSION["schedule_id"];
    $user_id = $_SESSION["user_id"];
    $ticket_no = $_SESSION["bookingdetails_purchaseid"];

    foreach ($seat_numbers_array as $seat_number) {
        // Mark the seat as confirmed in the database
        $query = "UPDATE seat_reservation 
                  SET status = 'confirmed', full_name = '$full_name', age = '$age', gender = '$gender', id_proof = '$id_proof', id_details = '$id_details', payment_method = '$payment_method', ticket_no = '$ticket_no' 
                  WHERE seat_number = '$seat_number' AND status = 'temporary'";

        if (!mysqli_query($conn, $query)) {
            echo "Error confirming booking for seat number $seat_number: " . mysqli_error($conn) . "<br>";
        }
    }

    // After confirming all seats, generate the PDF and send the email
    // Capture output from print_ticket.php
    ob_start();
    $_POST['schedule_id'] = $_SESSION["schedule_id"];
    $_POST['user_id'] = $user_id;
    $_POST['ticket_no'] = $ticket_no;
    include 'print_ticketsend.php'; // Include the script

    $html = ob_get_clean();
    $html = str_replace('../images/', 'http://localhost/RNBus/images/', $html);

    // Generate PDF using TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('RN Bus Pvt. Ltd.');
    $pdf->SetTitle('Bus Ticket');
    $pdf->SetSubject('Booking Confirmation');
    $pdf->SetKeywords('Ticket, Bus, Booking, RN Bus Pvt. Ltd.');

    // Add a page
    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 10);

    // Write HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    // Save the PDF
    $pdfPath = realpath(dirname(__FILE__) . '/../images/tickets') . '/ticket_' . $ticket_no . '.pdf';
    $pdf->Output($pdfPath, 'F');

    // Send email with PDF attachment
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info.rnbus@gmail.com';
        $mail->Password = 'uuzn fxwa aejr gbhr'; // Use App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('info.rnbus@gmail.com', 'RN Bus Pvt. Ltd.');
        $mail->addAddress($_SESSION['email'], $_SESSION['name']);
        $mail->addAttachment($pdfPath, 'eTicket.pdf');
        $mail->isHTML(true);
        $mail->Subject = 'Bus Ticket';
        $mail->Body = "
            <p>Dear sir/madam,</p>
            <p>Thank you for issuing bus ticket through RN Bus Pvt. Ltd.</p>
            <p>Please find the attached ticket. Kindly print or save this file in your mobile.</p>
            <br>
            <p>Regards,<br>
            RN Bus Pvt. Ltd.<br>
            977-9840594031<br>
            Chhetrapati, Kathmandu, Nepal</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Error sending email: " . $mail->ErrorInfo;
    }

    header("refresh:1.4;url=print_ticket.php?schedule_id=$schedule_id&user_id=$user_id&ticket_no=$ticket_no");
}
?>
