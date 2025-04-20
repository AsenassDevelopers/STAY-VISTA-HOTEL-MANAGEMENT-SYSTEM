-- Create the database
CREATE DATABASE IF NOT EXISTS stayvista_bookings;
USE stayvista_bookings;

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    adults INT NOT NULL,
    children INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);

-- Create rooms table (for room inventory)
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    available INT NOT NULL
);

-- Insert sample room data
INSERT INTO rooms (room_type, description, price, capacity, available) VALUES
('Premium King Room', 'Luxury king room with ocean view', 16000.00, 2, 5),
('Best King Room', 'Spacious king room for families', 12500.00, 5, 3),
('Luxury Marriot Room', 'Elegant room with premium amenities', 10000.00, 3, 4),
('Master Suite Room', 'Exclusive suite with extra space', 9000.00, 2, 2),
('Deluxe Room', 'Comfortable deluxe accommodation', 14500.00, 3, 6);

---Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE
);
-- Create users table for admin access
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Create admin user (change password before using in production)
INSERT INTO admin_users (username, password_hash, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');