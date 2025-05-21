<?php
/**
 * WhatsApp Webhook Integration in PHP
 * This script handles both webhook verification and incoming messages
 */

// Load environment variables from .env file
// You'll need to install the phpdotenv package or implement your own solution
// Alternatively, you can set these directly in your server environment
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file('.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Configuration
$verify_token = getenv('VERIFY_TOKEN') ?: 'your_custom_verify_token';
$access_token = getenv('ACCESS_TOKEN') ?: 'your_meta_access_token';
$phone_number_id = getenv('PHONE_NUMBER_ID') ?: 'your_phone_number_id';
$whatsapp_api_version = 'v17.0';
$whatsapp_api_url = "https://graph.facebook.com/{$whatsapp_api_version}/{$phone_number_id}/messages";

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Set up logging
function log_message($message, $type = 'info') {
    $date = date('Y-m-d H:i:s');
    $log_message = "[$date] [$type]: $message" . PHP_EOL;
    file_put_contents(__DIR__ . '/logs/whatsapp.log', $log_message, FILE_APPEND);
}

// Handle webhook verification (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Parse query parameters
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    log_message("Received verification request: mode=$mode, token=$token", 'debug');
    
    // Check if a token and mode were sent
    if (!empty($mode) && !empty($token)) {
        // Check the mode and token sent match your expectations
        if ($mode === 'subscribe' && $token === $verify_token) {
            // Respond with the challenge token from the request
            log_message('WEBHOOK_VERIFIED', 'success');
            echo $challenge;
            exit;
        } else {
            // Responds with '403 Forbidden' if verify tokens do not match
            log_message('VERIFICATION_FAILED: Token mismatch', 'error');
            http_response_code(403);
            exit;
        }
    } else {
        // Missing parameters
        log_message('MISSING_PARAMETERS in verification request', 'error');
        http_response_code(400);
        exit;
    }
}

// Handle incoming messages (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = file_get_contents('php://input');
    $body = json_decode($input, true);
    
    // Log the incoming webhook
    log_message("Received webhook: " . $input, 'debug');
    
    // Check if this is an event from a WhatsApp Business Account
    if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {
        try {
            // Iterate over each entry - there may be multiple
            foreach ($body['entry'] as $entry) {
                // Handle changes array
                foreach ($entry['changes'] as $change) {
                    // Check if this is a message
                    if ($change['field'] === 'messages') {
                        $value = $change['value'];
                        
                        // Only process messages, not statuses
                        if ($value['messaging_product'] === 'whatsapp' && isset($value['messages'])) {
                            foreach ($value['messages'] as $message) {
                                $contact = $value['contacts'][0] ?? ['profile' => ['name' => 'Customer']];
                                process_message($message, $contact);
                            }
                        }
                    }
                }
            }
            
            // Return a '200 OK' response to acknowledge receipt
            http_response_code(200);
            echo 'EVENT_RECEIVED';
            exit;
        } catch (Exception $e) {
            log_message('Error processing webhook: ' . $e->getMessage(), 'error');
            http_response_code(150000);
            exit;
        }
    } else {
        // Not from WhatsApp API
        log_message('Received non-WhatsApp webhook', 'warn');
        http_response_code(404);
        exit;
    }
}

/**
 * Process incoming WhatsApp messages
 * 
 * @param array $message The message data
 * @param array $contact The contact information
 */
function process_message($message, $contact) {
    log_message('Processing message: ' . json_encode($message), 'info');
    log_message('Contact info: ' . json_encode($contact), 'info');
    
    // Get the phone number
    $from = $message['from'];
    $response_text = "Thank you for your message! This is an automated response.";
    
    // Handle different message types
    if ($message['type'] === 'text') {
        $received_text = strtolower($message['text']['body']);
        
        // Simple response logic
        if (strpos($received_text, 'hello') !== false || strpos($received_text, 'hi') !== false) {
            $name = $contact['profile']['name'] ?? '';
            $response_text = "Hello $name! How can I help you today?";
        } elseif (strpos($received_text, 'help') !== false) {
            $response_text = "Here are some commands you can try:\n• hello\n• info\n• support";
        } elseif (strpos($received_text, 'info') !== false) {
            $response_text = "This is a WhatsApp Business API demo. You can customize it to your needs.";
        }
        
        // Reply to the message
        send_whatsapp_message($from, $response_text);
    } elseif ($message['type'] === 'image') {
        // Handle image messages
        send_whatsapp_message($from, "Thanks for the image! Unfortunately, I can only process text messages right now.");
    } elseif ($message['type'] === 'document') {
        // Handle document messages
        send_whatsapp_message($from, "I received your document, but I can only process text messages right now.");
    } else {
        // Handle other message types
        send_whatsapp_message($from, "I received your message, but I can only process text messages right now.");
    }
}

/**
 * Send a message via WhatsApp API
 * 
 * @param string $to The recipient's phone number
 * @param string $text The message text
 * @return bool Success status
 */
function send_whatsapp_message($to, $text) {
    global $whatsapp_api_url, $access_token;
    
    // Prepare the data
    $data = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => ['body' => $text]
    ];
    
    // Convert data to JSON
    $json_data = json_encode($data);
    
    // Set up curl request
    $ch = curl_init($whatsapp_api_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data)
    ]);
    
    // Execute the request
    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the result
    if ($status_code >= 200 && $status_code < 300) {
        log_message('Message sent successfully: ' . $result, 'success');
        return true;
    } else {
        log_message('Error sending message: ' . $result, 'error');
        return false;
    }
}

// If we get here, show a simple message (for direct browser access)
if (!isset($body) && $_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo 'WhatsApp Webhook Server is running!';
}