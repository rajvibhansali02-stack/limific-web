<?php
require_once 'config.php';
checkAuth();

// --- DATA FETCHING ---
// 1. Fetch Products
$prod_res = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $prod_res->fetch_all(MYSQLI_ASSOC);

// 2. Fetch Inquiries from Database
$inq_res = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
$inquiries = $inq_res->fetch_all(MYSQLI_ASSOC);

// 3. Fetch Unique Customers
$cust_res = $conn->query("SELECT email, name, phone, COUNT(*) as total_inquiries, MAX(created_at) as last_activity FROM inquiries GROUP BY email ORDER BY last_activity DESC");
$customers = $cust_res->fetch_all(MYSQLI_ASSOC);

// 4. Fetch Sales Data
$sales_res = $conn->query("SELECT * FROM sales ORDER BY sale_date DESC");
$sales = $sales_res->fetch_all(MYSQLI_ASSOC);

// 5. Calculate Total Revenue
$total_revenue = 0;
foreach ($sales as $s) { $total_revenue += $s['total_amount']; }

// 3. Analytics Calculation
$cat_counts = [];
foreach ($products as $p) {
    $cat = $p['category'];
    $cat_counts[$cat] = ($cat_counts[$cat] ?? 0) + 1;
}

$current_tab = $_GET['tab'] ?? 'products';

