<?php
try {
    // Connect to SQLite database in the same directory
    $db = new PDO('sqlite:' . __DIR__ . '/users.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create a users table if it doesn’t exist
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT
    )");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>