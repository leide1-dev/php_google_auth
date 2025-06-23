<?php
session_start();
require_once 'google-config.php';
$login_url = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TuProyecto</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-page">
    <div class="background-layer"></div>
    <div class="character-layer"><img src="character.png" alt="Personaje"></div>
    <div class="content-container">
        <header class="auth-header">
            <a href="#" class="logo"></a>
        </header>
        <main class="auth-main">
            <div class="form-wrapper">
                <p class="form-subtitle">Bienvenido de nuevo</p>
                <h2>Iniciar Sesión</h2>
                
                <?php if(isset($_SESSION['login_error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_SESSION['register_success'])): ?>
                    <div class="success-message"><?php echo $_SESSION['register_success']; unset($_SESSION['register_success']); ?></div>
                <?php endif; ?>

                <a href="<?php echo $login_url; ?>" class="google-signup-btn"><i class="fa-brands fa-google"></i> Inicia sesión con Google</a>
                <div class="separator"><span>o</span></div>
                <form action="login_handler.php" method="post" class="auth-form">
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit" class="submit-btn-main">Iniciar Sesión</button>
                </form>
                <p class="switch-form-link">¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </main>
    </div>
</body>
</html>