function getCountry($phone) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($clean) == 10) return "🇮🇳 India";
    if (strpos($clean, '91') === 0 && strlen($clean) == 12) return "🇮🇳 India";
    if (strpos($clean, '1') === 0 && strlen($clean) == 11) return "🇺🇸 USA";
    if (strpos($clean, '44') === 0 && strlen($clean) == 12) return "🇬🇧 UK";
    return "Other";
}
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

        /* Notifications */
        .notification {
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
        }
        .notif-success { background: rgba(76, 175, 80, 0.1); border: 1px solid rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .notif-error { background: rgba(244, 67, 54, 0.1); border: 1px solid rgba(244, 67, 54, 0.2); color: #f44336; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">LUMIFIC</div>
        <a href="?tab=products" class="nav-item <?php echo $current_tab == 'products' ? 'active' : ''; ?>"><i class="fa-solid fa-cube"></i> Products</a>
        <a href="?tab=customers" class="nav-item <?php echo $current_tab == 'customers' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> Customers</a>
        <a href="?tab=inquiries" class="nav-item <?php echo $current_tab == 'inquiries' ? 'active' : ''; ?>"><i class="fa-solid fa-envelope"></i> Inquiries</a>
        <a href="?tab=sales" class="nav-item <?php echo $current_tab == 'sales' ? 'active' : ''; ?>"><i class="fa-solid fa-receipt"></i> Sales</a>
        <a href="?tab=analytics" class="nav-item <?php echo $current_tab == 'analytics' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <div style="margin-top: auto;">
            <a href="logout.php" class="nav-item"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="notification notif-success">
                <i class="fa-solid fa-circle-check"></i> Product added successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
            <div class="notification notif-error">
                <i class="fa-solid fa-triangle-exclamation"></i> Error: A product with this name already exists.
            </div>
        <?php endif; ?>

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
                        <tr><th>Product</th><th>Category</th><th>Price</th><th>Description</th><th>Actions</th></tr>
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
                            <td><div style="font-size: 0.8rem; color: rgba(255,255,255,0.4); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo $p['description']; ?></div></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button class="action-btn" onclick='openEditModal(<?php echo json_encode($p); ?>)' title="Edit Product">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="actions.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" class="action-btn" style="color: #f44336;" onclick="return confirm('Delete this product?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab == 'inquiries'): ?>
            <div class="header">
                <h1>Service Inquiries</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Messages</h3>
                    <div class="value"><?php echo count($inquiries); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Leads</h3>
                    <div class="value"><?php echo count($customers); ?></div>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header"><h3>Recent Activity</h3></div>
                <table>
                    <thead>
                        <tr><th>Date</th><th>Client</th><th>Interest</th><th>Message</th><th>Contact Number</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($inquiries as $iq): ?>
                        <tr class="inquiry-row">
                            <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?php echo date('M d, Y | H:i', strtotime($iq['created_at'])); ?></td>
                            <td>
                                <div style="font-weight: 600;"><?php echo $iq['name']; ?></div>
                                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4);"><?php echo $iq['email']; ?></div>
                            </td>
                            <td><span class="badge-ui" style="background: rgba(226,176,78,0.1); color: var(--accent);"><?php echo $iq['product']; ?></span></td>
                            <td class="inquiry-msg"><?php echo $iq['message']; ?></td>
                            <td>
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                    <?php echo $iq['phone']; ?>
                                    <?php if(strlen(preg_replace('/[^0-9]/', '', $iq['phone'])) != 10): ?>
                                        <i class="fa-solid fa-circle-exclamation" style="color: #f44336; font-size: 0.7rem;" title="Not 10 digits"></i>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.7rem; color: rgba(255,255,255,0.4);"><?php echo getCountry($iq['phone']); ?></div>
                            </td>
                            <td>
                                <form action="actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_inquiry">
                                    <input type="hidden" name="id" value="<?php echo $iq['id']; ?>">
                                    <button type="submit" class="action-btn" style="color: rgba(255,255,255,0.3);" onclick="return confirm('Delete this inquiry?')" title="Delete Inquiry">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($inquiries)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 50px; opacity: 0.3;">No inquiries yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab == 'customers'): ?>
            <div class="header">
                <h1>Customer Database</h1>
            </div>

            <div class="content-section">
                <div class="section-header"><h3>Verified Leads</h3></div>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Total Inquiries</th><th>Last Activity</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($customers as $c): ?>
                        <tr class="inquiry-row">
                            <td style="font-weight: 600;"><?php echo $c['name']; ?></td>
                            <td><?php echo $c['email']; ?></td>
                            <td><?php echo $c['phone']; ?></td>
                            <td><?php echo $c['total_inquiries']; ?></td>
                            <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?php echo date('M d, Y', strtotime($c['last_activity'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($customers)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 50px; opacity: 0.3;">No customers found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($current_tab == 'sales'): ?>
            <div class="header">
                <h1>Sales Tracking</h1>
                <button class="btn-add" onclick="openSaleModal()"><i class="fa-solid fa-plus"></i> Log New Sale</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="value">₹<?php echo number_format($total_revenue); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Units Sold</h3>
                    <div class="value"><?php echo array_sum(array_column($sales, 'quantity')); ?></div>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header"><h3>Sales History</h3></div>
                <table>
                    <thead>
                        <tr><th>Date</th><th>Product</th><th>Customer</th><th>Qty</th><th>Total</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($sales as $s): ?>
                        <tr class="inquiry-row">
                            <td style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?php echo date('M d, Y', strtotime($s['sale_date'])); ?></td>
                            <td style="font-weight: 600;"><?php echo $s['product_name']; ?></td>
                            <td><?php echo $s['customer_name']; ?></td>
                            <td><?php echo $s['quantity']; ?></td>
                            <td style="font-weight: 600; color: var(--accent);">₹<?php echo number_format($s['total_amount']); ?></td>
                            <td>
                                <form action="actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_sale">
                                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" class="action-btn" onclick="return confirm('Delete this sale record?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($sales)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 50px; opacity: 0.3;">No sales logged yet.</td></tr>
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
                    <div class="input-group"><label>Category</label><select name="category"><option value="magnetic">Magnetic Systems</option><option value="downlights">Recessed Downlights</option><option value="spots">Spotlights / COB</option><option value="surface">Surface Mounted</option><option value="outdoor">Outdoor (Garden/Inground)</option><option value="underwater">Underwater Lights</option><option value="accessories">Accessories</option></select></div>
                    <div class="input-group"><label>Price (INR)</label><input type="number" name="price" required></div>
                    <div class="input-group"><label>Finish/Color</label><input type="text" name="color" list="colorOptions" placeholder="e.g. Matte Black"><datalist id="colorOptions"><option value="Matte Black"><option value="Textured White"><option value="Grey / Silver"><option value="Gold"><option value="Rose Gold"><option value="Copper"></datalist></div>
                    <div class="input-group full-width"><label>Product Description</label><textarea name="description" rows="3" placeholder="Enter product features or details..."></textarea></div>
                    <div class="input-group full-width"><label>Product Image</label><input type="file" name="image" accept="image/*" required></div>
                </div>
                <button type="submit" class="btn-add" style="width: 100%; margin-top: 10px;">Save Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2 style="margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Edit Product</h2>
            <form action="actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <div class="input-group full-width"><label>Product Name</label><input type="text" name="name" id="edit_name" required></div>
                    <div class="input-group"><label>Category</label><select name="category" id="edit_category"><option value="magnetic">Magnetic Systems</option><option value="downlights">Recessed Downlights</option><option value="spots">Spotlights / COB</option><option value="surface">Surface Mounted</option><option value="outdoor">Outdoor (Garden/Inground)</option><option value="underwater">Underwater Lights</option><option value="accessories">Accessories</option></select></div>
                    <div class="input-group"><label>Price (INR)</label><input type="number" name="price" id="edit_price" required></div>
                    <div class="input-group"><label>Finish/Color</label><input type="text" name="color" id="edit_color" list="colorOptions"></div>
                    <div class="input-group full-width"><label>Product Description</label><textarea name="description" id="edit_description" rows="3"></textarea></div>
                    <div class="input-group full-width">
                        <label>Change Image (Leave blank to keep current)</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>
                <button type="submit" class="btn-add" style="width: 100%; margin-top: 10px;">Update Product</button>
            </form>
        </div>
    </div>

    <!-- Log Sale Modal -->
    <div id="saleModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSaleModal()">&times;</span>
            <h2 style="margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Log Offline Sale</h2>
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="add_sale">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label>Select Product</label>
                        <select name="product_id" required onchange="updateSalePrice(this)">
                            <option value="">-- Choose Product --</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"><?php echo $p['name']; ?> (₹<?php echo number_format($p['price']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="sale_qty" value="1" min="1" required oninput="calculateTotal()">
                    </div>
                    <div class="input-group">
                        <label>Final Total Amount (₹)</label>
                        <input type="number" name="total_amount" id="sale_total" required>
                    </div>
                    <div class="input-group full-width">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" placeholder="Optional">
                    </div>
                </div>
                <button type="submit" class="btn-add" style="width: 100%; margin-top: 10px;">Record Sale</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('productModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('productModal').style.display = 'none'; }
        
        function openSaleModal() { document.getElementById('saleModal').style.display = 'flex'; }
        function closeSaleModal() { document.getElementById('saleModal').style.display = 'none'; }

        function updateSalePrice(select) {
            const price = select.options[select.selectedIndex].dataset.price;
            document.getElementById('sale_total').value = price;
            calculateTotal();
        }

        function calculateTotal() {
            const select = document.getElementsByName('product_id')[0];
            if(!select.value) return;
            const unitPrice = parseFloat(select.options[select.selectedIndex].dataset.price);
            const qty = parseInt(document.getElementById('sale_qty').value);
            document.getElementById('sale_total').value = unitPrice * qty;
        }

        function openEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_color').value = product.color;
            document.getElementById('edit_description').value = product.description;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
    </script>
</body>
</html>
