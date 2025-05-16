<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Business API Demo</title>
</head>
<body>
    <h1>WhatsApp Business API Demo</h1>
    <p><a href="logout.php">Logout</a></p>

    <form id="message-form">
        <label>Recipient WhatsApp Number (+1234567890): 
            <input type="text" id="to" name="to">
        </label><br>
        <label>Message: 
            <textarea id="body" name="body"></textarea>
        </label><br>
        <button type="submit">Send Message</button>
    </form>

    <h2>Received Messages</h2>
    <div id="messages"></div>

    <script>
        // Send message
        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('send_message_meta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'to': document.getElementById('to').value,
                    'body': document.getElementById('body').value
                })
            })
            .then(response => response.json())
            .then(data => alert(data.success ? 'Message sent!' : 'Error: ' + data.error));
        });

        // Fetch messages
        function loadMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(messages => {
                    document.getElementById('messages').innerHTML = messages.map(m => 
                        `<p>From: ${m.from}, Message: ${m.body}, Time: ${m.timestamp}</p>`
                    ).join('');
                });
        }
        loadMessages();
        setInterval(loadMessages, 5000); // Refresh every 5 seconds
    </script>
</body>
</html>