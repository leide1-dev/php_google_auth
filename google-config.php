<?php
// Iniciar la sesión de PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el autoload de Composer
require_once 'vendor/autoload.php';

// Crear una instancia del cliente de Google
$client = new Google_Client();

// Configurar las credenciales de OAuth 2.0
$client->setClientId('XXXXXXXXXXXXXXXXXX'); // Oe baboso pega tu ID de Cliente
$client->setClientSecret('ZZZZZZZZ-ZZZZZZZ'); // Pega tu Secreto de Cliente
$client->setRedirectUri('http://localhost:8088/google_auth/dashboard.php'); // Asegúrate que coincida con la que pusiste en Google Cloud

// Definir los 'scopes' (qué información solicitamos)
$client->addScope("email");
$client->addScope("profile");

?>