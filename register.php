<?php
session_start();
require_once 'db_connect.php'; // Nos conectamos a la base de datos

$error_message = '';

// El script se ejecuta solo si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recoger y limpiar los datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 2. Validar los datos
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico no es válido.";
    } elseif (strlen($password) < 6) {
        $error_message = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Las contraseñas no coinciden.";
    } else {
        // 3. Verificar si el usuario o email ya existen en la base de datos
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        
        if($stmt_check = $conn->prepare($sql_check)){
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $error_message = "El nombre de usuario o el correo ya están registrados.";
            } else {
                // 4. Si no existen, hashear la contraseña (¡NUNCA GUARDAR CONTRASEÑAS EN TEXTO PLANO!)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 5. Insertar el nuevo usuario en la base de datos
                $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                
                if($stmt_insert = $conn->prepare($sql_insert)){
                    $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

                    if ($stmt_insert->execute()) {
                        // Si el registro es exitoso, creamos un mensaje de éxito y redirigimos al login
                        $_SESSION['register_success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                        header("location: index.php");
                        exit();
                    } else {
                        $error_message = "Algo salió mal. Por favor, inténtalo de nuevo.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - TuProyecto</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-page">
    <div class="background-layer"></div>

    <div class="character-layer">
        <img src="character.png" alt="Personaje">
    </div>

    <div class="content-container">
        <header class="auth-header">
            <a href="#" class="logo">TuProyecto</a>
            <div class="header-nav">
                <span>¿Ya tienes una cuenta?</span>
                <a href="index.php" class="nav-link">Inicia sesión</a>
            </div>
        </header>

        <main class="auth-main">
            <div class="form-wrapper">
                <p class="form-subtitle">EMPIEZA GRATIS</p>
                <h2>Crear Nueva Cuenta</h2>
                
                <?php if(!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <a href="<?php require_once 'google-config.php'; echo $client->createAuthUrl(); ?>" class="google-signup-btn">
                    <i class="fa-brands fa-google"></i> Regístrate con Google
                </a>

                <div class="separator"><span>o</span></div>

                <form action="register.php" method="post" class="auth-form">
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="submit-btn-main">Crear Cuenta</button>
                </form>

                <p class="switch-form-link">¿Ya tienes una cuenta? <a href="index.php">Inicia sesión</a></p>
            </div>
        </main>
    </div>
</body>
</html>