<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/whatsapp.php';

use Dotenv\Dotenv;

// Load .env file (optional, comment out if not using .env)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set verify token (use .env or hardcode)
$verifyToken = $_ENV['WEBHOOK_VERIFY_TOKEN'] ?? 'HelloWorld!';

// Log all requests for debugging
error_log('Webhook request received: ' . json_encode($_GET));

// Webhook verification (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($mode === 'subscribe' && $token === $verifyToken) {
        error_log('Webhook verified successfully for token: ' . $token);
        header('Content-Type: text/plain');
        http_response_code(200);
        echo $challenge;
        exit;
    } else {
        error_log('Webhook verification failed. Mode: ' . $mode . ', Token: ' . $token . ', Expected: ' . $verifyToken);
        header('Content-Type: text/plain');
        http_response_code(403);
        exit;
    }
}

// Handle incoming messages (POST request)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['entry'][0]['changes'][0]['value']['messages'])) {
    $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
    $from = $message['from'];
    $body = $message['text']['body'];
    $timestamp = date('Y-m-d H:i:s', $message['timestamp']);

    // Store message in messages.json
    $messages = file_exists('messages.json') ? json_decode(file_get_contents('messages.json'), true) : [];
    $messages[] = ['from' => $from, 'body' => $body, 'timestamp' => $timestamp];
    file_put_contents('messages.json', json_encode($messages));

    // Send an echo response using WhatsApp class
    $whatsapp = new WhatsApp();
    $response = $whatsapp->sendMessage($from, "Echo: $body");
    error_log('Echo response sent: ' . json_encode($response));
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>