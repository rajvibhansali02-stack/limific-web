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
        $description = $conn->real_escape_string($_POST['description']);

        // Handle Image Upload
        $target_dir = "../images/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $db_path = "images/" . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO products (name, category, price, color, description, image_url, badge) 
                    VALUES ('$name', '$category', '$price', '$color', '$description', '$db_path', '$badge')";
            
            if ($conn->query($sql)) {
                header("Location: dashboard.php?success=1");
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    if ($action == "delete") {
        $id = $_POST['id'];
        
        // Get image path to delete file too
        $res = $conn->query("SELECT image_url FROM products WHERE id = $id");
        if ($row = $res->fetch_assoc()) {
            unlink("../" . $row['image_url']);
        }

        $conn->query("DELETE FROM products WHERE id = $id");
        header("Location: dashboard.php?deleted=1");
    }
}
?>
