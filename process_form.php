<?php
session_start(); // Start session for feedback messages

// ✅ Step 1: Connect to the DB
include 'config.php'; // Include your database configuration file

// ✅ Step 2: Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['message-name']);
    $email = mysqli_real_escape_string($conn, $_POST['message-email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // ✅ Step 3: Validate input
    if (!empty($name) && !empty($email) && !empty($message)) {
        $sql = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Message sent successfully!";
        } else {
            $_SESSION['error'] = "Oops! Database error.";
        }
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    header("Location: contact.php"); // Redirect to a success page
    exit();
}

$conn->close();
?>
