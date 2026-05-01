// Register GSAP ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// 0. Initialize Lenis Smooth Scroll (Buttery Smooth Virtual Scroll)
const lenis = new Lenis({
    duration: 1.4,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
    lerp: 0.075, // Slightly gentler for a more "liquid" feel
    wheelMultiplier: 1.0,
    infinite: false,
});

lenis.on('scroll', ScrollTrigger.update);

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}
requestAnimationFrame(raf);

// Handle Deep Linking / Back Button Scroll
if (window.location.hash) {
    const hash = window.location.hash;
    // Include all main sections: shop, about, contact, and product categories
    if (hash.match(/#(shop|about|contact|boutique|ceiling|wall-sculptures|ceiling-masterpieces|smart-lighting)/)) {
        // Essential: Scroll immediately to reduce perceived delay
        lenis.scrollTo(hash, { immediate: true, force: true });
    }
}


// 1. App Initialization & Page Load Animation
const hasSeenEntrance = sessionStorage.getItem('hasSeenEntrance');

// Immediate check to remove black overlay on navigation (don't wait for images)
if (hasSeenEntrance) {
    const style = document.createElement('style');
    style.innerHTML = '.transition-overlay { display: none !important; opacity: 0 !important; visibility: hidden !important; } .scroll-indicator { opacity: 1 !important; }';
    document.head.appendChild(style);
}

window.addEventListener("load", () => {
    document.body.classList.remove("loading");

    // Crucial: Refresh GSAP and re-scroll on load to account for final layout heights
    if (window.location.hash) {
        ScrollTrigger.refresh();
        lenis.scrollTo(window.location.hash, { immediate: true, force: true });
    }

    const tl = gsap.timeline({ defaults: { ease: "power4.out" } });

    if (!hasSeenEntrance) {
        // First entry to the site: Play the slow, premium fade-in
        tl.to(".transition-overlay", { opacity: 0, duration: 1.2, ease: "power2.inOut" })
            .to(".scroll-indicator", { opacity: 1, duration: 1 }, "-=0.5");
        sessionStorage.setItem('hasSeenEntrance', 'true');
    }

    // Static Orbs continuous movement
    gsap.to(".orb-purple", { x: '20vw', y: '10vh', duration: 15, repeat: -1, yoyo: true, ease: "sine.inOut" });
    gsap.to(".orb-orange", { x: '-10vw', y: '-20vh', duration: 20, repeat: -1, yoyo: true, ease: "sine.inOut" });
});


// 2. Navbar Scroll & Mobile Toggle
const navbar = document.getElementById("navbar");
const mobileToggle = document.getElementById("mobileToggle");

window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});

if (mobileToggle) {
    mobileToggle.addEventListener("click", () => {
        navbar.classList.toggle("active");
    });
}


// 3. Scroll Content Reveal (General Sections)
const revealElements = document.querySelectorAll(".reveal");
revealElements.forEach((el) => {
    // Specialized handling for contact to animate as a single block
    const isContact = el.classList.contains('contact');
    const targets = isContact ? [el.querySelector('.contact-container')] : el.querySelectorAll('h2, h3, p, .btn-primary, .ethos-grid');
    const animationTargets = targets.length > 0 ? targets : [el];

    gsap.fromTo(animationTargets,
        { opacity: 0, y: 60 },
        {
            opacity: 1,
            y: 0,
            duration: 1.2,
            ease: "power2.out",
            stagger: isContact ? 0 : 0.1,
            scrollTrigger: {
                trigger: el,
                start: "top 85%",
                toggleActions: "play none none reverse"
            },
            onStart: () => gsap.set(el, { visibility: "visible", opacity: 1 })
        }
    );
});


// 4. Hero Section Entrance (Masked Word-by-Word - Bouncy)
const heroEntranceTl = gsap.timeline();
heroEntranceTl.from(".hero .text-mask span", {
    y: "110%",
    opacity: 0,
    stagger: 0.3,
    duration: 1.5,
    ease: "back.out(1.4)",
    delay: 0.6
})
    .fromTo(".reveal-tagline",
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 1.5, ease: "power2.out" },
        "-=1.0"
    );


