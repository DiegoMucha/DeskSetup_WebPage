<?php
// Database connection details
$host = '172.171.240.171';
$port = '5432';
$dbname = 'desksetup';
$user = 'onworldadmin';
$password = 'admin123';

// Connection string
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>