<?php
// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // El usuario por defecto de UniServer/XAMPP
define('DB_PASSWORD', '1234');     // La contraseña por defecto está vacía
define('DB_NAME', 'auth_db');  // El nombre de nuestra base de datos

// Crear la conexión
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if($conn->connect_error){
    die("ERROR: No se pudo conectar a la base de datos. " . $conn->connect_error);
}
?>