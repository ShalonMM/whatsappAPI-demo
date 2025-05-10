<?php
// send_message.php
header('Content-Type: application/json');

$access_token = 'YOUR_PERMANENT_ACCESS_TOKEN'; // Replace with your Meta access token
$phone_number_id = 'YOUR_PHONE_NUMBER_ID'; // Replace with your WABA phone number ID
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