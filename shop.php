<?php
require_once 'admin/config.php';

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);

// Calculate dynamic counts based on new catalog categories
$counts = ['all' => count($products)];
foreach ($products as $p) {
    $cat = strtolower(trim($p['category']));
    if (!isset($counts[$cat])) {
        $counts[$cat] = 0;
    }
    $counts[$cat]++;
}

$displayNames = [
    'magnetic' => 'Magnetic Systems',
    'downlights' => 'Recessed Downlights',
    'spots' => 'Spotlights / COB',
    'surface' => 'Surface Mounted',
    'outdoor' => 'Garden / Inground',
    'underwater' => 'Underwater Lights',
    'accessories' => 'Accessories',
    'ceiling masterpieces' => 'Ceiling Masterpieces'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lumific Shop — Browse our premium architectural lighting catalog.">
    <title>Lumific | Shop — The Collection</title>

    <link rel="icon" type="image/webp" href="images/logo.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/shop.css?v=<?php echo time(); ?>">
    <style>
        @media (min-width: 1024px) {
            *, *::before, *::after {
                cursor: none !important;
            }
        }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'light-mode') {
            document.documentElement.classList.add('light-mode');
        }
        window.isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.userId = <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
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
                <li><a href="index.php#home" class="glitch-link" data-value="HOME"><span>HOME</span><span>HOME</span></a></li>
                <li><a href="index.php#shop" class="glitch-link" data-value="COLLECTIONS"><span>COLLECTIONS</span><span>COLLECTIONS</span></a></li>

                <li><a href="index.php#about" class="glitch-link" data-value="ABOUT"><span>ABOUT</span><span>ABOUT</span></a></li>
                <li><a href="index.php#contact" class="glitch-link" data-value="CONTACT"><span>CONTACT</span><span>CONTACT</span></a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="account.php" class="nav-icon-link" aria-label="My Account" title="My Account"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg></a></li>
                <?php else: ?>
                    <li><a href="login.php" class="glitch-link" data-value="LOGIN"><span>LOGIN</span><span>LOGIN</span></a></li>
                <?php endif; ?>
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

    <div class="cart-overlay" id="cartOverlay"></div>
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
            <div class="sidebar-section" id="sidebarSectionCollections">
                <h3 class="sidebar-heading">Collections</h3>
                <ul class="sidebar-nav">
                    <li><button class="filter-btn active" data-filter="all">All Products <span class="count"><?php echo $counts['all']; ?></span></button></li>
                    <?php 
                        // Order the categories based on our mapping for better UX
                        $ordered_cats = array_keys($displayNames);
                        foreach($ordered_cats as $cat): 
                            if(!isset($counts[$cat])) continue;
                    ?>
                        <li><button class="filter-btn" data-filter="<?php echo $cat; ?>"><?php echo $displayNames[$cat]; ?> <span class="count"><?php echo $counts[$cat]; ?></span></button></li>
                    <?php endforeach; ?>
                    
                    <?php 
                        // Show any other categories not in the displayNames mapping
                        foreach($counts as $cat => $count): 
                            if($cat == 'all' || isset($displayNames[$cat])) continue;
                    ?>
                        <li><button class="filter-btn" data-filter="<?php echo $cat; ?>"><?php echo ucwords($cat); ?> <span class="count"><?php echo $count; ?></span></button></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="sidebar-section" id="sidebarSectionSort">
                <h3 class="sidebar-heading">Sort By</h3>
                <ul class="sidebar-nav">
                    <li><button class="filter-btn active-sort" data-sort="featured">Featured</button></li>
                    <li><button class="filter-btn" data-sort="price-asc">Price: Low to High</button></li>
                    <li><button class="filter-btn" data-sort="price-desc">Price: High to Low</button></li>
                    <li><button class="filter-btn" data-sort="new">Newest</button></li>
                </ul>
            </div>
        </aside>

        <main class="shop-main">
            <div class="product-grid cols-3" id="productGrid">
                <?php foreach ($products as $p): ?>
                <article class="product-card" data-cat="<?php echo strtolower($p['category']); ?>" data-price="<?php echo $p['price']; ?>" data-id="<?php echo $p['id']; ?>" onclick="openProductDetail(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)">
                    <div class="product-img-wrap">
                        <div class="product-img-bg product-real-img">
                            <img src="<?php echo $p['image_url']; ?>" alt="<?php echo $p['name']; ?>" class="product-real-photo" onerror="this.src='images/logo.webp'; this.style.padding='20%'">
                        </div>
                        <div class="product-hover-glow"></div>
                    </div>
                    <div class="product-info">
                        <div class="product-details-top">
                            <h2 class="product-title"><?php echo $p['name']; ?></h2>
                            <p class="product-card-desc"><?php echo $p['description']; ?></p>
                        </div>
                        <div class="product-details-bottom">
                            <p class="product-price-tag">₹<?php echo number_format($p['price'], 0); ?></p>
                            <div class="card-qty-wrapper" onclick="event.stopPropagation();">
                                <div class="card-qty-controls">
                                    <button class="card-qty-btn" data-action="dec">−</button>
                                    <span class="card-qty-num">1</span>
                                    <button class="card-qty-btn" data-action="inc">+</button>
                                </div>
                                <button class="card-quick-add" data-id="<?php echo $p['id']; ?>" data-name="<?php echo $p['name']; ?>" data-price="<?php echo $p['price']; ?>" data-cat="<?php echo $p['category']; ?>">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                
                <!-- No Products Message -->
                <div id="noProductsMessage" class="no-results" style="display: none;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m16 16-4-4-4 4"/><path d="M12 12v-8"/>
                    </svg>
                    <h3>Collection Under Maintenance</h3>
                    <p>We're currently curating new designs for this collection. Please explore our other categories or check back soon!</p>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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
            const size = 10 - i * 0.75;
            const opacity = 0.5 - i * 0.04;
            const hue = 40 + (i / 10) * 140;
            td.style.width = td.style.height = `${size}px`;
            td.style.background = `hsla(${hue}, 85%, 72%, ${opacity})`;
            td.style.boxShadow = `0 0 ${size * 2.5}px hsla(${hue}, 85%, 72%, ${opacity * 0.7})`;
            document.body.appendChild(td);
            trailDots.push({ el: td, x: 0, y: 0 });
        }
        let mouseX = window.innerWidth / 2, mouseY = window.innerHeight / 2;
        let dotX = mouseX, dotY = mouseY, glowX = mouseX, glowY = mouseY;
        window.addEventListener('mousemove', e => {
            mouseX = e.clientX; mouseY = e.clientY;
            dot.classList.add('is-visible'); glow.classList.add('is-visible');
            trailDots.forEach(d => d.el.classList.add('is-visible'));
        });
        function animate() {
            dotX += (mouseX - dotX); dotY += (mouseY - dotY);
            glowX += (mouseX - glowX) * 0.12; glowY += (mouseY - glowY) * 0.12;
            dot.style.transform = `translate3d(${dotX}px, ${dotY}px, 0) translate3d(-50%, -50%, 0)`;
            glow.style.transform = `translate3d(${glowX}px, ${glowY}px, 0) translate3d(-50%, -50%, 0)`;
            let prevX = dotX, prevY = dotY;
            trailDots.forEach(d => {
                d.x += (prevX - d.x) * 0.35; d.y += (prevY - d.y) * 0.35;
                d.el.style.left = `${d.x}px`; d.el.style.top = `${d.y}px`;
                d.el.style.transform = `translate3d(-50%, -50%, 0)`;
                prevX = d.x; prevY = d.y;
            });
            requestAnimationFrame(animate);
        }
        animate();
    })();

    // Global Modal Functions
    function openProductDetail(p) {
        const modal = document.getElementById('productDetailModal');
        document.getElementById('modal_img').src = p.image_url;
        
        // Beautiful category formatting matching the premium dark mode mockup
        let categoryName = p.category || '';
        const displayNames = {
            'magnetic': 'Magnetic Systems',
            'downlights': 'Recessed Downlights',
            'spots': 'Tracklights',
            'surface': 'Surface Mounted',
            'outdoor': 'Garden / Inground',
            'underwater': 'Underwater Lights',
            'accessories': 'Accessories',
            'ceiling masterpieces': 'Ceiling Masterpieces'
        };
        const mappedCat = displayNames[categoryName.toLowerCase()] || categoryName;
        document.getElementById('modal_cat').innerHTML = mappedCat.toUpperCase() + ' <span class="cat-bullet">•</span>';
        
        // Title formatting to trim long technical suffixes for a clean designer-label title
        let titleText = p.name || '';
        titleText = titleText.replace(/\b(Track Light|Technical Spotlight|Recessed Light|Downlight|Garden Bollard|Inground Uplight|Underwater Spot|Surface Sconce|Architectural Disk)\b/gi, '').trim();
        document.getElementById('modal_title').textContent = titleText;
        
        // High-end integer formatted price (e.g. ₹420)
        document.getElementById('modal_price').textContent = "₹" + parseInt(p.price).toLocaleString();
        
        // Handle empty or N/A fields with smart premium defaults based on product type
        const name = (p.name || '').toLowerCase();
        const cat = (p.category || '').toLowerCase();
        
        let wattage = p.wattage;
        if (!wattage || wattage === 'N/A') {
            if (name.includes('enso') || name.includes('ceiling')) wattage = '24W';
            else if (name.includes('barrel')) wattage = '15W';
            else if (name.includes('gopro') || name.includes('spot')) wattage = '10W';
            else if (name.includes('halo') || name.includes('downlight')) wattage = '8W';
            else if (name.includes('iskim') || name.includes('profile')) wattage = '18W/m';
            else wattage = '12W';
        }
        
        let beam = p.beam_angle;
        if (!beam || beam === 'N/A') {
            if (name.includes('halo') || name.includes('allrounder') || name.includes('enso') || name.includes('iskim') || cat.includes('downlights') || cat.includes('profiles') || cat.includes('ceiling')) {
                beam = '120°';
            } else if (name.includes('barrel') || name.includes('baylight') || cat.includes('outdoor')) {
                beam = '36°';
            } else {
                beam = '24°';
            }
        }
        
        let cri = p.cri;
        if (!cri || cri === 'N/A') {
            cri = (cat.includes('outdoor') || name.includes('baylight')) ? 'Ra > 85' : 'Ra > 90';
        }
        
        let ip = p.ip_rating;
        if (!ip || ip === 'N/A') {
            if (cat.includes('underwater') || name.includes('aqua')) ip = 'IP68';
            else if (cat.includes('outdoor') || name.includes('baylight') || name.includes('garden')) ip = 'IP65';
            else if (name.includes('halo') || cat.includes('downlights')) ip = 'IP44';
            else ip = 'IP20';
        }

        document.getElementById('modal_color').textContent = p.color || 'Black / Gold';
        document.getElementById('modal_wattage').textContent = wattage;
        document.getElementById('modal_beam').textContent = beam;
        document.getElementById('modal_cri').textContent = cri + " | " + ip;
        document.getElementById('modal_desc').textContent = p.description;
        
        // Dynamically populate modal Add button data attributes and reset quantity to 1
        const modalAddBtn = document.getElementById('modal_add_btn');
        if (modalAddBtn) {
            modalAddBtn.dataset.id = p.id;
            modalAddBtn.dataset.name = p.name;
            modalAddBtn.dataset.price = p.price;
            modalAddBtn.dataset.cat = p.category;
        }
        const modalQtyNum = document.getElementById('modal_qty_num');
        if (modalQtyNum) {
            modalQtyNum.textContent = "1";
        }
        
        modal.classList.add('active');
        document.body.classList.add('modal-active');
        document.body.style.overflow = 'hidden';
    }

    function closeProductDetail() {
        const modal = document.getElementById('productDetailModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.classList.remove('modal-active');
            document.body.style.overflow = '';
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeProductDetail();
    });

    // Initialize Modal Click listener
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('productDetailModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target.id === 'productDetailModal') closeProductDetail();
            });
        }
    });

    </script>
    <div class="mobile-action-bar">
        <button class="action-bar-btn" id="mobileFilterBtn" onclick="openMobileDrawer('filter')">
            <svg style="pointer-events:none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16m-7 6h7"/></svg>
            <span style="pointer-events:none;">Collections</span>
        </button>
        <div class="action-bar-divider"></div>
        <button class="action-bar-btn" id="mobileSortBtn" onclick="openMobileDrawer('sort')">
            <svg style="pointer-events:none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M6 12h12m-3 6h3"/></svg>
            <span style="pointer-events:none;">Sort By</span>
        </button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('mobileFilterOverlay');
        if (overlay) overlay.addEventListener('click', window.closeMobileDrawer);
    });

    window.addEventListener('popstate', (e) => {
        const drawer = document.getElementById('mobileFilterDrawer');
        const overlay = document.getElementById('mobileFilterOverlay');
        if (drawer && drawer.classList.contains('open')) {
            drawer.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
    </script>

    <!-- Mobile Filter/Sort Drawer -->
    <div class="mobile-filter-overlay" id="mobileFilterOverlay"></div>
    <aside class="mobile-filter-drawer" id="mobileFilterDrawer">
        <div class="cart-drawer-header">
            <h2>Filter & Sort</h2>
        </div>
        <div class="mobile-filter-body" id="mobileFilterContent">
            <!-- Content will be mirrored from sidebar via JS -->
        </div>
    </aside>

    <a href="https://wa.me/919898103966" class="whatsapp-float" target="_blank" aria-label="Chat on WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.558 4.116 1.535 5.845L.057 23.428a.75.75 0 0 0 .916.916l5.638-1.479A11.953 11.953 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.698 9.698 0 0 1-4.95-1.355l-.355-.21-3.685.966.982-3.594-.23-.368A9.698 9.698 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/>
        </svg>
    </a>
    <script src="js/shop.js?v=<?php echo time(); ?>"></script>
    <script src="js/theme.js"></script>
    </aside>

    <!-- Product Detail Modal -->
    <div class="detail-modal" id="productDetailModal">
        <div class="detail-modal-content">
            <button class="detail-modal-close" onclick="closeProductDetail()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <div class="detail-img-side">
                <img id="modal_img" src="" alt="Product Detail">
            </div>
            <div class="detail-info-side">
                <div id="modal_cat" class="detail-cat"></div>
                <h2 id="modal_title" class="detail-title"></h2>
                <div id="modal_price" class="detail-price"></div>
                
                <div class="detail-specs-grid">
                    <div class="spec-item">
                        <span class="spec-label">Finish / Color</span>
                        <div id="modal_color" class="spec-value"></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Wattage</span>
                        <div id="modal_wattage" class="spec-value"></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Beam Angle</span>
                        <div id="modal_beam" class="spec-value"></div>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">CRI | IP Rating</span>
                        <div id="modal_cri" class="spec-value"></div>
                    </div>
                </div>

                <div class="detail-desc-label">Product Description</div>
                <p id="modal_desc" class="detail-desc"></p>
                
                <div class="card-qty-wrapper" style="margin-top: 12px; display: inline-flex; max-width: fit-content;">
                    <div class="card-qty-controls">
                        <button class="card-qty-btn" data-action="dec">−</button>
                        <span class="card-qty-num" id="modal_qty_num">1</span>
                        <button class="card-qty-btn" data-action="inc">+</button>
                    </div>
                    <button class="card-quick-add" id="modal_add_btn" data-id="" data-name="" data-price="" data-cat="">
                        Add
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Premium Success Glassmorphic Modal Markup -->
    <div class="custom-success-overlay" id="customSuccessAlert">
        <div class="custom-success-box">
            <h3><i class="fa-solid fa-circle-check" style="color: #2ecc71; margin-right: 8px; font-size: 1.1rem;"></i>SUCCESS</h3>
            <p id="customSuccessAlertMessage">Success! Your order has been placed.</p>
            <div class="custom-success-buttons">
                <button class="custom-success-btn btn-ok" onclick="closeSuccessAlert()">OK</button>
            </div>
        </div>
    </div>

    <!-- Custom Premium Info/Warning Glassmorphic Modal Markup -->
    <div class="custom-info-overlay" id="customLoginAlert">
        <div class="custom-info-box">
            <h3><i class="fa-solid fa-circle-info" style="color: var(--accent); margin-right: 8px; font-size: 1.1rem;"></i>LOGIN REQUIRED</h3>
            <p>Please login to your Lumific account to place this order.</p>
            <div class="custom-info-buttons">
                <button class="custom-info-btn btn-cancel" onclick="closeLoginAlert()">Cancel</button>
                <button class="custom-info-btn btn-login" onclick="proceedToLogin()">LOGIN</button>
            </div>
        </div>
    </div>

    <script>
        function showSuccessAlert(message) {
            const alertMsg = document.getElementById('customSuccessAlertMessage');
            const alertModal = document.getElementById('customSuccessAlert');
            if (alertMsg) alertMsg.textContent = message;
            if (alertModal) {
                alertModal.classList.add('show');
            }
        }

        function closeSuccessAlert() {
            const alertModal = document.getElementById('customSuccessAlert');
            if (alertModal) {
                alertModal.classList.remove('show');
            }
        }

        function showLoginAlert() {
            const loginModal = document.getElementById('customLoginAlert');
            if (loginModal) {
                loginModal.classList.add('show');
            }
        }

        function closeLoginAlert() {
            const loginModal = document.getElementById('customLoginAlert');
            if (loginModal) {
                loginModal.classList.remove('show');
            }
        }

        function proceedToLogin() {
            window.location.href = 'login.php';
        }

        // Close alerts on backdrop click
        window.addEventListener('DOMContentLoaded', () => {
            const alertOverlay = document.getElementById('customSuccessAlert');
            if (alertOverlay) {
                alertOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeSuccessAlert();
                    }
                });
            }

            const loginOverlay = document.getElementById('customLoginAlert');
            if (loginOverlay) {
                loginOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeLoginAlert();
                    }
                });
            }
        });
    </script>
</body>
</html>
