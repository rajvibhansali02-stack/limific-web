<?php
require_once 'admin/config.php';

$products = [
    [
        'name' => 'Aero 500 Architectural Disk',
        'category' => 'Surface',
        'price' => 15500,
        'color' => 'Textured White',
        'wattage' => '18W / 24W',
        'beam_angle' => '120°',
        'cri' => 'Ra > 92',
        'ip_rating' => 'IP44',
        'description' => 'A minimalist circular surface-mounted luminaire. Features a deep-recessed light source for anti-glare illumination and a soft-edge glow that transforms architectural ceilings.',
        'image_url' => 'images/aero-500.webp'
    ],
    [
        'name' => 'Pathfinder 60 Garden Bollard',
        'category' => 'Outdoor',
        'price' => 12800,
        'color' => 'Matte Black',
        'wattage' => '7W / 10W',
        'beam_angle' => '360°',
        'cri' => 'Ra > 85',
        'ip_rating' => 'IP65',
        'description' => 'Precision-engineered landscape lighting. Designed to withstand harsh environments while providing a perfect 360-degree ground wash for pathways and luxury gardens.',
        'image_url' => 'images/pathfinder-60.webp'
    ],
    [
        'name' => 'Lumina Magnetic Track Spot',
        'category' => 'Magnetic Systems',
        'price' => 8900,
        'color' => 'Deep Midnight Black',
        'wattage' => '12W',
        'beam_angle' => '24° / 36°',
        'cri' => 'Ra > 95',
        'ip_rating' => 'IP20',
        'description' => 'Ultra-flexible magnetic track spotlight. Features a tool-free "Click & Slide" mechanism for instant repositioning. Ideal for highlighting artwork or architectural textures.',
        'image_url' => 'images/lumina-track.webp'
    ],
    [
        'name' => 'Cylindro Masterpiece Pendant',
        'category' => 'Ceiling Masterpieces',
        'price' => 42000,
        'color' => 'Brushed Champagne Gold',
        'wattage' => '30W',
        'beam_angle' => 'Dual Emission',
        'cri' => 'Ra > 90',
        'ip_rating' => 'IP20',
        'description' => 'A luxury statement pendant crafted from aero-grade aluminum. Provides a sophisticated balance of direct task lighting and indirect ceiling ambiance for high-end dining or lobbies.',
        'image_url' => 'images/cylindro.webp'
    ]
];

foreach ($products as $p) {
    $name = $conn->real_escape_string($p['name']);
    $cat = $conn->real_escape_string($p['category']);
    $price = $p['price'];
    $color = $conn->real_escape_string($p['color']);
    $watt = $conn->real_escape_string($p['wattage']);
    $beam = $conn->real_escape_string($p['beam_angle']);
    $cri = $conn->real_escape_string($p['cri']);
    $ip = $conn->real_escape_string($p['ip_rating']);
    $desc = $conn->real_escape_string($p['description']);
    $img = $conn->real_escape_string($p['image_url']);

    $sql = "INSERT INTO products (name, category, price, color, wattage, beam_angle, cri, ip_rating, description, image_url) 
            VALUES ('$name', '$cat', $price, '$color', '$watt', '$beam', '$cri', '$ip', '$desc', '$img')";
    
    if ($conn->query($sql)) {
        echo "Added: $name\n";
    } else {
        echo "Error adding $name: " . $conn->error . "\n";
    }
}
?>
