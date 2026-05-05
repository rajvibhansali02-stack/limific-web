<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    // Protect all admin actions except public web checkout
    if ($action !== 'web_checkout') {
        checkAuth();
    }

    if ($action == "add") {
        $name = $conn->real_escape_string($_POST['name']);
        $category = $_POST['category'];
        $price = $_POST['price'];
        $color = $conn->real_escape_string($_POST['color']);
        $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";
        $wattage = $conn->real_escape_string($_POST['wattage'] ?? '');
        $beam_angle = $conn->real_escape_string($_POST['beam_angle'] ?? '');
        $cri = $conn->real_escape_string($_POST['cri'] ?? '');
        $ip_rating = $conn->real_escape_string($_POST['ip_rating'] ?? '');

        // Check for duplicates
        $check_dup = $conn->query("SELECT id FROM products WHERE name = '$name'");
        if ($check_dup->num_rows > 0) {
            header("Location: dashboard.php?error=duplicate");
            exit;
        }

        // File Upload Handling
        $target_dir = "../images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
        $new_filename = "shop_" . $clean_name . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        $db_path = "images/" . $new_filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO products (name, category, price, color, description, wattage, beam_angle, cri, ip_rating, image_url) 
                    VALUES ('$name', '$category', '$price', '$color', '$description', '$wattage', '$beam_angle', '$cri', '$ip_rating', '$db_path')";
            
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=1");
                exit;
            } else {
                echo "Database Error: " . $conn->error;
            }
        } else {
            echo "Error: Could not upload image. Check folder permissions.";
        }
    }

    if ($action == "edit") {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $category = $_POST['category'];
        $price = $_POST['price'];
        $color = $conn->real_escape_string($_POST['color']);
        $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";
        $wattage = $conn->real_escape_string($_POST['wattage'] ?? '');
        $beam_angle = $conn->real_escape_string($_POST['beam_angle'] ?? '');
        $cri = $conn->real_escape_string($_POST['cri'] ?? '');
        $ip_rating = $conn->real_escape_string($_POST['ip_rating'] ?? '');

        // Base update query
        $sql = "UPDATE products SET name='$name', category='$category', price='$price', color='$color', description='$description', wattage='$wattage', beam_angle='$beam_angle', cri='$cri', ip_rating='$ip_rating'";

        // Handle image update if new file is uploaded
        if (isset($_FILES["image"]) && $_FILES["image"]["size"] > 0) {
            // Delete old image
            $res = $conn->query("SELECT image_url FROM products WHERE id = $id");
            if ($row = $res->fetch_assoc()) {
                $old_path = "../" . $row['image_url'];
                if (file_exists($old_path) && is_file($old_path)) {
                    unlink($old_path);
                }
            }

            // Upload new image
            $target_dir = "../images/";
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
            $new_filename = "shop_" . $clean_name . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            $db_path = "images/" . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $sql .= ", image_url='$db_path'";
            }
        }

        $sql .= " WHERE id = $id";
        
        if ($conn->query($sql)) {
            header("Location: dashboard.php?updated=1");
            exit;
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    if ($action == "delete") {
        $id = intval($_POST['id']);
        
        // Get image path to delete file too
        $res = $conn->query("SELECT image_url FROM products WHERE id = $id");
        if ($row = $res->fetch_assoc()) {
            $full_path = "../" . $row['image_url'];
            if (file_exists($full_path) && is_file($full_path)) {
                unlink($full_path);
            }
        }

        $conn->query("DELETE FROM products WHERE id = $id");
        header("Location: dashboard.php?deleted=1");
        exit;
    }

    if ($action == "delete_inquiry") {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM inquiries WHERE id = $id");
        header("Location: dashboard.php?tab=inquiries&deleted=1");
        exit;
    }

    if ($action == "add_sale") {
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone'] ?? '');
        $products_data = $_POST['products'];
        $grand_total = 0;

        foreach ($products_data as $p) {
            $p_id = intval($p['id']);
            $qty = intval($p['qty']);
            $total = floatval($p['total']);
            $grand_total += $total;

            // Fetch product name
            $p_res = $conn->query("SELECT name FROM products WHERE id = $p_id");
            $p_name = ($row = $p_res->fetch_assoc()) ? $row['name'] : "Unknown Product";

            $sql = "INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name, order_id) 
                    VALUES ($p_id, '$p_name', $qty, '$total', '$customer_name', '$order_id')";
            $conn->query($sql);
        }

        // Create Master Order Record
        $status = $_POST['order_status'] ?? 'Processing';
        $payment = $_POST['payment_status'] ?? 'Paid';
        $conn->query("INSERT INTO orders (order_id, customer_name, customer_phone, total_amount, order_status, payment_status) 
                      VALUES ('$order_id', '$customer_name', '$customer_phone', '$grand_total', '$status', '$payment')");
        
        header("Location: dashboard.php?tab=sales&success=sale");
        exit;
    }

    if ($action == "update_order") {
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $status = $conn->real_escape_string($_POST['order_status']);
        $payment = $conn->real_escape_string($_POST['payment_status']);

        $conn->query("UPDATE orders SET order_status='$status', payment_status='$payment' WHERE order_id='$order_id'");
        header("Location: dashboard.php?tab=sales&updated=order");
        exit;
    }

    if ($action == "delete_sale") {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM sales WHERE id = $id");
        header("Location: dashboard.php?tab=sales&deleted=1");
        exit;
    }

    if ($action == "delete_order") {
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $conn->query("DELETE FROM orders WHERE order_id = '$order_id'");
        $conn->query("DELETE FROM sales WHERE order_id = '$order_id'");
        header("Location: dashboard.php?tab=sales&deleted=order");
        exit;
    }

    if ($action == "web_checkout") {
        header('Content-Type: application/json');
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $cart = json_decode($_POST['cart'], true);
        
        $user_id = $_SESSION['user_id'] ?? null;
        $customer_name = "Web Customer";
        $customer_phone = "";

        if ($user_id) {
            $user_res = $conn->query("SELECT name, phone FROM users WHERE id = $user_id");
            if ($u = $user_res->fetch_assoc()) {
                $customer_name = $conn->real_escape_string($u['name']);
                $customer_phone = $conn->real_escape_string($u['phone']);
            }
        }

        $grand_total = 0;
        foreach ($cart as $item) {
            $p_id = intval($item['id']);
            $qty = intval($item['qty']);
            $p_name = $conn->real_escape_string($item['name']);
            $total = floatval($item['price']) * $qty;
            $grand_total += $total;

            $sql = "INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name, order_id) 
                    VALUES ($p_id, '$p_name', $qty, '$total', '$customer_name', '$order_id')";
            $conn->query($sql);
        }

        // Create Master Order Record for Web Checkout with user_id
        $uid_sql = $user_id ? $user_id : "NULL";
        $conn->query("INSERT INTO orders (order_id, user_id, customer_name, customer_phone, total_amount, order_status, payment_status, payment_method) 
                      VALUES ('$order_id', $uid_sql, '$customer_name', '$customer_phone', '$grand_total', 'Pending', 'Unpaid', 'Web Checkout')");

        echo json_encode(['success' => true]);
        exit;
    }
    if ($action == "edit_user") {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);

        $sql = "UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id = $id";
        
        if ($conn->query($sql)) {
            header("Location: dashboard.php?tab=customers&updated=user");
            exit;
        } else {
            echo "Error updating user: " . $conn->error;
        }
    }

    if ($action == "delete_user") {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM users WHERE id = $id");
        header("Location: dashboard.php?tab=customers&deleted=user");
        exit;
    }
}
?>
