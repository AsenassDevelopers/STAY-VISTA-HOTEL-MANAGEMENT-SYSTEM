<?php
// Database configuration (same as booking.php)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stayvista_bookings');

// Connect to database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch booking details
$booking = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
} catch(PDOException $e) {
    die("ERROR: Could not fetch booking details.");
}

if (!$booking) {
    header("Location: booking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - StayVista</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .confirmation { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .highlight { color: #2a6496; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Confirmation</h1>
        <p>Thank you for choosing StayVista! Your booking has been confirmed.</p>
        
        <div class="confirmation">
            <h2>Booking Details</h2>
            <p><strong>Booking Reference:</strong> <span class="highlight">SV-<?php echo $booking['id']; ?></span></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . htmlspecialchars($booking['last_name'])); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
            <p><strong>Check-in:</strong> <?php echo date('F j, Y', strtotime($booking['check_in'])); ?></p>
            <p><strong>Check-out:</strong> <?php echo date('F j, Y', strtotime($booking['check_out'])); ?></p>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['room_type']); ?></p>
            <p><strong>Guests:</strong> <?php echo $booking['adults']; ?> adult(s), <?php echo $booking['children']; ?> child(ren)</p>
            <p>A confirmation email has been sent to <?php echo htmlspecialchars($booking['email']); ?>.</p>
        </div>
        
        <p>If you have any questions about your booking, please contact our reservations team at <strong>reservations@stayvista.com</strong> or call <strong>(+254) 112-876-340</strong>.</p>
        
        <p>We look forward to welcoming you to StayVista!</p>
    </div>
</body>
</html>