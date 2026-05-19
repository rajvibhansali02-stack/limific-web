<?php
require_once 'admin/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=account.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'] ?? '';

// Fetch user details
$user_res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user_data = $user_res->fetch_assoc();

// Fetch orders for this user
$orders_res = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
$orders = $orders_res->fetch_all(MYSQLI_ASSOC);

// Fetch all sales (items) for these orders
$order_ids = array_column($orders, 'order_id');
$items = [];
if (!empty($order_ids)) {
    $order_ids_str = "'" . implode("','", $order_ids) . "'";
    $items_res = $conn->query("SELECT s.*, p.image_url FROM sales s LEFT JOIN products p ON s.product_id = p.id WHERE s.order_id IN ($order_ids_str)");
    while ($row = $items_res->fetch_assoc()) {
        $items[$row['order_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="My Account | Lumific — Track your orders and manage your profile.">
    <title>My Account | Lumific</title>
    <link rel="icon" type="image/webp" href="images/logo.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/account.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        if (localStorage.getItem('theme') === 'light-mode') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
</head>
<body>
    <!-- Background Gradients -->
    <div class="bg-gradients">
        <div class="gradient-orb orb-purple"></div>
        <div class="gradient-orb orb-orange"></div>
        <div class="gradient-orb orb-blue"></div>
    </div>

    <!-- Navigation -->
    <nav id="navbar">
        <div class="nav-container">
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <span class="line"></span>
                <span class="line"></span>
            </button>
            <ul class="nav-links">
                <li><a href="index.php#home" class="glitch-link" data-value="HOME"><span>HOME</span><span>HOME</span></a></li>
                <li><a href="shop.php" class="glitch-link" data-value="SHOP"><span>SHOP</span><span>SHOP</span></a></li>
                <li><a href="index.php#shop" class="glitch-link" data-value="COLLECTIONS"><span>COLLECTIONS</span><span>COLLECTIONS</span></a></li>

                <li><a href="index.php#about" class="glitch-link" data-value="ABOUT"><span>ABOUT</span><span>ABOUT</span></a></li>
                <li><a href="index.php#contact" class="glitch-link" data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>
                <li><a href="account.php" class="nav-icon-link active" aria-label="My Account" title="My Account" style="color: var(--accent);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg></a></li>
            </ul>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Light Mode">
                <span class="toggle-track">
                    <span class="toggle-thumb">
                        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                        </svg>
                        <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </span>
                </span>
            </button>
        </div>
    </nav>

    <main class="account-container">
        <header class="account-header">
            <div class="header-left">
                <div class="user-greeting">
                    <span class="eyebrow">Executive Member</span>
                    <h1 class="user-name"><?php echo htmlspecialchars($user_name); ?></h1>
                </div>
            </div>
            <div class="header-right">
                <div class="account-stats">
                    <div class="stat-box">
                        <span class="stat-label">Orders</span>
                        <span class="stat-value"><?php echo count($orders); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-box">
                        <span class="stat-label">Since</span>
                        <span class="stat-value"><?php echo date('M Y', strtotime($user_data['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <section class="account-grid">
            <!-- Sidebar Navigation -->
            <aside class="account-sidebar">
                <ul class="side-nav">
                    <li><a href="#orders" class="side-nav-link active" data-tab="orders">
                        <i class="fa-solid fa-box"></i>
                        <span>Orders</span>
                    </a></li>
                    <li><a href="#profile" class="side-nav-link" data-tab="profile">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Profile</span>
                    </a></li>
                    <li><a href="#security" class="side-nav-link" data-tab="security">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Security</span>
                    </a></li>
                    <li><a href="#payments" class="side-nav-link" data-tab="payments">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Payments</span>
                    </a></li>
                    <li class="nav-logout-item"><a href="logout_user.php" class="side-nav-link logout-link" onclick="return confirmLogout(event)">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Logout</span>
                    </a></li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <div class="account-content">
                <!-- Orders Tab -->
                <div id="orders" class="tab-pane active">
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <p>You haven't placed any orders yet.</p>
                            <a href="shop.php" class="btn-primary">Browse Studio</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-meta">
                                            <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="order-status-badge <?php echo strtolower($order['order_status']); ?>">
                                            <?php 
                                                $status_icon = 'fa-circle-notch fa-spin';
                                                if($order['order_status'] == 'Delivered') $status_icon = 'fa-check-double';
                                                if($order['order_status'] == 'Shipped') $status_icon = 'fa-truck-fast';
                                                if($order['order_status'] == 'Processing') $status_icon = 'fa-gears';
                                                if($order['order_status'] == 'Cancelled') $status_icon = 'fa-xmark';
                                                if($order['order_status'] == 'Refunded') $status_icon = 'fa-rotate-left';
                                            ?>
                                            <i class="fa-solid <?php echo $status_icon; ?>"></i>
                                            <?php echo $order['order_status']; ?>
                                        </div>
                                    </div>
                                    <div class="order-items">
                                        <?php if (isset($items[$order['order_id']])): ?>
                                            <?php foreach ($items[$order['order_id']] as $item): ?>
                                                <div class="order-item">
                                                    <div class="item-img-wrapper">
                                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'images/logo.webp'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-img">
                                                    </div>
                                                    <div class="item-info">
                                                        <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                        <span class="item-qty">Qty: <?php echo $item['quantity']; ?></span>
                                                    </div>
                                                    <div class="item-price">₹<?php echo number_format($item['total_amount'], 2); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-footer">
                                        <div class="payment-method">
                                            <span class="label">Status</span>
                                            <span class="value"><?php echo $order['payment_status']; ?> (<?php echo $order['payment_method']; ?>)</span>
                                        </div>
                                        <div class="order-total">
                                            <span class="label">Total</span>
                                            <span class="value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <?php if ($order['order_status'] !== 'Cancelled' && $order['order_status'] !== 'Refunded'): ?>
                                        <div class="order-tracking">
                                            <?php 
                                                $status = $order['order_status'];
                                                $s_val = 0;
                                                if ($status == 'Pending') $s_val = 1;
                                                elseif ($status == 'Processing') $s_val = 2;
                                                elseif ($status == 'Shipped') $s_val = 3;
                                                elseif ($status == 'Delivered') $s_val = 4;
                                                elseif ($status == 'Refunded') $s_val = 0;
                                                
                                                $progress = (($s_val - 1) / 3) * 100;
                                                if ($s_val == 0) $progress = 0;
                                                if ($status == 'Delivered') $progress = 100;
                                            ?>
                                            <div class="tracking-steps">
                                                <div class="step <?php echo $s_val >= 1 ? 'completed' : ''; ?>">
                                                    <div class="node"><?php echo $s_val > 1 ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-cart-shopping"></i>'; ?></div>
                                                    <span>Placed</span>
                                                </div>
                                                <div class="step <?php echo $s_val >= 2 ? 'completed' : ''; ?>">
                                                    <div class="node"><?php echo $s_val > 2 ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-gear"></i>'; ?></div>
                                                    <span>Processing</span>
                                                </div>
                                                <div class="step <?php echo $s_val >= 3 ? 'completed' : ''; ?>">
                                                    <div class="node"><?php echo $s_val > 3 ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-truck-fast"></i>'; ?></div>
                                                    <span>Shipped</span>
                                                </div>
                                                <div class="step <?php echo $s_val >= 4 ? 'completed' : ''; ?>">
                                                    <div class="node"><?php echo $s_val >= 4 ? '<i class="fa-solid fa-house-chimney"></i>' : '<i class="fa-solid fa-box"></i>'; ?></div>
                                                    <span>Delivered</span>
                                                </div>
                                            </div>
                                            <div class="tracking-line">
                                                <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="order-status-notice">
                                            <?php if ($order['order_status'] == 'Cancelled'): ?>
                                                <div class="status-msg cancelled-msg">
                                                    <i class="fa-solid fa-circle-xmark"></i> Cancelled
                                                </div>
                                            <?php elseif ($order['order_status'] == 'Refunded'): ?>
                                                <div class="status-msg refunded-msg">
                                                    <i class="fa-solid fa-rotate-left"></i> Refunded
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Profile Tab -->
                <div id="profile" class="tab-pane">
                    <div class="profile-single-card">
                        <!-- Profile Info Form -->
                        <form id="profileForm" class="profile-card">
                            <h2 class="form-section-title">Personal Details</h2>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                            </div>
                             <div class="form-group">
                                 <label>Email Address</label>
                                 <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                             </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>" placeholder="Enter your delivery address">
                            </div>
                            <button type="submit" class="btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Security Tab -->
                <div id="security" class="tab-pane">
                    <div class="profile-single-card">
                        <!-- Change Password Form -->
                        <form id="passwordForm" class="profile-card">
                            <h2 class="form-section-title">Security</h2>
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="••••••••" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="••••••••" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>

                <!-- Payments Tab -->
                <div id="payments" class="tab-pane">
                    <div class="profile-card">
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order Ref</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td data-label="Date"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td data-label="Order Ref">#<?php echo $order['order_id']; ?></td>
                                        <td data-label="Method"><?php echo $order['payment_method']; ?></td>
                                        <td data-label="Amount">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td data-label="Status"><span class="status-pill <?php echo strtolower($order['payment_status']); ?>"><?php echo $order['payment_status']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="5" style="text-align:center; padding: 40px; opacity: 0.5;">No payment records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="account-footer-main">
        <p>&copy; 2026 Lumific. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.33/dist/lenis.min.js"></script>
    <script>
        // Tab Switching Logic
        const navLinks = document.querySelectorAll('.side-nav-link');
        const tabPanes = document.querySelectorAll('.tab-pane');

        navLinks.forEach(link => {
            if (link.classList.contains('logout-link')) return;
            link.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation(); // Completely prevent any other global click listeners (like script.js scroll hijacking) from running
                e.stopPropagation();
                
                const targetTabId = link.getAttribute('data-tab');
                const targetPane = document.getElementById(targetTabId);

                if (link.classList.contains('active')) return;

                // Deactivate others
                navLinks.forEach(l => l.classList.remove('active'));
                tabPanes.forEach(p => {
                    p.classList.remove('active');
                    gsap.set(p, { clearProps: "all" }); // Reset any GSAP state
                });

                // Activate target
                link.classList.add('active');
                targetPane.classList.add('active');

                // Refresh ScrollTrigger and Lenis immediately in case content length changed
                if (window.ScrollTrigger) ScrollTrigger.refresh();
                if (window.lenis) {
                    window.lenis.resize();
                }
            });
        });

        // Profile and Password Form Handlers
        const profileForm = document.getElementById('profileForm');
        const passwordForm = document.getElementById('passwordForm');

        if (profileForm) {
            profileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = profileForm.querySelector('button');
                const originalText = btn.innerText;
                btn.innerText = 'Updating...';
                btn.disabled = true;

                const formData = new FormData(profileForm);
                formData.append('action', 'update_profile');

                try {
                    const res = await fetch('update_user_profile.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    alert(data.message);
                    if (data.success) {
                        document.querySelector('.user-name').innerText = formData.get('name');
                    }
                } catch (err) {
                    alert('An error occurred. Please try again.');
                } finally {
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            });
        }

        if (passwordForm) {
            passwordForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = passwordForm.querySelector('button');
                const originalText = btn.innerText;
                btn.innerText = 'Changing...';
                btn.disabled = true;

                const formData = new FormData(passwordForm);
                formData.append('action', 'change_password');

                try {
                    const res = await fetch('update_user_profile.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    alert(data.message);
                    if (data.success) passwordForm.reset();
                } catch (err) {
                    alert('An error occurred. Please try again.');
                } finally {
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            });
        }

        // Initialize GSAP animations for the page
        window.addEventListener('load', () => {
            gsap.from('.account-header', { opacity: 0, y: -30, duration: 1, ease: "power3.out" });
            gsap.from('.account-sidebar', { opacity: 0, x: -30, duration: 1, delay: 0.2, ease: "power3.out" });
            gsap.from('.account-content', { opacity: 0, x: 30, duration: 1, delay: 0.2, ease: "power3.out" });
            if (window.ScrollTrigger) ScrollTrigger.refresh();
        });

        function confirmLogout(e) {
            if (e) e.preventDefault();
            const alertModal = document.getElementById('customLogoutAlert');
            if (alertModal) {
                alertModal.classList.add('show');
            }
            return false;
        }

        function closeCustomAlert() {
            const alertModal = document.getElementById('customLogoutAlert');
            if (alertModal) {
                alertModal.classList.remove('show');
            }
        }

        function proceedLogout() {
            window.location.href = "logout_user.php";
        }

        // Close alert on backdrop click
        window.addEventListener('DOMContentLoaded', () => {
            const alertOverlay = document.getElementById('customLogoutAlert');
            if (alertOverlay) {
                alertOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeCustomAlert();
                    }
                });
            }
        });
    </script>

    <!-- Custom Premium Glassmorphic Alert Modal Markup -->
    <div class="custom-alert-overlay" id="customLogoutAlert">
        <div class="custom-alert-box">
            <h3><i class="fa-solid fa-circle-exclamation" style="color: #e74c3c; margin-right: 8px; font-size: 1.1rem;"></i>LOGOUT</h3>
            <p>Are you sure you want to logout of your Lumific account?</p>
            <div class="custom-alert-buttons">
                <button class="custom-alert-btn btn-cancel" onclick="closeCustomAlert()">Cancel</button>
                <button class="custom-alert-btn btn-confirm" onclick="proceedLogout()">LOGOUT</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.33/dist/lenis.min.js"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/theme.js"></script>
</body>
</html>
