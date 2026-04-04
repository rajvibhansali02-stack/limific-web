// Register GSAP ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// 0. Initialize Lenis Smooth Scroll (Buttery Smooth Virtual Scroll)
const lenis = new Lenis({
    duration: 1.2, 
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), 
    smoothWheel: true,
    lerp: 0.1,
});

lenis.on('scroll', ScrollTrigger.update);

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}
requestAnimationFrame(raf);


// 1. App Initialization & Page Load Animation
window.addEventListener("load", () => {
    document.body.classList.remove("loading");
    
    const tl = gsap.timeline({ defaults: { ease: "power4.out" } });
    
    tl.to(".transition-overlay", { opacity: 0, duration: 1.2, ease: "power2.inOut" })
      .to(".scroll-indicator", { opacity: 1, duration: 1 }, "-=0.5");

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
            duration: 1.2,
            ease: "back.out(1.2)",
            stagger: 0.15,
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
heroEntranceTl.to(".text-mask span", {
    y: 0,
    stagger: 0.3,
    duration: 1.5,
    ease: "back.out(1.4)",
    delay: 0.6
})
.fromTo(".reveal-tagline", 
    { opacity: 0, y: 20 },
    { opacity: 1, y: 0, duration: 1.2, ease: "back.out(1.2)" },
    "-=0.8"
);


// 5. Specialized Stacked Cards Animation (Masterpiece Collections)
const cards = gsap.utils.toArray(".cards-stack .card");
if (cards.length > 0) {
    const stackTl = gsap.timeline({
        scrollTrigger: {
            trigger: ".collections",
            start: "top top",
            end: "+=600%", // MAX PERSISTENCE: Section stays pinned for longer
            pin: true,
            scrub: 1.5,
            anticipatePin: 1,
            snap: {
                snapTo: "labels", 
                duration: { min: 0.2, max: 0.4 },
                delay: 0,
                ease: "expo.out"
            }
        }
    });

    // 1. Initial Quick Header Reveal
    stackTl.fromTo(".collections .section-header .text-mask span", 
        { y: "105%", opacity: 0, filter: "blur(10px)" },
        { y: 0, opacity: 1, filter: "blur(0px)", duration: 0.7, ease: "back.out(1.2)" }
    ).addLabel("header");

    cards.forEach((card, i) => {
        const landPos = i * 40; 
        const maskedSpans = card.querySelectorAll('.text-mask span');

        if (i === 0) {
            stackTl.fromTo(card, 
                { y: 20, opacity: 0 },
                { y: landPos, opacity: 1, duration: 0.5, ease: "back.out(1.2)" },
                "<0.1"
            ).addLabel(`card-${i+1}-arrival`);
            
            stackTl.fromTo(maskedSpans, { y: "105%", opacity: 0, filter: "blur(5px)" }, { y: 0, opacity: 1, filter: "blur(0px)", duration: 0.7, stagger: 0.08, ease: "back.out(1.2)" }, "<");
        } else {
            stackTl.fromTo(card, 
                { y: 400, opacity: 0, zIndex: i + 1 },
                { y: landPos, opacity: 1, duration: 0.6, ease: "back.out(1.2)", zIndex: i + 1 }
            ).addLabel(`card-${i+1}-arrival`);

            stackTl.fromTo(maskedSpans, { y: "105%", opacity: 0, filter: "blur(5px)" }, { y: 0, opacity: 1, filter: "blur(0px)", duration: 0.8, stagger: 0.1, ease: "back.out(1.2)" }, "<0.1");
        }
        stackTl.to({}, { duration: 0.4 }); 
    });

    // MASSIVE FINAL HOLD: Keep the last card visible until definitively done scrolling
    stackTl.to({}, { duration: 5 }).addLabel("end");
}


// 6. Global Repeatable Scroll Triggers
// A. Masked Slide Reveal for all Headers
gsap.utils.toArray(".text-mask span").forEach(span => {
    if (span.closest('.hero-title')) return;
    gsap.fromTo(span, { y: "105%" }, { 
        y: 0, duration: 1.2, ease: "back.out(1.2)",
        scrollTrigger: {
            trigger: span,
            start: "top 92%",
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


// 8. Interactive & Visual Polish
// Magnetic Elements
const magneticElements = document.querySelectorAll('.nav-links li a, .btn-primary, .glitch-link, .hero-title');
magneticElements.forEach(el => {
    el.addEventListener('mousemove', (e) => {
        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        const force = el.classList.contains('hero-title') ? 0.4 : 0.3;
        gsap.to(el, { x: x * force, y: y * force, duration: 0.4, ease: "power2.out" });
    });
    el.addEventListener('mouseleave', () => {
        gsap.to(el, { x: 0, y: 0, duration: 0.6, ease: "elastic.out(1.2, 0.3)" });
    });
});

// Glitch Effect
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

// Ambient Glow Follower
const heroSection = document.querySelector(".hero");
const ambientGlow = document.getElementById("ambientGlow");
if (heroSection && ambientGlow) {
    heroSection.addEventListener("mousemove", (e) => {
        const rect = heroSection.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        gsap.to(ambientGlow, { x: x, y: y, duration: 0.4, ease: "power2.out" });
    });
}

// 9. Parallax & Atmosphere
gsap.to(".orb-purple", { yPercent: 50, scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 1.5 } });
gsap.to(".orb-orange", { yPercent: -50, scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 1.5 } });
gsap.to(".exp-bg-wrapper", { yPercent: 20, ease: "none", scrollTrigger: { trigger: ".experience", start: "top bottom", end: "bottom top", scrub: true } });

// 10. Scroll Hue-Shifting Background Atmosphere
gsap.to(".bg-gradients", {
    backgroundColor: "rgba(10, 0, 20, 0.3)", 
    scrollTrigger: {
        trigger: "body",
        start: "top top",
        end: "bottom bottom",
        scrub: 1
    }
});

gsap.to(".orb-purple", {
    filter: "hue-rotate(150deg)", 
    scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: true }
});

gsap.to(".orb-orange", {
    filter: "hue-rotate(-60deg)", 
    scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: true }
});

// Custom Cursor (Subtle)
const cursor = document.createElement('div');
cursor.className = 'custom-cursor';
document.body.appendChild(cursor);
gsap.set(cursor, { xPercent: -50, yPercent: -50 });
window.addEventListener('mousemove', e => {
    gsap.to(cursor, { x: e.clientX, y: e.clientY, duration: 0.1, ease: "power2.out" });
});
document.querySelectorAll('a, button, .card').forEach(el => {
    el.addEventListener('mouseenter', () => cursor.classList.add('active'));
    el.addEventListener('mouseleave', () => cursor.classList.remove('active'));
});
