<?php
session_start();
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}
$error = isset($_GET['error']) ? $_GET['error'] : false;
$error_msg = "";
if ($error === 'user') $error_msg = "Invalid Admin Username.";
if ($error === 'pass') $error_msg = "Incorrect Security Key.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumific | Admin Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #050505;
            --accent: #E2B04E; /* Lumific Gold */
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated Background Orbs */
        .orb {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.3;
            animation: move 20s infinite alternate;
        }

        .orb-1 { background: var(--accent); top: -100px; left: -100px; }
        .orb-2 { background: #3b2b8a; bottom: -100px; right: -100px; animation-delay: -5s; }

        @keyframes move {
            from { transform: translate(0, 0); }
            to { transform: translate(100px, 100px); }
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            padding: 50px;
            border-radius: 24px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: 4px;
            margin-bottom: 30px;
            display: block;
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            font-size: 1.2rem;
            font-weight: 300;
            color: rgba(255,255,255,0.6);
            margin-bottom: 40px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            margin-bottom: 8px;
            margin-left: 5px;
        }

        input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            padding: 15px 20px;
            border-radius: 12px;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 20px rgba(226, 176, 78, 0.1);
        }

        .btn-login {
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(226, 176, 78, 0.3);
            filter: brightness(1.1);
        }

        .back-to-site {
            margin-top: 30px;
            display: block;
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .back-to-site:hover {
            color: #fff;
        }

        .error-msg {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: <?php echo $error ? 'block' : 'none'; ?>;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="login-card">
        <span class="brand-logo">LUMIFIC</span>
        <h1>Boutique Management</h1>

        <div class="error-msg" id="errorMsg">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
        </div>

        <form action="auth.php" method="POST">
            <div class="input-group">
                <label>Admin Username</label>
                <input type="text" name="username" placeholder="e.g. admin" required>
            </div>
            <div class="input-group">
                <label>Security Key</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Unlock Dashboard</button>
        </form>

        <a href="../" class="back-to-site">← Return to Boutique</a>
    </div>
</body>
</html>
