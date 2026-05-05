<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
$error = isset($_GET['error']) ? $_GET['error'] : false;
$success = isset($_GET['success']) ? $_GET['success'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumific | Join the Boutique</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #050505;
            --accent: #E2B04E;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --error: #ff4d4d;
            --success: #00e676;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Ambient Orbs */
        .orb {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(120px);
            z-index: -1;
            opacity: 0.2;
            animation: move 25s infinite alternate;
        }

        .orb-1 { background: var(--accent); top: -200px; right: -200px; }
        .orb-2 { background: #3b2b8a; bottom: -200px; left: -200px; animation-delay: -7s; }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(100px, 100px) scale(1.2); }
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            perspective: 1000px;
        }

        .auth-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border: 1px solid var(--border);
            padding: 35px 30px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
            position: relative;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 600;
            letter-spacing: 6px;
            margin-bottom: 8px;
            display: block;
            background: linear-gradient(to bottom, #fff, rgba(255,255,255,0.4));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tagline {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.4);
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        .tabs {
            display: flex;
            background: rgba(255,255,255,0.05);
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 25px;
            position: relative;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            color: rgba(255,255,255,0.5);
            font-family: inherit;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            z-index: 1;
            transition: color 0.3s;
        }

        .tab-btn.active {
            color: #000;
        }

        .tab-slider {
            position: absolute;
            top: 5px;
            left: 5px;
            width: calc(50% - 5px);
            height: calc(100% - 10px);
            background: var(--accent);
            border-radius: 10px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .auth-form {
            display: none;
            animation: fadeIn 0.5s ease forwards;
        }

        .auth-form.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.3);
            margin-bottom: 6px;
            margin-left: 4px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.2);
            font-size: 0.85rem;
        }

        .otp-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--accent);
            color: #000;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .otp-btn:disabled {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.3);
            cursor: not-allowed;
        }

        input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            padding: 12px 16px 12px 42px;
            border-radius: 12px;
            color: #fff;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 25px rgba(226, 176, 78, 0.08);
        }

        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(226, 176, 78, 0.25);
            filter: brightness(1.1);
        }

        .alert {
            padding: 14px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
        }

        .alert-error {
            background: rgba(255, 77, 77, 0.1);
            color: var(--error);
            border-color: rgba(255, 77, 77, 0.2);
        }

        .alert-success {
            background: rgba(0, 230, 118, 0.1);
            color: var(--success);
            border-color: rgba(0, 230, 118, 0.2);
        }

        .back-link {
            margin-top: 35px;
            display: inline-block;
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            letter-spacing: 1px;
        }

        .back-link:hover {
            color: var(--accent);
            transform: translateX(-5px);
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 40px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <div class="auth-card">
            <a href="index.html" class="brand-logo">LUMIFIC</a>
            <p class="tagline">Illuminate Your Lifestyle</p>

            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <div class="tabs">
                <div class="tab-slider" id="tabSlider"></div>
                <button class="tab-btn active" onclick="switchTab('login')">SIGN IN</button>
                <button class="tab-btn" onclick="switchTab('signup')">JOIN US</button>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="auth-form active" action="auth_user.php" method="POST">
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="name@domain.com" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Security Key</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Enter Boutique</button>
            </form>

            <!-- Sign Up Form -->
            <form id="signupForm" class="auth-form" action="signup_process.php" method="POST">
                <div class="input-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="name" placeholder="John Doe" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="signupEmail" name="email" placeholder="name@domain.com" required>
                        <button type="button" class="otp-btn" onclick="sendOTP('email')">Verify</button>
                    </div>
                </div>
                <div class="input-group" id="emailOtpGroup" style="display:none">
                    <label>Email OTP</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-key"></i>
                        <input type="text" name="email_otp" placeholder="6-digit code">
                    </div>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone"></i>
                        <input type="tel" id="signupPhone" name="phone" placeholder="10-digit number" pattern="[0-9]{10}" required>
                        <button type="button" class="otp-btn" onclick="sendOTP('phone')">Verify</button>
                    </div>
                </div>
                <div class="input-group" id="phoneOtpGroup" style="display:none">
                    <label>Phone OTP</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-key"></i>
                        <input type="text" name="phone_otp" placeholder="6-digit code">
                    </div>
                </div>
                <div class="input-group">
                    <label>Create Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Create Account</button>
            </form>

            <a href="index.html" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Return to Site
            </a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            const slider = document.getElementById('tabSlider');
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const btns = document.querySelectorAll('.tab-btn');

            if (tab === 'login') {
                slider.style.transform = 'translateX(0)';
                loginForm.classList.add('active');
                signupForm.classList.remove('active');
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
            } else {
                slider.style.transform = 'translateX(100%)';
                loginForm.classList.remove('active');
                signupForm.classList.add('active');
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
            }
        }

        // Auto-switch to signup if coming from signup error or similar
        <?php if(isset($_GET['mode']) && $_GET['mode'] === 'signup'): ?>
            switchTab('signup');
        <?php endif; ?>

        async function sendOTP(type) {
            const email = document.getElementById('signupEmail').value;
            const phone = document.getElementById('signupPhone').value;
            const target = type === 'email' ? email : phone;
            const btn = event.target;

            if (!target) {
                alert(`Please enter your ${type} first.`);
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Sending...';

            try {
                const response = await fetch('send_otp.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `type=${type}&target=${encodeURIComponent(target)}`
                });
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById(`${type}OtpGroup`).style.display = 'block';
                    btn.textContent = 'Sent';
                    alert(`OTP sent to your ${type}. (Check console for demo code)`);
                    console.log(`[DEMO] ${type.toUpperCase()} OTP: ${data.otp}`);
                } else {
                    alert(data.message || 'Failed to send OTP.');
                    btn.disabled = false;
                    btn.textContent = 'Verify';
                }
            } catch (err) {
                alert('Network error. Try again.');
                btn.disabled = false;
                btn.textContent = 'Verify';
            }
        }
    </script>
</body>
</html>
