<?php
session_start();
// Load environment variables from .env file if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1], " \t\n\r\0\x0B\"'");
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Lumific Boutique - Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'lumific_boutique');

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

// Auto-populate premium lighting specs for products where they are currently missing
$conn->query("UPDATE products SET wattage = '12W', beam_angle = '24°', cri = 'Ra > 90', ip_rating = 'IP20' WHERE name LIKE 'Spotboy%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '15W', beam_angle = '36°', cri = 'Ra > 92', ip_rating = 'IP20' WHERE name LIKE 'Barrel%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '8W', beam_angle = '120°', cri = 'Ra > 90', ip_rating = 'IP44' WHERE name LIKE 'Halo%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '12W', beam_angle = '120°', cri = 'Ra > 90', ip_rating = 'IP20' WHERE name LIKE 'AllRounder%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '10W', beam_angle = '24°', cri = 'Ra > 95', ip_rating = 'IP20' WHERE name LIKE 'Go Pro%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '12W', beam_angle = '45°', cri = 'Ra > 85', ip_rating = 'IP65' WHERE name LIKE 'Baylight%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '18W/m', beam_angle = '120°', cri = 'Ra > 90', ip_rating = 'IP20' WHERE name LIKE 'Iskim%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET wattage = '24W', beam_angle = '120°', cri = 'Ra > 90', ip_rating = 'IP20' WHERE name LIKE 'Enso%' AND (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");

// General fallbacks in case any generic or new products are missing metadata
$conn->query("UPDATE products SET wattage = '12W' WHERE (wattage IS NULL OR wattage = '' OR wattage = 'N/A')");
$conn->query("UPDATE products SET beam_angle = '24°' WHERE (beam_angle IS NULL OR beam_angle = '' OR beam_angle = 'N/A')");
$conn->query("UPDATE products SET cri = 'Ra > 90' WHERE (cri IS NULL OR cri = '' OR cri = 'N/A')");
$conn->query("UPDATE products SET ip_rating = 'IP20' WHERE (ip_rating IS NULL OR ip_rating = '' OR ip_rating = 'N/A')");

// Auto-update descriptions to luxury architectural lighting copy
$conn->query("UPDATE products SET description = 'An architectural masterpiece designed for dynamic galleries and high-end residential spaces. Spotboy 338 features an adjustable zoom lens with a precision-milled knurled gold accent ring, allowing you to seamlessly customize the beam angle while delivering an absolute glare-free, museum-grade light beam.' WHERE name LIKE 'Spotboy%'");
$conn->query("UPDATE products SET description = 'Forged from heavy-duty architectural-grade aluminum, the Barrel 320 delivers a pristine, ultra-minimalist cylinder aesthetic. Its high-efficiency COB engine is recessed deep within an anti-glare dark baffle, casting high-contrast architectural accents while remaining completely hidden from the direct line of sight.' WHERE name LIKE 'Barrel%'");
$conn->query("UPDATE products SET description = 'A revolutionary recessed downlight featuring Lumific\'s signature gold-plated dual-ring optics. The Halo 394 blends secondary ambient ceiling wash with a concentrated primary beam, creating a luxurious \'warm halo\' ceiling effect while eliminating the harsh shadows typical of standard downlights.' WHERE name LIKE 'Halo%'");
$conn->query("UPDATE products SET description = 'The absolute standard in luxury general ambient illumination. Featuring a signature micro-prismatic diffuser, the AllRounder 392 distributes an exceptionally soft, shadowless, and uniform warm-luxe glow across living areas, corridors, and premium lobbies, making spaces feel expansive and inviting.' WHERE name LIKE 'AllRounder%'");
$conn->query("UPDATE products SET description = 'A professional-grade deep-spot luminaire engineered for elite interior accents. Featuring a brushed gold bezel and custom-engineered spot reflectors, the Go Pro 168 projects a highly-concentrated, intensely saturated beam of light that brings out the richest textures and colors of luxury interior elements.' WHERE name LIKE 'Go Pro%'");
$conn->query("UPDATE products SET description = 'A masterpiece of weatherproof engineering, the Baylight 418 is sculpted from marine-grade anodized aluminum to withstand coastal and highly humid environments. It projects a soft, luxury downward wash, seamlessly illuminating exterior columns, premium landscape pathways, and oceanfront patios.' WHERE name LIKE 'Baylight%'");
$conn->query("UPDATE products SET description = 'An elegant, ultra-slim linear profile designed for seamless integration. The Iskim 126 features a high-density, dot-free architectural LED strip housed inside a sandblasted gold extrusion. It is the perfect choice for high-end cabinetry, floating shelves, and continuous false-ceiling cove lighting.' WHERE name LIKE 'Iskim%'");
$conn->query("UPDATE products SET description = 'A breathtaking, large-format suspended ceiling sculpture from the legendary Studio Abby series. The Enso 30 features a hand-burnished Ember Gold circular frame that radiates a warm, diffused 360-degree ambient glow, instantly transforming high-ceiling dining rooms and luxury lobbies into modern art sanctuaries.' WHERE name LIKE 'Enso%'");

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

// Ensure address column exists in users table
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT AFTER phone");

// Admin Session Helper
function checkAuth() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header("Location: index.php");
        exit;
    }
}
?>
