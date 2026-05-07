<?php
require_once 'admin/config.php';
$res = $conn->query("SELECT * FROM products");
while($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Watt: '{$row['wattage']}' | Beam: '{$row['beam_angle']}' | CRI: '{$row['cri']}' | IP: '{$row['ip_rating']}'\n";
}
?>
