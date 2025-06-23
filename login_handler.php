<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Por favor, ingresa correo y contraseña.";
        header("location: index.php");
        exit();
    }

    // Pedimos también el campo 'status' en la consulta
    $sql = "SELECT id, username, email, password, profile_picture, status FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verificamos la contraseña
            if (password_verify($password, $user['password'])) {
                
                // --- ¡NUEVA VERIFICACIÓN DE ESTADO! ---
                if ($user['status'] == 'active') {
                    // Si el usuario está activo, creamos la sesión
                    $_SESSION['access_token'] = 'local_user';
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    // ... (resto de las variables de sesión) ...
                    header("location: dashboard.php");
                    exit();
                } else {
                    // Si el usuario no está activo (inactivo o baneado)
                    $_SESSION['login_error'] = "Tu cuenta ha sido desactivada o baneada.";
                    header("location: index.php");
                    exit();
                }
                // --- FIN DE LA VERIFICACIÓN ---

            } else {
                $_SESSION['login_error'] = "La contraseña es incorrecta.";
                header("location: index.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "No se encontró una cuenta con ese correo.";
            header("location: index.php");
            exit();
        }
    }
    $stmt->close();
}
$conn->close();
?>