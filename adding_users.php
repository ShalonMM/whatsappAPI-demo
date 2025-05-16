/*
 * This script adds a new user to the database with a hashed password.
 * Make sure to run this script only once to avoid duplicate entries.
 */

<?php
require 'db.php';

$username = 'admin';
$password = 'password123'; // Use a strong password in real scenarios
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
$stmt->execute(['username' => $username, 'password' => $hashed_password]);

echo "User added successfully";
?>