<?php
require_once "config/session.php";
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?action=dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Villa de Sant</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gold: #c5a059;
            --secondary-gold: #a68544;
            --bg-dark: #05070a;
            --card-glass: rgba(20, 25, 35, 0.7);
            --text-light: #f0f0f0;
            --text-muted: #8a8d9a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--text-light);
        }

        /* Animated Background Gradient */
        .bg-glow {
            position: absolute;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(197, 160, 89, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(197, 160, 89, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(197, 160, 89, 0.2);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            text-align: center;
            position: relative;
        }

        .logo-area {
            margin-bottom: 30px;
        }
        
        .logo-circle {
            width: 70px;
            height: 70px;
            background: rgba(197, 160, 89, 0.1);
            border: 1px solid var(--primary-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 0 20px rgba(197, 160, 89, 0.2);
        }

        .logo-circle img {
            width: 45px;
            border-radius: 50%;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-gold);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            color: var(--primary-gold);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 14px 14px 14px 40px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(197, 160, 89, 0.2);
            border-radius: 12px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-gold);
            background: rgba(197, 160, 89, 0.05);
            box-shadow: 0 0 15px rgba(197, 160, 89, 0.1);
        }

        button.btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            border: none;
            border-radius: 12px;
            color: black;
            font-weight: 800;
            font-size: 0.85rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-top: 10px;
        }

        button.btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(197, 160, 89, 0.4);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border-left: 3px solid #e74c3c;
            color: #ff9a90;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Home Button */
        .back-home {
            margin-top: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .back-home:hover {
            color: var(--primary-gold);
        }

        .back-home i {
            font-size: 0.75rem;
        }

    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <div class="logo-circle">
                    <img src="assets/img/logo.png" alt="Villa de Sant">
                </div>
                <h1>Panel de Gestión</h1>
                <p class="subtitle">Acceso exclusivo para administradores</p>
            </div>

            <form action="api/auth.php" method="POST">
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" required placeholder="admin@villadesant.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-login">Ingresar al Sistema</button>

                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Credenciales incorrectas o acceso denegado.
                    </div>
                <?php endif; ?>
            </form>

            <a href="index.php?action=home" class="back-home">
                <i class="fa-solid fa-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>
