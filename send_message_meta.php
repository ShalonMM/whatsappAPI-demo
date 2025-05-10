<?php
// send_message.php
header('Content-Type: application/json');

$access_token = 'EAAJeGGGWnYIBOZCEgl2y8oGW0wed6xSSwr7V23bcULyl63UL3tfk5fcM5T3I2Ko9YYujZA6P6cZChLdmOBR2wgEhiOZCnw4XwaKz44OgaZCIXICsckGkXGFF6eFdSwhCMgk005cZBcUkk7ZAmig5xEEHnTirlcxpvWYDS6tWQ7MEwrZBolYSf8Eqxb7BNTKJUISE5yKnr83nTSeVEzOESdC8nuoH8UGp8J8JIHab'; // Replace with your Meta access token
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