// 5. Specialized Stacked Cards Animation (Masterpiece Collections)
// Refactored to work with CSS Sticky for maximum flexibility
const cards = gsap.utils.toArray(".card");

// Pre-set all card text spans to visible immediately — CSS fallback
// so they're never stuck invisible if scroll trigger misfires
document.querySelectorAll('.card .text-mask span').forEach(span => {
    gsap.set(span, { y: 0, opacity: 1, filter: "none" });
});

if (cards.length > 0) {
    cards.forEach((card, i) => {
        // Entrance reveal: animate from hidden to visible
        const maskedSpans = card.querySelectorAll('.text-mask span');

        // Only animate if the card hasn't entered the viewport yet
        const cardRect = card.getBoundingClientRect();
        if (cardRect.top > window.innerHeight) {
            // Card is below the fold — safe to animate in
            gsap.fromTo(maskedSpans,
                { y: "105%", opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
                    stagger: 0.1,
                    ease: "power2.out",
                    clearProps: "transform,opacity",
                    scrollTrigger: {
                        trigger: card,
                        start: "top 90%",
                        toggleActions: "play none none none"
                    }
                }
            );
        }

        // Core Stacking Effect: Scale down previous card as next one arrives
        if (i < cards.length - 1) {
            gsap.to(card, {
                scale: 0.94,
                opacity: 0.5,
                // Removed blur filter for buttery performance
                scrollTrigger: {
                    trigger: cards[i + 1],
                    start: "top top",
                    end: "top 30%",
                    scrub: 1.2 // Smoother catch-up for a premium feel
                }
            });
        }

        // 5B. Card Click Navigation
        card.addEventListener("click", (e) => {
            // If the user clicked a link inside the card, let the link handle it
            if (e.target.closest('a')) return;

            const link = card.querySelector(".card-link");
            if (link) {
                const href = link.getAttribute("href");
                window.location.href = href;
            }
        });
    });
}


// 6. Global Repeatable Scroll Triggers
// A. Masked Slide Reveal for all Headers
gsap.utils.toArray(".text-mask span").forEach(span => {
    if (span.closest('.hero') || span.closest('.card')) return;
    gsap.from(span, {
        y: "105%",
        opacity: 0,
        duration: 1.2,
        ease: "back.out(1.2)",
        scrollTrigger: {
            trigger: span,
            start: "top 95%",
            toggleActions: "play none none none"
        }
    });
});

// B. Fade/Slide Reveal for Paragraphs & Buttons
gsap.utils.toArray(".reveal-tagline, .reveal-btn, .about p, .contact p, .contact-info p, .exp-content p, .exp-content button").forEach(el => {
    if (el.classList.contains('hero-btn') || el.classList.contains('hero-tagline')) return;
    gsap.fromTo(el, { opacity: 0, y: 60 }, {
        opacity: 1, y: 0, duration: 1.2, ease: "back.out(1.2)",
        scrollTrigger: {
            trigger: el,
            start: "top 95%",
            toggleActions: "play none none none"
        }
    });
});


// 7. Stats Counter (Smooth Elevation)
gsap.utils.toArray(".stat-number").forEach(num => {
    const target = parseInt(num.dataset.target);
    const counterObj = { value: 0 };
    gsap.to(counterObj, {
        value: target,
        duration: 2.5,
        ease: "power2.out",
        scrollTrigger: {
            trigger: num,
            start: "top 90%",
            toggleActions: "restart none none none",
            once: false
        },
        onUpdate: () => {
            num.innerText = Math.floor(counterObj.value);
        },
        onComplete: () => {
            num.innerText = target;
        }
    });
});


// 8. Interactive & Visual Polish (High-End Tactile Interaction)
const magneticElements = document.querySelectorAll('.nav-links li a, .btn-primary, .glitch-link, .hero-title');
const heroSection = document.querySelector(".hero");
const ambientGlow = document.getElementById("ambientGlow");
const heroTitle = document.querySelector(".hero-title");

