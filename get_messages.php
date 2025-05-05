//Retrieves stored messages for display.

<?php
header('Content-Type: application/json');
echo file_get_contents('messages.json');
?>