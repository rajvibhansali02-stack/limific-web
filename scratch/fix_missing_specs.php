<?php
require_once 'admin/config.php';

$updates = [
    4 => ['watt' => '12W', 'beam' => '30°', 'cri' => 'Ra > 80', 'ip' => 'IP68'],
    5 => ['watt' => 'N/A', 'beam' => 'N/A', 'cri' => 'N/A', 'ip' => 'IP20'],
    6 => ['watt' => '10W', 'beam' => '90°', 'cri' => 'Ra > 90', 'ip' => 'IP44'],
    7 => ['watt' => '15W', 'beam' => '24°', 'cri' => 'Ra > 92', 'ip' => 'IP20'],
    8 => ['watt' => '20W', 'beam' => '36°', 'cri' => 'Ra > 95', 'ip' => 'IP20'],
    9 => ['watt' => '8W', 'beam' => '60°', 'cri' => 'Ra > 90', 'ip' => 'IP20'],
    10 => ['watt' => '12W', 'beam' => '45°', 'cri' => 'Ra > 90', 'ip' => 'IP20']
];

foreach ($updates as $id => $s) {
    $sql = "UPDATE products SET wattage = '{$s['watt']}', beam_angle = '{$s['beam']}', cri = '{$s['cri']}', ip_rating = '{$s['ip']}' WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Updated ID $id\n";
    } else {
        echo "Error updating ID $id: " . $conn->error . "\n";
    }
}

// Also check for any other null/empty ones and give them defaults if they exist
$conn->query("UPDATE products SET wattage = '12W' WHERE wattage IS NULL OR wattage = ''");
$conn->query("UPDATE products SET beam_angle = '36°' WHERE beam_angle IS NULL OR beam_angle = ''");
$conn->query("UPDATE products SET cri = 'Ra > 90' WHERE cri IS NULL OR cri = ''");
$conn->query("UPDATE products SET ip_rating = 'IP20' WHERE ip_rating IS NULL OR ip_rating = ''");

echo "Bulk update completed.";
?>