function updateMagnetic(e, el) {
    let x, y;
    if (e.type.startsWith('touch')) {
        x = e.touches[0].clientX;
        y = e.touches[0].clientY;
    } else {
        x = e.clientX;
        y = e.clientY;
    }

    const rect = el.getBoundingClientRect();
    const tX = x - rect.left - rect.width / 2;
    const tY = y - rect.top - rect.height / 2;
    const force = el.classList.contains('hero-title') ? 0.25 : 0.22;
    gsap.to(el, { x: tX * force, y: tY * force, duration: 0.6, ease: "power3.out" }); // More liquid duration

    // Handle Ambient Glow separately (if inside hero)
    if (heroSection && ambientGlow) {
        const hRect = heroSection.getBoundingClientRect();
        gsap.to(ambientGlow, { x: x - hRect.left, y: y - hRect.top, duration: 0.8, ease: "power3.out" });
    }
}

function resetMagnetic(el) {
    gsap.to(el, { x: 0, y: 0, duration: 1.2, ease: "elastic.out(1.0, 0.5)" });
}

magneticElements.forEach(el => {
    el.addEventListener('mousemove', (e) => updateMagnetic(e, el));
    el.addEventListener('mouseleave', () => resetMagnetic(el));
});


// High-Tech Scramble Effect (Links)
const scrambleSymbols = "ABCDEFGHIJKLMNOPQRSTUVWXYZ#%&*$0123456789";
document.querySelectorAll(".glitch-link").forEach(link => {
    const targetSpan = link.querySelector("span:nth-child(2)");
    if (!targetSpan) return;
    const originalValue = link.dataset.value;
    let interval = null;
    link.addEventListener("mouseenter", () => {
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
});

// 9. Parallax & Atmosphere
gsap.to(".orb-purple", { yPercent: 50, scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 0.5 } });
gsap.to(".orb-orange", { yPercent: -50, scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 0.5 } });
gsap.to(".exp-bg-wrapper", { yPercent: 20, ease: "none", scrollTrigger: { trigger: ".experience", start: "top bottom", end: "bottom top", scrub: 0.5 } });

// 10. Scroll Hue-Shifting Background Atmosphere
gsap.to(".bg-gradients", {
    backgroundColor: "rgba(10, 0, 20, 0.3)",
    scrollTrigger: {
        trigger: "body",
        start: "top top",
        end: "bottom bottom",
        scrub: 0.5
    }
});

gsap.to(".orb-purple", {
    filter: "hue-rotate(150deg)",
    scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 0.5 }
});

gsap.to(".orb-orange", {
    filter: "hue-rotate(-60deg)",
    scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 0.5 }
});

// ─── Custom Cursor — The Spectral Liquid Ribbon ───
(function initializeSpectralCursor() {
    // Skip initialization on touch devices or mobile-sized screens
    if (window.matchMedia('(pointer: coarse)').matches || window.innerWidth < 1024) {
        return;
    }

    const dot = document.createElement('div');

    const glow = document.createElement('div');
    dot.className = 'cursor-dot';
    glow.className = 'cursor-glow';
    document.body.appendChild(dot);
    document.body.appendChild(glow);

    // Create 10 trail dots
    const trailDots = [];
    const trailCount = 10;
    for (let i = 0; i < trailCount; i++) {
        const td = document.createElement('div');
        td.className = 'cursor-trail-dot';
        const size = 10 - i * 0.75;
        const opacity = 0.5 - i * 0.04;
        const hue = 20 + (i / trailCount) * 25; // Warm Sunset → Gold Ember
        td.style.width = `${size}px`;
        td.style.height = `${size}px`;
        td.style.background = `hsla(${hue}, 85%, 72%, ${opacity})`;
        td.style.boxShadow = `0 0 ${size * 2.5}px hsla(${hue}, 85%, 72%, ${opacity * 0.7})`;
        document.body.appendChild(td);
        trailDots.push({ el: td, x: 0, y: 0 });
    }

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let dotX = mouseX, dotY = mouseY;
    let glowX = mouseX, glowY = mouseY;

    window.addEventListener('mousemove', e => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        dot.classList.add('is-visible');
        glow.classList.add('is-visible');
        trailDots.forEach(d => d.el.classList.add('is-visible'));
    });

    // Physics Settings (Synced with Lumific-office)
    const GLOW_LAG = 0.12;
    const DOT_LAG = 1.0; // Real-time alignment, no delay
    const TRAIL_LAG = 0.35;

    function animate() {
        // Core and Glow strictly follow mouseX/Y
        dotX += (mouseX - dotX) * DOT_LAG;
        dotY += (mouseY - dotY) * DOT_LAG;
        glowX += (mouseX - glowX) * GLOW_LAG;
        glowY += (mouseY - glowY) * GLOW_LAG;

        dot.style.transform = `translate3d(${dotX}px, ${dotY}px, 0) translate3d(-50%, -50%, 0)`;
        glow.style.transform = `translate3d(${glowX}px, ${glowY}px, 0) translate3d(-50%, -50%, 0)`;

        // Trail dots — each follows the one before it
        let prevX = dotX;
        let prevY = dotY;

        trailDots.forEach((d) => {
            d.x += (prevX - d.x) * TRAIL_LAG;
            d.y += (prevY - d.y) * TRAIL_LAG;
            d.el.style.left = `${d.x}px`;
            d.el.style.top = `${d.y}px`;
            d.el.style.transform = `translate3d(-50%, -50%, 0)`;
            prevX = d.x;
            prevY = d.y;
        });

        requestAnimationFrame(animate);
    }
    animate();

    // Interaction Listeners (Visual Feedback Only)
    function bindInteractions() {
        const links = document.querySelectorAll('a, button, .card, .glitch-link, .scene-btn');
        links.forEach(link => {
            link.addEventListener('mouseenter', () => glow.classList.add('active'));
            link.addEventListener('mouseleave', () => glow.classList.remove('active'));
        });
    }
    bindInteractions();
})();


