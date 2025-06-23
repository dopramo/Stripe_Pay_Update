require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51O8hN1EqXnZAamc5xAGQrgO2SCcJkCGsylRg6RazDZfZiE3teZzBQufPiUUuQiFj8fQiBoLcjksob4o4mFJXaaSa00gNgFxooI');

// Create a Checkout Session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => 'Invoice Payment',
            ],
            'unit_amount' => 1000, // $10.00
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/success?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/cancel',
]);

$paymentUrl = $session->url;

// Generate QR Code for Payment Link
require_once('vendor/autoload.php');
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$qrCode = QrCode::create($paymentUrl);
$writer = new PngWriter();
$result = $writer->write($qrCode);

// Save QR code to disk
$qrPath = __DIR__ . '/payment_qr.png';
$result->saveToFile($qrPath);

// Now send the email with payment link and QR code
require 'PHPMailer/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->setFrom('your@email.com', 'Auto Bravia');
$mail->addAddress('customer@email.com');
$mail->Subject = 'Your Invoice Payment Link';
$mail->Body = "Dear Customer,\nPlease use the following link to pay your invoice: $paymentUrl\nOr scan the attached QR code.";
$mail->addAttachment($qrPath);

if(!$mail->send()) {
    echo 'Message could not be sent.' . $mail->ErrorInfo;
} else {
    echo 'Invoice email sent!';
}