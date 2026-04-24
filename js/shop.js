// ─── LUMIFIC SHOP — E-commerce Logic ────────────────────────────────────────

// ─── 1. Cart State ───────────────────────────────────────────────────────────
const cart = [];
const cartBadge = document.getElementById('cartBadge');
const cartDrawer = document.getElementById('cartDrawer');
const cartOverlay = document.getElementById('cartOverlay');
const cartToggle = document.getElementById('cartToggle');
const cartCloseBtn = document.getElementById('cartCloseBtn');
const cartBody = document.getElementById('cartBody');
const cartItemsList = document.getElementById('cartItemsList');
const cartEmpty = document.getElementById('cartEmpty');
const cartFooter = document.getElementById('cartFooter');
const cartTotal = document.getElementById('cartTotal');

// ─── 2. Cart Drawer Open/Close ────────────────────────────────────────────────
function openCart() {
    cartDrawer.classList.add('open');
    cartOverlay.classList.add('open');
    cartDrawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    cartDrawer.classList.remove('open');
    cartOverlay.classList.remove('open');
    cartDrawer.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

if (cartToggle) cartToggle.addEventListener('click', openCart);
if (cartCloseBtn) cartCloseBtn.addEventListener('click', closeCart);
if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

// Keyboard: Escape closes drawer
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCart();
});

