<?php
require_once 'config.php';
checkAuth();

// --- DATA FETCHING ---
// 1. Fetch Products
$prod_res = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $prod_res->fetch_all(MYSQLI_ASSOC);

// 2. Fetch & Parse Inquiries from Log
$inquiries = [];
if (file_exists('../inquiries.log')) {
    $log_content = file_get_contents('../inquiries.log');
    $entries = explode("------------------------------------------", $log_content);
    foreach (array_reverse($entries) as $entry) {
        if (trim($entry) == "") continue;
        
        $lines = explode("\n", trim($entry));
        $inquiry = [
            'date' => str_replace(['[', '] NEW INQUIRY'], '', $lines[0]),
            'email' => str_replace('From: ', '', $lines[1] ?? ''),
            'interest' => str_replace('Interest: ', '', $lines[2] ?? ''),
            'message' => str_replace('Message: ', '', $lines[3] ?? '')
        ];
        $inquiries[] = $inquiry;
    }
}

// 3. Analytics Calculation
$cat_counts = [];
foreach ($products as $p) {
    $cat = $p['category'];
    $cat_counts[$cat] = ($cat_counts[$cat] ?? 0) + 1;
}

$current_tab = $_GET['tab'] ?? 'products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Lumific Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a0a0a;
            --sidebar: #111;
            --accent: #E2B04E;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --success: #4CAF50;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #fff; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .logo { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 600; letter-spacing: 3px; margin-bottom: 50px; color: var(--accent); }

        .nav-item {
            padding: 15px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item:hover, .nav-item.active { background: var(--glass); color: #fff; }
        .nav-item i { width: 20px; text-align: center; }

        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 50px; }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-family: 'Outfit', sans-serif; font-weight: 400; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--glass); border: 1px solid var(--border); padding: 30px; border-radius: 20px; text-align: left; }
        .stat-card h3 { color: rgba(255,255,255,0.4); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: 600; }

        /* Content Sections */
        .content-section { background: var(--glass); border: 1px solid var(--border); border-radius: 24px; overflow: hidden; margin-bottom: 30px; }
        .section-header { padding: 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 20px 25px; color: rgba(255,255,255,0.4); font-weight: 400; font-size: 0.85rem; border-bottom: 1px solid var(--border); }
        td { padding: 20px 25px; border-bottom: 1px solid var(--border); vertical-align: middle; }

        .prod-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; background: #222; }
        .badge-ui { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .badge-popular { background: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .badge-new { background: rgba(33, 150, 243, 0.1); color: #2196F3; }

        .btn-add { background: var(--accent); color: #000; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(226, 176, 78, 0.2); }

        .action-btn { background: none; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 1.1rem; transition: 0.3s; }
        .action-btn:hover { color: #f44336; }

        /* Inquiry Card */
        .inquiry-row:hover { background: rgba(255,255,255,0.02); }
        .inquiry-msg { font-size: 0.9rem; color: rgba(255,255,255,0.7); max-width: 400px; line-height: 1.5; }

        /* Analytics Chart Simulation */
        .chart-container { padding: 40px; display: flex; flex-direction: column; gap: 30px; }
        .bar-group { display: flex; flex-direction: column; gap: 10px; }
        .bar-label { display: flex; justify-content: space-between; font-size: 0.9rem; color: rgba(255,255,255,0.6); }
        .bar-outer { height: 12px; background: rgba(255,255,255,0.05); border-radius: 6px; overflow: hidden; }
        .bar-inner { height: 100%; background: var(--accent); border-radius: 6px; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: #111; border: 1px solid var(--border); width: 100%; max-width: 600px; border-radius: 24px; padding: 40px; position: relative; }
        .close-modal { position: absolute; top: 25px; right: 25px; color: rgba(255,255,255,0.3); cursor: pointer; font-size: 1.5rem; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { margin-bottom: 20px; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 8px; color: rgba(255,255,255,0.6); font-size: 0.85rem; }
        input, select, textarea { width: 100%; background: #1a1a1a; border: 1px solid var(--border); padding: 12px 15px; border-radius: 10px; color: #fff; font-family: inherit; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">LUMIFIC</div>
        <a href="?tab=products" class="nav-item <?php echo $current_tab == 'products' ? 'active' : ''; ?>"><i class="fa-solid fa-cube"></i> Products</a>
        <a href="?tab=inquiries" class="nav-item <?php echo $current_tab == 'inquiries' ? 'active' : ''; ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a>
        <a href="?tab=analytics" class="nav-item <?php echo $current_tab == 'analytics' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <div style="margin-top: auto;">
            <a href="logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <?php if ($current_tab == 'products'): ?>
            <div class="header">
                <h1>Product Management</h1>
                <button class="btn-add" onclick="openModal()"><i class="fa-solid fa-plus"></i> Add Product</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Inventory</h3>
                    <div class="value"><?php echo count($products); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Collection Value</h3>
                    <div class="value">Premium</div>
                </div>
                <div class="stat-card">
                    <h3>Live on Site</h3>
                    <div class="value"><?php echo count($products); ?></div>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h3>Live Inventory</h3>
                    <span style="color: rgba(255,255,255,0.4); font-size: 0.9rem;"><?php echo count($products); ?> Items</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Product</th><th>Category</th><th>Price</th><th>Badge</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr class="inquiry-row">
                            <td style="display: flex; align-items: center; gap: 15px;">
                                <img src="../<?php echo $p['image_url']; ?>" class="prod-img">
                                <div>
                                    <div style="font-weight: 600;"><?php echo $p['name']; ?></div>
                                    <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4);"><?php echo $p['color']; ?></div>
                                </div>
                            </td>
                            <td><span style="text-transform: capitalize;"><?php echo $p['category']; ?></span></td>
                            <td>₹<?php echo number_format($p['price']); ?></td>
                            <td><span class="badge-ui badge-<?php echo strtolower($p['badge']); ?>"><?php echo $p['badge']; ?></span></td>
                            <td>
                                <form action="actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="action-btn" onclick="return confirm('Delete this product?')"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab == 'inquiries'): ?>
            <div class="header">
                <h1>Customer Inquiries</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Messages</h3>
                    <div class="value"><?php echo count($inquiries); ?></div>
                </div>
                <div class="stat-card">
                    <h3>New Leads</h3>
                    <div class="value"><?php echo count($inquiries); ?></div>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header"><h3>Recent Activity</h3></div>
                <table>
                    <thead>
                        <tr><th>Date</th><th>Client Email</th><th>Product Interest</th><th>Message</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($inquiries as $iq): ?>
                        <tr class="inquiry-row">
                            <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?php echo $iq['date']; ?></td>
                            <td style="font-weight: 600;"><?php echo $iq['email']; ?></td>
                            <td><span class="badge-ui" style="background: rgba(226,176,78,0.1); color: var(--accent);"><?php echo $iq['interest']; ?></span></td>
                            <td class="inquiry-msg"><?php echo $iq['message']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($inquiries)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 50px; opacity: 0.3;">No inquiries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab == 'analytics'): ?>
            <div class="header">
                <h1>Boutique Analytics</h1>
            </div>

            <div class="content-section">
                <div class="section-header"><h3>Category Distribution</h3></div>
                <div class="chart-container">
                    <?php 
                    $max = count($products) > 0 ? max($cat_counts) : 1;
                    foreach ($cat_counts as $cat => $count): 
                        $percent = ($count / $max) * 100;
                    ?>
                    <div class="bar-group">
                        <div class="bar-label">
                            <span style="text-transform: capitalize;"><?php echo $cat; ?></span>
                            <span><?php echo $count; ?> Products</span>
                        </div>
                        <div class="bar-outer">
                            <div class="bar-inner" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Add New Product</h2>
            <form action="actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="input-group full-width"><label>Product Name</label><input type="text" name="name" required></div>
                    <div class="input-group"><label>Category</label><select name="category"><option value="tracklights">Tracklights</option><option value="downlights">Downlights</option><option value="spots">Spots</option><option value="outdoor">Outdoor</option><option value="profiles">Profiles</option><option value="ceiling">Studio Abby</option></select></div>
                    <div class="input-group"><label>Price (INR)</label><input type="number" name="price" required></div>
                    <div class="input-group"><label>Finish/Color</label><input type="text" name="color"></div>
                    <div class="input-group"><label>Badge</label><select name="badge"><option value="New">New</option><option value="Popular">Popular</option><option value="Best Seller">Best Seller</option><option value="Classic">Classic</option></select></div>
                    <div class="input-group full-width"><label>Product Image</label><input type="file" name="image" accept="image/*" required></div>
                </div>
                <button type="submit" class="btn-add" style="width: 100%; margin-top: 10px;">Save Product</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('productModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('productModal').style.display = 'none'; }
    </script>
</body>
</html>