// ─── Smart Lighting Interaction Logic ───
(function () {
    const smartToggle = document.getElementById('smartToggle');
    const brInput = document.getElementById('brInput');
    const cctInput = document.getElementById('cctInput');
    const lightBeam = document.getElementById('lightBeam');
    const ambientSpill = document.getElementById('ambientSpill');
    const sourceChip = document.getElementById('sourceChip');
    const brLabel = document.getElementById('brLabel');
    const cctLabel = document.getElementById('cctLabel');
    const statusLabel = document.getElementById('statusLabel');
    const appControls = document.getElementById('appControls');
    const sceneBtns = document.querySelectorAll('.scene-btn');

    const sliders = {
        brightness: { input: brInput, fill: document.getElementById('brFill'), thumb: document.getElementById('brThumb'), label: brLabel },
        cct: { input: cctInput, thumb: document.getElementById('cctThumb'), label: cctLabel }
    };

    let state = {
        isOn: true,
        brightness: 85,
        cct: 4000
    };

    function getCCTColorLiteral(temp) {
        if (temp <= 4000) {
            const ratio = (temp - 2700) / 1300;
            return `rgb(255, ${Math.round(174 + (235 - 174) * ratio)}, ${Math.round(0 + (214 - 0) * ratio)})`;
        } else {
            const ratio = (temp - 4000) / 2500;
            return `rgb(${Math.round(255 + (160 - 255) * ratio)}, ${Math.round(235 + (216 - 235) * ratio)}, ${Math.round(214 + (239 - 214) * ratio)})`;
        }
    }

    function updateUI() {
        const color = getCCTColorLiteral(state.cct);

        if (state.isOn) {
            gsap.to(lightBeam, {
                opacity: (state.brightness / 100) * 0.4,
                backgroundImage: `linear-gradient(to bottom, ${color} 0%, transparent 100%)`,
                duration: 0.3
            });
            gsap.to(ambientSpill, {
                opacity: (state.brightness / 100) * 0.2,
                backgroundImage: `radial-gradient(circle at center, ${color} 0%, transparent 70%)`,
                duration: 0.3
            });
            gsap.to(sourceChip, { backgroundColor: color, boxShadow: `0 0 20px ${color}`, duration: 0.3 });
            appControls.style.opacity = "1";
            appControls.style.pointerEvents = "auto";
            smartToggle.classList.add('active');
        } else {
            gsap.to([lightBeam, ambientSpill], { opacity: 0, duration: 0.3 });
            gsap.to(sourceChip, { backgroundColor: "#333", boxShadow: "none", duration: 0.3 });
            appControls.style.opacity = "0.3";
            appControls.style.pointerEvents = "none";
            smartToggle.classList.remove('active');
        }

        statusLabel.innerText = state.isOn ? `On • ${state.brightness}%` : 'Off';
        brLabel.innerText = `${state.brightness}%`;
        cctLabel.innerText = `${state.cct}K`;

        sliders.brightness.fill.style.width = `${state.brightness}%`;

        // Fix for thumb going out of bounds
        const brThumbPos = (state.brightness / 100) * (sliders.brightness.input.parentElement.offsetWidth - 42);
        sliders.brightness.thumb.style.left = `${brThumbPos + 4}px`;

        const cctPercent = (state.cct - 2700) / 3800;
        const cctThumbPos = cctPercent * (sliders.cct.input.parentElement.offsetWidth - 42);
        sliders.cct.thumb.style.left = `${cctThumbPos + 4}px`;
    }

    if (smartToggle && brInput && cctInput && lightBeam) {
        smartToggle.addEventListener('click', () => {
            state.isOn = !state.isOn;
            updateUI();
        });

        brInput.addEventListener('input', (e) => {
            state.brightness = parseInt(e.target.value);
            updateUI();
        });

        cctInput.addEventListener('input', (e) => {
            state.cct = parseInt(e.target.value);
            updateUI();
        });

        if (sceneBtns.length > 0) {
            sceneBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    sceneBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    state.brightness = parseInt(btn.dataset.br);
                    state.cct = parseInt(btn.dataset.cct);
                    state.isOn = true;
                    brInput.value = state.brightness;
                    cctInput.value = state.cct;
                    updateUI();
                });
            });
        }

        updateUI();
    }
})();