// ─── 3. Cart Rendering ────────────────────────────────────────────────────────
function updateCartSummary() {
    let total = 0;
    let totalQty = 0;
    cart.forEach(item => {
        total += item.price * item.qty;
        totalQty += item.qty;
    });

    if (cartTotal) cartTotal.textContent = `₹${total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    if (cartBadge) {
        cartBadge.textContent = totalQty;
        if (totalQty > 0) cartBadge.classList.add('visible');
        else cartBadge.classList.remove('visible');
    }

    if (cart.length === 0) {
        cartEmpty.style.display = 'flex';
        if (cartFooter) cartFooter.style.display = 'none';
        if (cartItemsList) cartItemsList.innerHTML = '';
    } else {
        cartEmpty.style.display = 'none';
        if (cartFooter) cartFooter.style.display = 'block';
    }
}

function renderCart() {
    if (!cartItemsList) return;
    updateCartSummary();
    if (cart.length === 0) return;

    cartItemsList.innerHTML = '';
    cart.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = 'cart-item';
        const imgHTML = item.imgSrc 
            ? `<img src="${item.imgSrc}" alt="${item.name}" class="cart-item-photo">`
            : item.svgSnippet;

        li.innerHTML = `
            <div class="cart-item-img">${imgHTML}</div>
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-cat">${item.cat}</div>
                <div class="cart-item-price">₹${item.price.toFixed(2)}</div>
                <div class="cart-item-qty">
                    <button class="qty-btn" data-action="dec" data-index="${index}">−</button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn" data-action="inc" data-index="${index}">+</button>
                </div>
            </div>
            <button class="cart-item-remove" data-index="${index}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
        `;
        cartItemsList.appendChild(li);
    });

    // Re-bind listeners (only when list changes)
    bindCartEvents();
}

function bindCartEvents() {
    cartItemsList.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const i = parseInt(btn.dataset.index);
            const qtySpan = btn.parentElement.querySelector('.qty-num');
            if (btn.dataset.action === 'inc') {
                cart[i].qty++;
                qtySpan.textContent = cart[i].qty;
                updateCartSummary();
            } else {
                cart[i].qty--;
                if (cart[i].qty <= 0) {
                    cart.splice(i, 1);
                    renderCart(); // Full re-render needed when item removed
                } else {
                    qtySpan.textContent = cart[i].qty;
                    updateCartSummary();
                }
            }
        });
    });

    cartItemsList.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            cart.splice(parseInt(btn.dataset.index), 1);
            renderCart();
        });
    });
}

// Initial render
renderCart();

// ─── 4. Card Quantity Selector Logic ──────────────────────────────────────────
document.querySelectorAll('.card-qty-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const numSpan = btn.parentElement.querySelector('.card-qty-num');
        let current = parseInt(numSpan.textContent);
        if (btn.dataset.action === 'inc') {
            numSpan.textContent = ++current;
        } else {
            if (current > 1) numSpan.textContent = --current;
        }
    });
});

// ─── 5. Add to Cart Logic + Fly-to-Bag Animation ─────────────────────────────
document.querySelectorAll('.card-quick-add').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();

        const id = parseInt(btn.dataset.id);
        const name = btn.dataset.name;
        const price = parseFloat(btn.dataset.price);
        const cat = btn.dataset.cat;
        const numSpan = btn.parentElement.querySelector('.card-qty-num');
        const qtyToAdd = parseInt(numSpan.textContent);

        // Capture Image OR SVG from the card for display in cart
        const card = btn.closest('.product-card');
        const imgEl = card.querySelector('.product-real-photo');
        const svgEl = card.querySelector('.product-svg-icon');
        
        const imgSrc = (imgEl && imgEl.style.display !== 'none') ? imgEl.getAttribute('src') : null;
        const svgSnippet = (!imgSrc && svgEl) ? svgEl.outerHTML : '';

        // Add to cart array or increment
        const existing = cart.find(c => c.id === id);
        if (existing) {
            existing.qty += qtyToAdd;
        } else {
            cart.push({ id, name, price, cat, qty: qtyToAdd, imgSrc, svgSnippet });
        }

        // Reset card qty back to 1
        numSpan.textContent = "1";

        // ─── Fly-to-Bag Animation ───────────────────────────────────────────
        if (window.gsap && cartToggle) {
            const imgWrap = card.querySelector('.product-img-wrap');
            const imgRect = imgWrap.getBoundingClientRect();
            const cartRect = cartToggle.getBoundingClientRect();

            // Create flying clone
            const flyEl = document.createElement('div');
            flyEl.className = 'fly-to-bag-clone';
            flyEl.style.cssText = `
                position: fixed;
                top: ${imgRect.top}px;
                left: ${imgRect.left}px;
                width: ${imgRect.width}px;
                height: ${imgRect.height}px;
                background: rgba(17,17,17,0.95);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 4px;
                z-index: 99999;
                pointer-events: none;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                will-change: transform, opacity;
            `;
            
            if (imgSrc) {
                flyEl.innerHTML = `<img src="${imgSrc}" style="width:100%; height:100%; object-fit:cover;">`;
            } else {
                flyEl.innerHTML = svgSnippet;
            }
            
            document.body.appendChild(flyEl);

            gsap.to(flyEl, {
                top: cartRect.top + 10,
                left: cartRect.left + 10,
                width: 20,
                height: 20,
                opacity: 0.2,
                borderRadius: '50%',
                duration: 0.8,
                ease: 'power3.in',
                onComplete: () => {
                    flyEl.remove();
                    renderCart();

                    // Cart icon bounce
                    gsap.fromTo(cartToggle,
                        { scale: 0.8 },
                        { scale: 1, duration: 0.5, ease: 'back.out(3)' }
                    );

                    // Auto-open cart on first item
                    if (cart.length === 1 && cart[0].qty === 1) {
                        setTimeout(openCart, 150);
                    }
                }
            });
        } else {
            renderCart();
        }

        // ─── Button Feedback ────────────────────────────────────────────────
        btn.classList.add('added');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg> Added`;

        setTimeout(() => {
            btn.classList.remove('added');
            btn.innerHTML = originalHTML;
        }, 2000);
    });
});

// ─── 4.5 Checkout Button ───────────────────────────────────────────────────
const checkoutBtn = document.querySelector('.btn-checkout');
if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
        alert("Proceeding to secure checkout... (This would redirect to Stripe/PayPal in production)");
    });
}

// ─── 5. Category Filtering ────────────────────────────────────────────────────
const filterBtns = document.querySelectorAll('.filter-btn[data-filter]');
const productCards = document.querySelectorAll('.product-card');
const resultsCount = document.getElementById('resultsCount');

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const filter = btn.dataset.filter;
        document.querySelectorAll('.filter-btn[data-filter]').forEach(b => b.classList.remove('active'));
        document.querySelectorAll(`.filter-btn[data-filter="${filter}"]`).forEach(b => b.classList.add('active'));
        filterProducts(filter);
        if (typeof closeMobileFilter === 'function') closeMobileFilter();
    });
});

function filterProducts(filter) {
    let visible = 0;
    productCards.forEach(card => {
        const cat = card.dataset.cat;
        const show = filter === 'all' || cat === filter;
        card.classList.toggle('hidden', !show);
        if (show) visible++;
    });
    

}

