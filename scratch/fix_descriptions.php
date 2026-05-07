<?php
require_once 'admin/config.php';

$descriptions = [
    1 => "A signature circular surface luminaire featuring a deep-recessed LED core for superior visual comfort. Designed to create a floating halo effect on minimalist ceilings.",
    2 => "Professional-grade landscape bollard light with a 360-degree radial wash. Crafted from anti-corrosive aluminum to provide elegant wayfinding for high-end residential pathways.",
    3 => "High-power architectural inground uplight designed for wall-washing and highlighting large trees. Features a marine-grade stainless steel faceplate and precision-angled optics.",
    4 => "Fully submersible IP68 spotlight designed for fountains and luxury pools. Engineered with high-quality seals and heat-dissipating housing for long-term underwater performance.",
    5 => "Anti-glare optical accessory designed for the Lumific spotlight range. Reduces spill light and enhances visual comfort by shielding the light source from direct view.",
    6 => "Architectural wall-mounted sconce providing a soft, indirect glow. Ideal for corridors and transitional spaces where a minimalist, flush-to-wall aesthetic is required.",
    7 => "Precision track spotlight with a high-performance COB engine. Features a dual-axis rotation for maximum flexibility in retail and residential gallery environments.",
    8 => "A heavy-duty technical spotlight with a focused, long-throw beam. Perfect for high-ceiling architectural voids and dramatic accent lighting in large-scale interiors.",
    9 => "Ultra-trim recessed downlight with a vanishing edge design. Blends seamlessly into modern gypsum ceilings for a clean, architectural lighting look.",
    10 => "The essential architectural downlight. Offers a versatile balance of efficiency and color quality, making it the perfect choice for consistent illumination across luxury residences."
];

foreach ($descriptions as $id => $desc) {
    $safe_desc = $conn->real_escape_string($desc);
    $sql = "UPDATE products SET description = '$safe_desc' WHERE id = $id";
    if ($conn->query($sql)) {
        echo "Updated ID $id description.\n";
    } else {
        echo "Error updating ID $id: " . $conn->error . "\n";
    }
}

echo "Description update completed.";
?>
