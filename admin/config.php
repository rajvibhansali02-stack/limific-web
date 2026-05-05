<?php
// Lumific Boutique - Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lumific_boutique');

// Establish Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    $conn->select_db(DB_NAME);
} else {
    die("Error creating database: " . $conn->error);
}

// Product Table Setup - Added professional lighting metadata
$table_sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2),
    color VARCHAR(100),
    description TEXT,
    wattage VARCHAR(50),
    beam_angle VARCHAR(50),
    cri VARCHAR(20),
    ip_rating VARCHAR(20),
    image_url VARCHAR(255),
    badge VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($table_sql)) {
    die("Error creating table: " . $conn->error);
}

// Ensure new columns exist for existing installations
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS wattage VARCHAR(50) AFTER description");
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS beam_angle VARCHAR(50) AFTER wattage");
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS cri VARCHAR(20) AFTER beam_angle");
$conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS ip_rating VARCHAR(20) AFTER cri");

// Orders Table Setup - NEW for status and payment tracking
$orders_sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(20),
    total_amount DECIMAL(12, 2),
    order_status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Unpaid', 'Paid', 'Refunded') DEFAULT 'Unpaid',
    payment_method VARCHAR(50) DEFAULT 'Manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($orders_sql)) {
    die("Error creating orders table: " . $conn->error);
}

// Inquiries Table Setup
$inquiries_sql = "CREATE TABLE IF NOT EXISTS inquiries (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    product VARCHAR(255),
    message TEXT,
    status ENUM('new', 'contacted', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($inquiries_sql)) {
    die("Error creating inquiries table: " . $conn->error);
}

// Sales Table Setup (Items within an order)
$sales_sql = "CREATE TABLE IF NOT EXISTS sales (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11),
    product_name VARCHAR(255),
    quantity INT(11) DEFAULT 1,
    total_amount DECIMAL(12, 2),
    customer_name VARCHAR(255),
    order_id VARCHAR(50),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ";

if (!$conn->query($sales_sql)) {
    die("Error creating sales table: " . $conn->error);
}

// Admin Session Helper
session_start();
function checkAuth() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header("Location: index.php");
        exit;
    }
}
?>
