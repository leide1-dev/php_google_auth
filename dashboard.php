<?php
session_start();
require_once 'google-config.php'; // Asegúrate de que este archivo inicializa $client
require_once 'db_connect.php';   // Asegúrate de que este archivo establece $conn
require_once 'functions.php';   // Asegúrate de que este archivo contiene get_server_info()

// PASO 1: PROCESAR LA RESPUESTA DE GOOGLE PRIMERO
// Esto se ejecuta cuando Google redirige al usuario de vuelta con el parámetro 'code'.
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // Verifica si la obtención del token fue exitosa (no hubo errores de autenticación de Google)
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $oauth2 = new Google_Service_Oauth2($client);
        $user_info_google = $oauth2->userinfo->get();

        // --- INICIO DE LA LÓGICA DE BASE DE DATOS Y VERIFICACIÓN DE ESTADO ---

        // Extraer la información del usuario de Google
        $google_id = $user_info_google->id;
        $full_name = $user_info_google->name;
        $email = $user_info_google->email;
        $picture = $user_info_google->picture;
        $given_name = $user_info_google->givenName;

        // 1. Revisar si el usuario ya existe en nuestra BD
        // Seleccionamos todos los campos necesarios, incluyendo el nuevo campo 'status'
        $sql_check = "SELECT id, google_id, username, email, profile_picture, status FROM users WHERE google_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        // Manejo de error si prepare falla
        if ($stmt_check === false) {
            $_SESSION['login_error'] = 'Error de base de datos al preparar la consulta de verificación.';
            header('Location: index.php');
            exit();
        }
        $stmt_check->bind_param("s", $google_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        $user_local_data = null; // Variable para almacenar los datos del usuario de nuestra BD

        if ($result->num_rows > 0) {
            // El usuario ya existe en nuestra BD. Obtenemos sus datos.
            $user_local_data = $result->fetch_assoc();
            
            // Opcional: Actualizamos su nombre, email y foto en nuestra BD si han cambiado en Google.
            // No actualizamos el 'status' aquí, ya que se gestiona manualmente en la BD.
            $sql_update = "UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE google_id = ?";
            $stmt_update = $conn->prepare($sql_update);
             if ($stmt_update === false) {
                $_SESSION['login_error'] = 'Error de base de datos al preparar la consulta de actualización.';
                header('Location: index.php');
                exit();
            }
            $stmt_update->bind_param("ssss", $full_name, $email, $picture, $google_id);
            $stmt_update->execute();
            $stmt_update->close();

        } else {
            // El usuario NO existe en nuestra BD. Lo insertamos.
            // La contraseña es un placeholder, ya que la autenticación es por Google.
            $password_placeholder = 'google_user';
            $default_status = 'active'; // Nuevo usuario, por defecto 'activo'

            $sql_insert = "INSERT INTO users (google_id, username, email, password, profile_picture, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert === false) {
                $_SESSION['login_error'] = 'Error de base de datos al preparar la consulta de inserción.';
                header('Location: index.php');
                exit();
            }
            $stmt_insert->bind_param("ssssss", $google_id, $full_name, $email, $password_placeholder, $picture, $default_status);
            $stmt_insert->execute();
            
            // Después de insertar, necesitamos obtener los datos completos del usuario (incluido el 'id' y 'status')
            // para poder verificar su estado justo después de la creación.
            $stmt_insert->close(); // Cerrar el statement de inserción
            
            // Volvemos a ejecutar la consulta SELECT para obtener los datos del usuario recién creado
            $stmt_check_after_insert = $conn->prepare($sql_check); 
            if ($stmt_check_after_insert === false) {
                $_SESSION['login_error'] = 'Error de base de datos al preparar la consulta después de la inserción.';
                header('Location: index.php');
                exit();
            }
            $stmt_check_after_insert->bind_param("s", $google_id);
            $stmt_check_after_insert->execute();
            $result_after_insert = $stmt_check_after_insert->get_result();
            if ($result_after_insert->num_rows > 0) {
                $user_local_data = $result_after_insert->fetch_assoc();
            }
            $stmt_check_after_insert->close();
        }
        $stmt_check->close(); // Asegúrate de cerrar el statement original de verificación

        // --- VERIFICACIÓN FINAL DEL ESTADO DEL USUARIO DESDE NUESTRA BD ---
        if ($user_local_data && $user_local_data['status'] === 'active') {
            // Si el usuario existe en nuestra BD y su estado es 'active', creamos la sesión.
            $_SESSION['access_token'] = $token['access_token'];
            $_SESSION['user_id'] = $user_local_data['id']; // Usamos el ID de nuestra tabla
            $_SESSION['user_name'] = $full_name; // Usamos el nombre de Google (se actualizó si fue necesario)
            $_SESSION['user_email'] = $email;   // Usamos el email de Google
            $_SESSION['user_picture'] = $picture; // Usamos la foto de Google
            $_SESSION['user_given_name'] = $given_name; 
            $_SESSION['login_time'] = time();

            // Redirige al usuario al dashboard sin el parámetro 'code' para una URL limpia
            header('Location: dashboard.php');
            exit();

        } else {
            // Si el usuario no se encontró, o su estado NO es 'active'
            // Limpiamos cualquier rastro de sesión potencial antes de redirigir.
            session_unset();
            session_destroy();
            $_SESSION['login_error'] = "Tu cuenta ha sido desactivada o baneada. Contacta al administrador.";
            header('Location: index.php');
            exit();
        }
        // --- FIN DE LA LÓGICA DE BASE DE DATOS Y VERIFICACIÓN DE ESTADO ---

    } else {
        // Error en la autenticación con Google (ej. token inválido o denegado)
        session_unset();
        session_destroy();
        $_SESSION['login_error'] = 'Error al autenticar con Google. Por favor, inténtalo de nuevo.';
        header('Location: index.php');
        exit();
    }
}

