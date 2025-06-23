<?php
// Paso 1: Iniciar la sesión para poder acceder a ella.
session_start();

// Paso 2: Destruir todas las variables de sesión.
// Esto "olvida" al usuario en el servidor.
$_SESSION = array();

// Paso 3: Destruir la sesión por completo.
session_destroy();

// Paso 4: Redirigir al usuario a la página de inicio.
header('Location: index.php');
exit();
?>