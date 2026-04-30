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
        $badge = $_POST['badge'];
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
            $sql = "INSERT INTO products (name, category, price, color, description, image_url, badge) 
                    VALUES ('$name', '$category', '$price', '$color', '$description', '$db_path', '$badge')";
            
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
        $badge = $_POST['badge'];
        $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : "";

        // Base update query
        $sql = "UPDATE products SET name='$name', category='$category', price='$price', color='$color', badge='$badge', description='$description'";

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
}
?>
