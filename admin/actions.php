<?php
require_once 'config.php';
checkAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "add") {
        $name = $conn->real_escape_string($_POST['name']);
        $category = $_POST['category'];
        $price = $_POST['price'];
        $color = $conn->real_escape_string($_POST['color']);
        $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";

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
            $sql = "INSERT INTO products (name, category, price, color, description, image_url) 
                    VALUES ('$name', '$category', '$price', '$color', '$description', '$db_path')";
            
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

        // Base update query
        $sql = "UPDATE products SET name='$name', category='$category', price='$price', color='$color', description='$description'";

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
        $products_data = $_POST['products'];

        foreach ($products_data as $p) {
            $p_id = intval($p['id']);
            $qty = intval($p['qty']);
            $total = $conn->real_escape_string($p['total']);

            // Fetch product name
            $p_res = $conn->query("SELECT name FROM products WHERE id = $p_id");
            $p_name = ($row = $p_res->fetch_assoc()) ? $row['name'] : "Unknown Product";

            $sql = "INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name, order_id) 
                    VALUES ($p_id, '$p_name', $qty, '$total', '$customer_name', '$order_id')";
            $conn->query($sql);
        }
        
        header("Location: dashboard.php?tab=sales&success=sale");
        exit;
    }

    if ($action == "delete_sale") {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM sales WHERE id = $id");
        header("Location: dashboard.php?tab=sales&deleted=1");
        exit;
    }

    if ($action == "web_checkout") {
        header('Content-Type: application/json');
        $order_id = $conn->real_escape_string($_POST['order_id']);
        $cart = json_decode($_POST['cart'], true);
        $customer_name = "Web Customer"; // Default for now

        foreach ($cart as $item) {
            $p_id = intval($item['id']);
            $qty = intval($item['qty']);
            $p_name = $conn->real_escape_string($item['name']);
            $total = $item['price'] * $qty;

            $sql = "INSERT INTO sales (product_id, product_name, quantity, total_amount, customer_name, order_id) 
                    VALUES ($p_id, '$p_name', $qty, '$total', '$customer_name', '$order_id')";
            $conn->query($sql);
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
?>
