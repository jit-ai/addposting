<?php
require_once 'config/database.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE categories ADD COLUMN image VARCHAR(255) AFTER description";

if ($conn->query($sql) === TRUE) {
    echo "Image column added successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();