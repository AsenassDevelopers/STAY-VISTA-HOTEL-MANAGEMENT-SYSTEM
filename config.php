<?php
// Database configuratio

$host = 'localhost';
$user = 'root';
$password = ''; // Or your DB password
$database = 'stayvista_bookings'; // Replace this with your actual DB name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

?>