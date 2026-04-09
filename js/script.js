// Register GSAP ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// 0. Initialize Lenis Smooth Scroll (Buttery Smooth Virtual Scroll)
const lenis = new Lenis({
    duration: 1.1, 
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), 
    smoothWheel: true,
    lerp: 0.1, // More responsive to remove perceived lag
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
    // Only scroll immediately if it's one of the product cards
    if (window.location.hash.match(/#(ceiling|wall-sculptures|ceiling-masterpieces|smart-lighting)/)) {
        setTimeout(() => {
            lenis.scrollTo(window.location.hash, { immediate: true });
            ScrollTrigger.refresh();
        }, 50);
    }
}


// 1. App Initialization & Page Load Animation
const hasSeenEntrance = sessionStorage.getItem('hasSeenEntrance');

// Immediate check to remove black overlay on navigation (don't wait for images)
if (hasSeenEntrance) {
    // We already moved the style injection to a separate turn if needed, 
    // but for now, we'll just force the style immediately.
    const style = document.createElement('style');
    style.innerHTML = '.transition-overlay { display: none !important; opacity: 0 !important; visibility: hidden !important; } .scroll-indicator { opacity: 1 !important; }';
    document.head.appendChild(style);
}

window.addEventListener("load", () => {
    document.body.classList.remove("loading");
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


// 2. Navbar Scroll State
const navbar = document.getElementById("navbar");
window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});


// 3. Scroll Content Reveal (General Sections)
const revealElements = document.querySelectorAll(".reveal");
revealElements.forEach((el) => {
    const targets = el.querySelectorAll('h2, h3, p, .btn-primary');
    const animationTargets = targets.length > 0 ? targets : [el];
    
    gsap.fromTo(animationTargets, 
        { opacity: 0, y: 60 }, 
        {
            opacity: 1,
            y: 0,
            duration: 1.5,
            ease: "power2.out", // Softened from back.out for a more liquid feel
            stagger: 0.1,
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
                    scrub: 0.8 // Snappier catch-up
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
    if (span.closest('.hero-title') || span.closest('.card')) return;
    gsap.from(span, { 
        y: "105%",
        opacity: 0,
        duration: 1.2, 
        ease: "back.out(1.2)",
        scrollTrigger: {
            trigger: span,
            start: "top 95%",
            toggleActions: "play reverse play reverse"
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
            toggleActions: "play reverse play reverse"
        }
    });
});


// 7. Stats Flicker Counter (Repeatable)
gsap.utils.toArray(".stat-number").forEach(num => {
    const target = parseInt(num.dataset.target);
    const counterObj = { value: 0 };
    gsap.to(counterObj, {
        value: target, duration: 3, ease: "power2.out",
        scrollTrigger: {
            trigger: num,
            start: "top 90%",
            toggleActions: "restart none none none",
            once: false
        },
        onUpdate: () => {
            let current = Math.floor(counterObj.value);
            if (Math.random() < 0.12 && current < target) {
                num.innerText = Math.floor(Math.random() * target * 1.1);
                num.classList.add("flicker-active");
                setTimeout(() => num.classList.remove("flicker-active"), 60);
            } else {
                num.innerText = current;
            }
        },
        onComplete: () => {
            num.innerText = target;
            num.classList.add("flicker-active");
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

// Custom Cursor — Advanced Dual-Layer Smooth Trailing
const cursor = document.createElement('div');
cursor.className = 'custom-cursor';
const cursorDot = document.createElement('div');
cursorDot.className = 'cursor-dot';
document.body.appendChild(cursor);
document.body.appendChild(cursorDot);

let mouseX = window.innerWidth  / 2;
let mouseY = window.innerHeight / 2;
let curX   = mouseX;
let curY   = mouseY;

// Track real mouse position
window.addEventListener('mousemove', e => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    // Desktop only: show cursor on first movement
    cursor.classList.add('is-visible');
    cursorDot.classList.add('is-visible');
});

// Track touch position for mobile — tactile feedback
window.addEventListener('touchstart', e => {
    mouseX = e.touches[0].clientX;
    mouseY = e.touches[0].clientY;
    cursor.classList.add('touch-active', 'is-visible');
    cursorDot.classList.add('touch-active', 'is-visible');
}, { passive: true });

window.addEventListener('touchmove', e => {
    mouseX = e.touches[0].clientX;
    mouseY = e.touches[0].clientY;
}, { passive: true });

window.addEventListener('touchend', () => {
    cursor.classList.remove('touch-active', 'is-visible', 'active');
    cursorDot.classList.remove('touch-active', 'is-visible', 'active');
}, { passive: true });

// Ensure visibility is removed on click for absolute safety on mobile taps
window.addEventListener('click', () => {
    // Only remove on mobile if needed, or across both for universal snap-back
    if (window.matchMedia("(pointer: coarse)").matches) {
        cursor.classList.remove('touch-active', 'is-visible', 'active');
        cursorDot.classList.remove('touch-active', 'is-visible', 'active');
    }
});

// Lerp factor — lower = more fluid/liquid, higher = snappier
const LERP = 1.0; // Set to 1.0 for zero delay as requested
const TOUCH_LERP = 1.0; 

function lerpCursor() {
    const isTouch = cursor.classList.contains('touch-active');
    const currentLerp = isTouch ? TOUCH_LERP : LERP;

    // Outer ring — liquid lag (now instantaneous)
    curX  += (mouseX - curX) * currentLerp;
    curY  += (mouseY - curY) * currentLerp;
    
    cursor.style.transform = `translate3d(${curX}px, ${curY}px, 0) translate3d(-50%, -50%, 0)`;
    cursorDot.style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0) translate3d(-50%, -50%, 0)`;
    
    requestAnimationFrame(lerpCursor);
}
requestAnimationFrame(lerpCursor);

// Hover states
document.querySelectorAll('a, button, .card').forEach(el => {
    el.addEventListener('mouseenter', () => {
        cursor.classList.add('active');
        cursorDot.classList.add('active');
    });
    el.addEventListener('mouseleave', () => {
        cursor.classList.remove('active');
        cursorDot.classList.remove('active');
    });
});
// 11. Custom Smooth Navigation & Form Logic
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = this.getAttribute('href');
        lenis.scrollTo(target);
    });
});

// 11B. Instant Page Transitions (Redirected immediately as requested)
document.querySelectorAll('.card-link, .back-btn').forEach(link => {
    link.addEventListener('click', (e) => {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#')) {
            // No e.preventDefault() here to allow standard quick navigation
            // or just let the default browser behavior handle it.
            // But if we want to ensure zero animation, we just remove the listener 
            // or just use window.location.href immediately if we kept preventDefault.
        }
    });
});

const formSubmit = document.querySelector(".form-submit");
if (formSubmit) {
    formSubmit.addEventListener("click", (e) => {
        const btn = e.target;
        const originalText = btn.innerText;
        btn.innerText = "MESSAGE SENT";
        btn.classList.add("sent");
        
        gsap.fromTo(btn, { scale: 0.95 }, { scale: 1.05, duration: 0.2, yoyo: true, repeat: 1 });
        
        setTimeout(() => {
            btn.innerText = originalText;
            btn.classList.remove("sent");
        }, 3000);
    });
}
