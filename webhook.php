
<?php
//Processes incoming messages from Twilio via webhook.


$from = $_POST['From'];
$body = $_POST['Body'];
$timestamp = date('c');

$message = [
    'from' => $from,
    'body' => $body,
    'timestamp' => $timestamp
];

$messages = json_decode(file_get_contents('messages.json'), true);
$messages[] = $message;
file_put_contents('messages.json', json_encode($messages));
?>