// PASO 2: VERIFICAR SI EL USUARIO YA TIENE UNA SESIÓN ACTIVA
// Esto se ejecuta en cargas posteriores del dashboard o si ya se había logueado.
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    // Si no hay token de acceso o user_id en la sesión, el usuario no está logueado.
    // Limpiamos la sesión por seguridad y redirigimos.
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// PASO 3: PREPARAR DATOS PARA MOSTRAR EN EL DASHBOARD
// Si llegamos aquí, el usuario está autenticado y activo.
$system_info = get_server_info($conn); // Función de functions.php para obtener info del servidor/DB

// Recuperar y sanitizar los datos de la sesión para mostrarlos en HTML
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
$user_email = htmlspecialchars($_SESSION['user_email'] ?? 'No disponible');
$user_picture = htmlspecialchars($_SESSION['user_picture'] ?? 'default_avatar.png');
$login_time = $_SESSION['login_time'] ?? time(); // Hora de inicio de sesión

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-grid-container">
        
        <aside class="left-sidebar">
            <div class="sidebar-content-centered">
                <div class="welcome-section">
                    <h1>BIENVENIDO</h1>
                    <p class="user-email-sidebar"><?php echo $user_email; ?></p>
                </div>
                <div class="profile-section">
                    <img src="<?php echo $user_picture; ?>" alt="Foto de Perfil" class="profile-image-sidebar">
                    <span class="profile-name-sidebar"><?php echo $user_name; ?></span>
                </div>
            </div>
            <a href="logout.php" class="logout-btn-sidebar">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </a>
        </aside>

        <main class="main-content-area">
            <div class="character-backdrop"></div>
            <img src="character2.png" alt="Personaje del Dashboard" class="dashboard-character">
        </main>

        <aside class="right-sidebar">
            <div class="info-cards-wrapper">
                <div class="info-card">
                    <h4><i class="fa-solid fa-id-card"></i> ID de Usuario (Local)</h4>
                    <p class="stat-value"><?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-clock"></i> Hora de Inicio</h4>
                    <p class="stat-value"><?php echo date('d/m/Y H:i:s', $login_time); ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-hourglass-half"></i> Tiempo Conectado</h4>
                    <p class="stat-value" id="timer">00:00:00</p>
                </div>
                   <div class="info-card">
                    <h4><i class="fa-solid fa-database"></i> Estado BD</h4>
                    <p class="stat-value">
                        <span class="status-dot <?php echo $system_info['db_status_class']; ?>"></span>
                        <?php echo $system_info['db_status_text']; ?>
                    </p>
                </div>
                   <div class="info-card full-width-card">
                    <h3><i class="fa-solid fa-info-circle"></i> Info del Sistema</h3>
                    <ul class="system-info-list">
                        <li><span>Versión PHP:</span> <strong><?php echo $system_info['php_version']; ?></strong></li>
                        <li><span>Servidor:</span> <strong><?php echo $system_info['server_software']; ?></strong></li>
                        <li><span>Puerto:</span> <strong><?php echo $system_info['server_port']; ?></strong></li>
                        <li><span>Versión MySQL:</span> <strong><?php echo $system_info['mysql_version']; ?></strong></li>
                    </ul>
                </div>
            </div>
        </aside>

    </div>
    <script>
        const timerElement = document.getElementById('timer');
        if (timerElement) {
            // Calcula los segundos transcurridos desde el login PHP
            let totalSeconds = <?php echo time() - $login_time; ?>; 
            setInterval(() => {
                totalSeconds++;
                // Formatea el tiempo en HH:MM:SS
                let h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
                let m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
                let s = (totalSeconds % 60).toString().padStart(2, '0');
                timerElement.textContent = h + ':' + m + ':' + s;
            }, 1000); // Actualiza cada segundo
        }
    </script>
</body>
</html>