// 11. Custom Smooth Navigation & Form Logic
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = this.getAttribute('href');

        // Close mobile menu on click
        navbar.classList.remove("active");

        const scrollOffset = target === '#about' ? 0 : -80;
        lenis.scrollTo(target, { offset: scrollOffset });
    });
});

// 11. Functional Form Submission Handler (PURE PHP)
const contactForms = document.querySelectorAll(".contact-form");

contactForms.forEach(form => {
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const btn = form.querySelector(".form-submit");
        const originalText = btn.innerText;
        const formData = new FormData(form);

        // Visual feedback
        btn.innerText = "SENDING...";
        btn.disabled = true;

        try {
            // This sends data strictly to your own PHP script
            const response = await fetch('send_email.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json().catch(() => ({ message: "Invalid Server Response" }));

            if (response.ok) {
                btn.innerText = "MESSAGE SENT!";
                btn.style.backgroundColor = "#4CAF50"; // Green for success
                btn.style.color = "#fff";
                form.reset();
            } else {
                throw new Error("Failed");
            }
        } catch (error) {
            console.error("Form Error:", error);
            btn.innerText = "ERROR - RETRY";
            btn.style.backgroundColor = "#f44336"; // Red for error
            btn.style.color = "#fff";
        } finally {
            setTimeout(() => {
                btn.innerText = originalText;
                btn.disabled = false;
                btn.style.backgroundColor = ""; // Reset background
                btn.style.color = "";
            }, 6000);
        }
    });
});


// 12. Swiper Variants Carousel Initialization
function initVariantsCarousel() {
    const swiperContainers = document.querySelectorAll('.variantsCarousel');
    
    swiperContainers.forEach(container => {
        new Swiper(container, {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 3,
            loop: true,
            loopedSlides: 12,
            speed: 8000,
            allowTouchMove: true,
            coverflowEffect: {
                rotate: 0,
                stretch: 0,
                depth: 100,
                modifier: 2.5,
                slideShadows: false,
            },
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
            },
            keyboard: {
                enabled: true,
            },
            // Pure linear movement for infinite treadmill effect
            on: {
                init: function() {
                    this.el.style.transitionTimingFunction = 'linear';
                },
                autoplayStop: function() {
                    this.el.style.transitionTimingFunction = '';
                },
                autoplayStart: function() {
                    this.el.style.transitionTimingFunction = 'linear';
                }
            }
        });
    });
}

// Call initialization on load
window.addEventListener('load', initVariantsCarousel);




