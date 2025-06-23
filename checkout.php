<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Set Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51O8hN1EqXnZAamc5xAGQrgO2SCcJkCGsylRg6RazDZfZiE3teZzBQufPiUUuQiFj8fQiBoLcjksob4o4mFJXaaSa00gNgFxooI');

// Get customer email & amount from form POST
$customer_email = $_POST['customer_email'];
$amount = $_POST['amount'] * 100; // Stripe expects amount in cents

// Create Stripe Checkout Session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'product_data' => ['name' => 'Invoice Payment'],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/index.php?canceled=true',
    'customer_email' => $customer_email,
]);

$paymentUrl = $session->url;

// Generate QR Code for the payment link
$qrCode = new QrCode($paymentUrl);
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrPath = __DIR__ . '/payment_qr.png';
$result->saveToFile($qrPath);

// Send Email with payment link and QR code
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'fundmenow23@gmail.com'; // Your Gmail address
$mail->Password = 'viywkbgmwmmctqaq'; // Your app password, no spaces!
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('fundmenow23@gmail.com', 'Auto Bravia');
$mail->addAddress($customer_email);
$mail->Subject = 'Your Payment Link';
$mail->Body = "Click to pay: $paymentUrl\n\nOr scan the attached QR code.";
$mail->addAttachment($qrPath);

// Send the mail
if(!$mail->send()) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    // Optionally display an error to the user
}

// Redirect to Stripe payment page
header("Location: " . $paymentUrl);
exit;
?>