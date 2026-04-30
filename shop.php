<?php
require_once 'admin/config.php';

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);

// Category Counts
$counts = [
    'all' => count($products),
    'tracklights' => 0,
    'downlights' => 0,
    'spots' => 0,
    'outdoor' => 0,
    'profiles' => 0,
    'ceiling' => 0
];

foreach ($products as $p) {
    if (isset($counts[$p['category']])) {
        $counts[$p['category']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lumific Shop — Browse our premium architectural lighting catalog.">
    <title>Lumific | Shop — The Collection</title>

    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
    <style>
        @media (min-width: 1024px) {
            body, a, button, .product-card, .filter-btn, .quick-add-btn { cursor: none !important; }
        }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'light-mode') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
</head>

<body>
    <div class="bg-gradients">
        <div class="gradient-orb orb-purple"></div>
        <div class="gradient-orb orb-orange"></div>
        <div class="gradient-orb orb-blue"></div>
    </div>

    <nav id="navbar">
        <div class="nav-container">
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <span class="line"></span>
                <span class="line"></span>
            </button>
            <ul class="nav-links">
                <li><a href="index.html#home" class="glitch-link" data-value="HOME"><span>HOME</span><span>HOME</span></a></li>
                <li><a href="index.html#shop" class="glitch-link" data-value="COLLECTIONS"><span>COLLECTIONS</span><span>COLLECTIONS</span></a></li>
                <li><a href="https://lumific.in/lumific-2026.pdf" target="_blank" class="glitch-link" data-value="CATALOGUE"><span>CATALOGUE</span><span>CATALOGUE</span></a></li>
                <li><a href="index.html#about" class="glitch-link" data-value="ABOUT"><span>ABOUT</span><span>ABOUT</span></a></li>
                <li><a href="index.html#contact" class="glitch-link" data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>
            </ul>

            <button id="cartToggle" class="cart-toggle" aria-label="View Cart (0 items)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span class="cart-badge" id="cartBadge">0</span>
            </button>

            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Light Mode">
                <span class="toggle-track">
                    <span class="toggle-thumb">
                        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                        <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </span>
                </span>
            </button>
        </div>
    </nav>

    <aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
        <div class="cart-drawer-header">
            <h2>Your Bag</h2>
            <button class="cart-close-btn" id="cartCloseBtn">&times;</button>
        </div>
        <div class="cart-body" id="cartBody">
            <div class="cart-empty" id="cartEmpty"><p>Your bag is empty</p></div>
            <ul class="cart-items-list" id="cartItemsList"></ul>
        </div>
        <div class="cart-footer" id="cartFooter" style="display:none">
            <div class="cart-subtotal"><span>Subtotal</span><span id="cartTotal">₹0.00</span></div>
            <button class="btn-checkout">Proceed to Checkout</button>
        </div>
    </aside>

    <section class="shop-hero">
        <div class="shop-hero-content">
            <span class="shop-eyebrow">Studio Abby by Lumific</span>
            <h1 class="shop-hero-title">The Collection</h1>
            <p class="shop-hero-subtitle">Sculpting spatial atmosphere with precision-engineered artifacts.</p>
        </div>
    </section>

    <div class="shop-layout">
        <aside class="shop-sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-heading">Categories</h3>
                <ul class="sidebar-nav">
                    <li><button class="filter-btn active" data-filter="all">All Products <span class="count"><?php echo $counts['all']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="tracklights">Tracklights <span class="count"><?php echo $counts['tracklights']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="downlights">Downlights <span class="count"><?php echo $counts['downlights']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="spots">Spots <span class="count"><?php echo $counts['spots']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="outdoor">Outdoor <span class="count"><?php echo $counts['outdoor']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="profiles">Profiles <span class="count"><?php echo $counts['profiles']; ?></span></button></li>
                    <li><button class="filter-btn" data-filter="ceiling">Studio Abby <span class="count"><?php echo $counts['ceiling']; ?></span></button></li>
                </ul>
            </div>
        </aside>

        <main class="shop-main">
            <div class="product-grid cols-2" id="productGrid">
                <?php foreach ($products as $p): ?>
                <article class="product-card" data-cat="<?php echo $p['category']; ?>" data-price="<?php echo $p['price']; ?>" data-id="<?php echo $p['id']; ?>">
                    <div class="product-img-wrap">
                        <div class="product-img-bg product-real-img">
                            <img src="<?php echo $p['image_url']; ?>" alt="<?php echo $p['name']; ?>" class="product-real-photo">
                        </div>
                        <?php if($p['badge']): ?><span class="badge-ui" style="position:absolute; top:15px; left:15px; background:var(--accent); color:#000; padding:4px 10px; border-radius:4px; font-size:0.7rem; font-weight:600;"><?php echo strtoupper($p['badge']); ?></span><?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-details-top">
                            <span class="product-cat-tag" style="text-transform: capitalize;"><?php echo $p['category']; ?></span>
                            <h2 class="product-title"><?php echo $p['name']; ?></h2>
                        </div>
                        <div class="product-details-bottom">
                            <p class="product-price-tag">₹<?php echo number_format($p['price'], 2); ?></p>
                            <button class="card-quick-add" data-id="<?php echo $p['id']; ?>" data-name="<?php echo $p['name']; ?>" data-price="<?php echo $p['price']; ?>" data-cat="<?php echo $p['category']; ?>">Add</button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                
                <?php if(empty($products)): ?>
                <div style="grid-column: span 2; text-align: center; padding: 100px; opacity: 0.5;">
                    <h3>The inventory is currently being curated.</h3>
                    <p>Check back shortly for the latest Lumific arrivals.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer style="padding: 40px; text-align:center; color: rgba(255,255,255,0.3); font-size:0.85rem; border-top: 1px solid rgba(255,255,255,0.05);">
        &copy; 2026 Lumific. All rights reserved.
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="js/shop.js"></script>
    <script src="js/theme.js"></script>
    <script>
    (function initializeSpectralCursor() {
        if (window.matchMedia('(pointer: coarse)').matches || window.innerWidth < 1024) return;
        const dot = document.createElement('div');
        const glow = document.createElement('div');
        dot.className = 'cursor-dot'; glow.className = 'cursor-glow';
        document.body.appendChild(dot); document.body.appendChild(glow);
        const trailDots = [];
        for (let i = 0; i < 10; i++) {
            const td = document.createElement('div');
            td.className = 'cursor-trail-dot';
            document.body.appendChild(td);
            trailDots.push({ el: td, x: 0, y: 0 });
        }
        let mouseX = 0, mouseY = 0, dotX = 0, dotY = 0;
        window.addEventListener('mousemove', e => { mouseX = e.clientX; mouseY = e.clientY; });
        function animate() {
            dotX += (mouseX - dotX); dotY += (mouseY - dotY);
            dot.style.transform = `translate3d(${dotX}px, ${dotY}px, 0) translate3d(-50%, -50%, 0)`;
            requestAnimationFrame(animate);
        }
        animate();
    })();
    </script>
</body>
</html>
