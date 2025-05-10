<?php
// meta_webhook.php
header('Content-Type: application/json');

// Webhook verification
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verify_token = 'YOUR_WEBHOOK_VERIFY_TOKEN'; // Set this in Meta Developer Portal
    if (isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $verify_token) {
        echo $_GET['hub_challenge'];
        exit;
    }
    http_response_code(403);
    exit;
}

// Handle incoming messages
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['entry'][0]['changes'][0]['value']['messages'])) {
    $message = $input['entry'][0]['changes'][0]['value']['messages'][0];
    $from = $message['from'];
    $body = $message['text']['body'];
    $timestamp = date('Y-m-d H:i:s', $message['timestamp']);

    // Store message in messages.json
    $messages = file_exists('messages.json') ? json_decode(file_get_contents('messages.json'), true) : [];
    $messages[] = ['from' => $from, 'body' => $body, 'timestamp' => $timestamp];
    file_put_contents('messages.json', json_encode($messages));
}

echo json_encode(['status' => 'success']);
?>