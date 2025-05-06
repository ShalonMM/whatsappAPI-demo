<?php

//Handles sending messages via Twilio API.
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$sid = 'AC13345d0dd9e0333934b90c46ce077e6b';
$token = '3d291b75d14812db32cccb460255b032';
$twilio = new Client($sid, $token);

$to = $_POST['to'];
$body = $_POST['body'];

try {
    $message = $twilio->messages->create(
        "whatsapp:$to",
        [
            "from" => "whatsapp:+12283728918",
            "body" => $body
        ]
    );
    echo json_encode(['success' => true, 'sid' => $message->sid]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>