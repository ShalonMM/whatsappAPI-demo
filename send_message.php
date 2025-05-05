<?php

//Handles sending messages via Twilio API.
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$sid = 'YOUR_TWILIO_ACCOUNT_SID';
$token = 'YOUR_TWILIO_AUTH_TOKEN';
$twilio = new Client($sid, $token);

$to = $_POST['to'];
$body = $_POST['body'];

try {
    $message = $twilio->messages->create(
        "whatsapp:$to",
        [
            "from" => "whatsapp:YOUR_TWILIO_WHATSAPP_NUMBER",
            "body" => $body
        ]
    );
    echo json_encode(['success' => true, 'sid' => $message->sid]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>