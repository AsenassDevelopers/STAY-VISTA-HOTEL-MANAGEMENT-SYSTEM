<?php
// Database configuration
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

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $errors = [];
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $room_type = $_POST['room_type'];
    $adults = (int)$_POST['adults'];
    $children = (int)$_POST['children'];
    
    // Basic validation
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($check_in) || empty($check_out)) $errors[] = "Check-in and check-out dates are required";
    if ($check_in >= $check_out) $errors[] = "Check-out date must be after check-in date";
    if (empty($room_type)) $errors[] = "Room type is required";
    if ($adults < 1) $errors[] = "At least one adult is required";
    
    // Check room availability
    if (empty($errors)) {
        try {
            // Check if room is available
            $stmt = $pdo->prepare("SELECT available FROM rooms WHERE room_type = ?");
            $stmt->execute([$room_type]);
            $room = $stmt->fetch();
            
            if (!$room || $room['available'] < 1) {
                $errors[] = "Selected room type is not available";
            }
        } catch(PDOException $e) {
            $errors[] = "Error checking room availability";
        }
    }
    
    // If no errors, save booking
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings 
                (first_name, last_name, email, phone, check_in, check_out, room_type, adults, children) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $first_name,
                $last_name,
                $email,
                $phone,
                $check_in,
                $check_out,
                $room_type,
                $adults,
                $children,
            ]);
            
            // Get booking ID
            $booking_id = $pdo->lastInsertId();
            
            // Redirect to success page
            header("Location: booking-success.php?id=".$booking_id);
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Error saving booking: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>StayVista - Book Your Stay</title>
    
    <!-- Favicon -->
    <link rel="icon" href="./img/core-img/favicon.png">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Custom Booking Form Styles -->
    <style>
        /* Main booking section styles */
        .booking-section {
            padding: 100px 0;
            background-color: #f8f9fa;
        }
        
        .booking-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 50px;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .section-heading {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-heading h2 {
            font-size: 42px;
            margin-bottom: 15px;
            color: #2a2a2a;
        }
        
        .section-heading h6 {
            color: #fbb710;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0;
        }
        
        /* Form styles */
        .booking-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2a2a2a;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            height: 50px;
            padding: 0 20px;
            border: 1px solid #ebebeb;
            border-radius: 3px;
            background-color: #f8f9fa;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #fbb710;
            background-color: #fff;
            outline: none;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        /* Button styles */
        .btn-submit {
            grid-column: 1 / -1;
            background-color: #fbb710;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 30px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #2a2a2a;
        }
        
        /* Error message styles */
        .error-messages {
            grid-column: 1 / -1;
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .error-messages ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .error-messages li {
            color: #f44336;
            margin-bottom: 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .booking-form {
                grid-template-columns: 1fr;
            }
            
            .booking-form-container {
                padding: 30px;
            }
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <!-- /Preloader -->

    <!-- Header Area Start -->
    <header class="header-area">
        <!-- Top Header Area Start -->
        <div class="top-header-area">
            <div class="container">
                <div class="row">
                    <div class="col-6">
                        <div class="top-header-content">
                            <a href="#"><i class="icon_phone"></i> <span>(+254) 112-876-340</span></a>
                            <a href="#"><i class="icon_mail"></i> <span>reservations@stayvista.com</span></a>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="top-header-content">
                            <!-- Top Social Area -->
                            <div class="top-social-area ml-auto">
                                <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                                <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                                <a href="#"><i class="fa fa-tripadvisor" aria-hidden="true"></i></a>
                                <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Top Header Area End -->

        <!-- Main Header Start -->
        <div class="main-header-area">
            <div class="classy-nav-container breakpoint-off">
                <div class="container">
                    <!-- Classy Menu -->
                    <nav class="classy-navbar justify-content-between" id="robertoNav">
                        <!-- Logo -->
                        <a class="nav-brand" href="index.html"><img src="./img/core-img/log.png" alt=""></a>
                        <!-- Navbar Toggler -->
                        <div class="classy-navbar-toggler">
                            <span class="navbarToggler"><span></span><span></span><span></span></span>
                        </div>
                        <!-- Menu -->
                        <div class="classy-menu">
                            <!-- Menu Close Button -->
                            <div class="classycloseIcon">
                                <div class="cross-wrap"><span class="top"></span><span class="bottom"></span></div>
                            </div>
                            <!-- Nav Start -->
                            <div class="classynav">
                                <ul id="nav">
                                    <li><a href="./index.html">Home</a></li>
                                    <li><a href="./room.html">Rooms</a></li>
                                    <li><a href="./about.html">About Us</a></li>
                                    <li><a href="#">Pages</a>
                                        <!-- <ul class="dropdown">
                                            <li><a href="./single-room.html">- Single Rooms</a></li>
                                            <li><a href="./single-blog.html">- Single Blog</a></li>
                                        </ul> -->
                                    </li>
                                    <li><a href="./blog.html">Blog</a></li>
                                    <li><a href="./contact.php">Contact</a></li>
                                </ul>
                                <!-- Book Now -->
                            </div>
                            <!-- Nav End -->
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- Header Area End -->

    <!-- Booking Form Section Start -->
    <section class="booking-section">
        <div class="container">
            <div class="booking-form-container">
                <div class="section-heading wow fadeInUp" data-wow-delay="100ms">
                    <h6>Reservation</h6>
                    <h2>Book Your Stay</h2>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="booking.php" method="post" class="booking-form">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check_in">Check-in Date *</label>
                        <input type="date" id="check_in" name="check_in" required
                               value="<?php echo isset($check_in) ? htmlspecialchars($check_in) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check_out">Check-out Date *</label>
                        <input type="date" id="check_out" name="check_out" required
                               value="<?php echo isset($check_out) ? htmlspecialchars($check_out) : ''; ?>">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="room_type">Room Type *</label>
                        <select id="room_type" name="room_type" required>
                            <option value="">-- Select Room --</option>
                            <option value="Premium King Room" <?php echo (isset($room_type) && $room_type == 'Premium King Room') ? 'selected' : ''; ?>>Premium King Room (16,000 KES/day)</option>
                            <option value="Best King Room" <?php echo (isset($room_type) && $room_type == 'Best King Room') ? 'selected' : ''; ?>>Best King Room (12,500 KES/day)</option>
                            <option value="Luxury Marriot Room" <?php echo (isset($room_type) && $room_type == 'Luxury Marriot Room') ? 'selected' : ''; ?>>Luxury Marriot Room (10,000 KES/day)</option>
                            <option value="Master Suite Room" <?php echo (isset($room_type) && $room_type == 'Master Suite Room') ? 'selected' : ''; ?>>Master Suite Room (9,000 KES/day)</option>
                            <option value="Deluxe Room" <?php echo (isset($room_type) && $room_type == 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room (14,500 KES/day)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="adults">Adults *</label>
                        <select id="adults" name="adults" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo (isset($adults) && $adults == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="children">Children</label>
                        <select id="children" name="children">
                            <?php for ($i = 0; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo (isset($children) && $children == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit">Complete Booking</button>
                </form>
            </div>
        </div>
    </section>
    <!-- Booking Form Section End -->

    <!-- Footer Area Start -->
    <footer class="footer-area section-padding-80-0">
        <!-- Main Footer Area -->
        <div class="main-footer-area">
            <div class="container">
                <div class="row align-items-baseline justify-content-between">
                    <!-- Single Footer Widget Area -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="single-footer-widget mb-80">
                            <!-- Footer Logo -->
                            <a href="#" class="footer-logo"><img src="img/core-img/logo2.png" alt=""></a>
                            <h4>(+254) 112-876-340</h4>
                            <span>reservations@stayvista.com</span>
                            <span>856 WestLand Extension Apt. 356, Nairobi Kenya</span>
                        </div>
                    </div>

                    <!-- Single Footer Widget Area -->
                    <div class="col-12 col-sm-4 col-lg-2">
                        <div class="single-footer-widget mb-80">
                            <!-- Widget Title -->
                            <h5 class="widget-title">Links</h5>
                            <!-- Footer Nav -->
                            <ul class="footer-nav">
                                <li><a href="index.html"><i class="fa fa-caret-right" aria-hidden="true"></i>Home </a></li>
                                <li><a href="about.html"><i class="fa fa-caret-right" aria-hidden="true"></i> About Us</a></li>
                                <li><a href="room.html"><i class="fa fa-caret-right" aria-hidden="true"></i> Our Room</a></li>
                                <li><a href="blog.html"><i class="fa fa-caret-right" aria-hidden="true"></i> News</a></li>
                                <li><a href="contact.php"><i class="fa fa-caret-right" aria-hidden="true"></i> Contact</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Single Footer Widget Area -->
                    <div class="col-12 col-sm-8 col-lg-4">
                        <div class="single-footer-widget mb-80">
                            <!-- Widget Title -->
                            <h5 class="widget-title">Subscribe Newsletter</h5>
                            <span>Subscribe our newsletter gor get notification about new updates.</span>
                            <!-- Newsletter Form -->
                            <form action="index.html" class="nl-form">
                                <input type="email" class="form-control" placeholder="Enter your email...">
                                <button type="submit"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copywrite Area -->
        <div class="container">
            <div class="copywrite-content">
                <div class="row align-items-center">
                    <div class="col-12 col-md-8">
                        <!-- Copywrite Text -->
                        <div class="copywrite-text">
                            <p>Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <!-- Social Info -->
                        <div class="social-info">
                            <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                            <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                            <a href="#"><i class="fa fa-linkedin" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Area End -->

    <!-- **** All JS Files ***** -->
    <!-- jQuery 2.2.4 -->
    <script src="js/jquery.min.js"></script>
    <!-- Popper -->
    <script src="js/popper.min.js"></script>
    <!-- Bootstrap -->
    <script src="js/bootstrap.min.js"></script>
    <!-- All Plugins -->
    <script src="js/roberto.bundle.js"></script>
    <!-- Active -->
    <script src="js/default-assets/active.js"></script>
</body>
</html>