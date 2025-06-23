require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$endpoint_secret = 'whsec_547d78ddefc5377d5af34234f721ccd62df35ae8f0f8655b694f1c327a2f2cab';
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(Exception $e) {
    http_response_code(400);
    exit();
}

if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
    $customer_email = $session->customer_email;

    $mail = new PHPMailer();
    $mail->setFrom('your@email.com', 'Auto Bravia');
    $mail->addAddress($customer_email);
    $mail->Subject = 'Payment Received';
    $mail->Body = "Thank you! Your payment has been received.";
    $mail->send();
}
http_response_code(200);