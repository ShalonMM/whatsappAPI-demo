<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
// send_message.php
header('Content-Type: application/json');
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$access_token = 'EAAJeGGGWnYIBOykonSfYaFTuCI609hsT820BElFpOjrq4ITGdiNRnHAbKtrE1wX9lFXCRmOJ8apDTVJGpx1RFFwaZADSmvkZCRCpSIFkRDz1JPf1cgbbZCmTH0NbGtnsbbtiTYU9awY4aZBbhgeZBd18ZCjyfo8VcXFElHVxHbZAuoTYS184P5eo35ZBGLxTrB3ZCnWFkaUiMZCvDuh6nHDbD6cmfzQ3tcuxYw3YZB6'; // Replace with your Meta access token
$phone_number_id = '634297469766679'; // Replace with your WABA phone number ID
$to = $_POST['to'];
$body = $_POST['body'];

$url = "https://graph.facebook.com/v20.0/$phone_number_id/messages";
$data = [
    'messaging_product' => 'whatsapp',
    'to' => $to,
    'type' => 'text',
    'text' => ['body' => $body]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => json_decode($response)->error->message]);
}
?>