// ─── 5.5 Sorting Logic ────────────────────────────────────────────────────────
const sortBtns = document.querySelectorAll('.filter-btn[data-sort]');
sortBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const sortType = btn.dataset.sort;
        sortBtns.forEach(b => b.classList.remove('active-sort'));
        btn.classList.add('active-sort');
        sortProducts(sortType);
    });
});

function sortProducts(type) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.product-card'));

    cards.sort((a, b) => {
        const priceA = parseFloat(a.dataset.price);
        const priceB = parseFloat(b.dataset.price);
        const idA = parseInt(a.dataset.id);
        const idB = parseInt(b.dataset.id);

        if (type === 'price-asc') return priceA - priceB;
        if (type === 'price-desc') return priceB - priceA;
        if (type === 'new') return idB - idA;
        return idA - idB; // featured
    });

    cards.forEach(card => grid.appendChild(card));
}

// ─── 7. Mobile Filter Drawer ──────────────────────────────────────────────────
const mobileFilterBtn = document.getElementById('mobileFilterBtn');
const mobileFilterDrawer = document.getElementById('mobileFilterDrawer');
const mobileFilterOverlay = document.getElementById('mobileFilterOverlay');
const mobileFilterClose = document.getElementById('mobileFilterClose');

function openMobileFilter() {
    if (mobileFilterDrawer) {
        mobileFilterDrawer.classList.add('open');
        mobileFilterOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileFilter() {
    if (mobileFilterDrawer) {
        mobileFilterDrawer.classList.remove('open');
        mobileFilterOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

mobileFilterBtn && mobileFilterBtn.addEventListener('click', openMobileFilter);
mobileFilterClose && mobileFilterClose.addEventListener('click', closeMobileFilter);
mobileFilterOverlay && mobileFilterOverlay.addEventListener('click', closeMobileFilter);

// ─── 8. Navbar Scroll & Mobile Toggle ─────────────────────────────────────────
const navbar = document.getElementById('navbar');
const mobileToggle = document.getElementById('mobileToggle');

window.addEventListener('scroll', () => {
    if (navbar) {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        navbar.classList.toggle('active');
    });
}

// ─── 9. Auto-apply filter from URL ?cat= ─────────────────────────────────────
(function() {
    const params = new URLSearchParams(window.location.search);
    const cat = params.get('cat');
    if (cat && cat !== 'all') {
        const matchBtn = document.querySelector(`.filter-btn[data-filter="${cat}"]`);
        if (matchBtn) {
            matchBtn.click();
            setTimeout(() => {
                const grid = document.getElementById('productGrid');
                if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);
        }
    }
})();// ─── 10. High-Tech Scramble Effect (Glitch Links) ─────────────────────────────
(function() {
    const scrambleSymbols = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#%&*$0123456789";
    
    function initScramble(el) {
        const targetSpan = el.querySelector("span:nth-child(2)");
        if (!targetSpan) return;
        
        let interval = null;
        el.addEventListener("mouseenter", () => {
            const originalValue = el.dataset.value || el.innerText;
            let iteration = 0;
            clearInterval(interval);
            
            interval = setInterval(() => {
                targetSpan.innerText = originalValue.split("").map((letter, index) => {
                    if (index < iteration) return originalValue[index];
                    return scrambleSymbols[Math.floor(Math.random() * scrambleSymbols.length)].toUpperCase();
                }).join("");
                
                if (iteration >= originalValue.length) clearInterval(interval);
                iteration += 1 / 3;
            }, 30);
        });
    }

    // Initialize existing ones
    document.querySelectorAll(".glitch-link").forEach(initScramble);
    
    // Also handle dynamic counts in sidebar if they don't have the listener
    // (though we added it to the class above)
})();

// ─── 11. Magnetic Effect ──────────────────────────────────────────────────────
(function() {
    const magneticElements = document.querySelectorAll('.nav-links li a, .cart-toggle, .filter-btn, .card-quick-add');
    
    function updateMagnetic(e, el) {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        gsap.to(el, { x: x * 0.2, y: y * 0.2, duration: 0.6, ease: "power3.out" });
    }

    function resetMagnetic(el) {
        gsap.to(el, { x: 0, y: 0, duration: 1.2, ease: "elastic.out(1.0, 0.5)" });
    }

    magneticElements.forEach(el => {
        el.addEventListener('mousemove', (e) => updateMagnetic(e, el));
        el.addEventListener('mouseleave', () => resetMagnetic(el));
    });
})();



