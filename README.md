# Sistema de Login Híbrido con PHP y Google OAuth

![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg) ![MySQL](https://img.shields.io/badge/MySQL-red.svg) ![License](https://img.shields.io/badge/License-MIT-green.svg)

Un sistema de autenticación completo y moderno desarrollado en PHP puro. Permite a los usuarios registrarse e iniciar sesión usando una cuenta local (almacenada en una base de datos MySQL) o de forma segura a través de sus cuentas de Google utilizando el protocolo OAuth 2.0.

El proyecto incluye un dashboard de usuario funcional que muestra información de la sesión, del perfil y del sistema, con una interfaz de usuario pulida y personalizable.

## 🚀 Características Principales

* **Doble Sistema de Autenticación:**
    * Registro e Inicio de Sesión local.
    * Inicio de Sesión con Google (OAuth 2.0).
* **Seguridad:**
    * Contraseñas hasheadas con los algoritmos más recientes de PHP (`password_hash`).
    * Uso de sentencias preparadas para prevenir inyección SQL.
    * Autenticación delegada a Google para mayor seguridad.
* **Dashboard de Usuario Interactivo:**
    * Diseño profesional de 3 columnas inspirado en interfaces de videojuegos.
    * Muestra de datos del perfil (foto, nombre, email).
    * Información de la sesión en tiempo real (hora de inicio, tiempo conectado).
    * Estadísticas del servidor y la base de datos.
* **Interfaz Moderna:**
    * Diseño de login y registro con efecto de capas (background + personaje PNG).
    * Uso de CSS Grid y Flexbox para un layout responsive y moderno.
    * Paleta de colores personalizada y elegante.

## 📸 Capturas de Pantalla

### Página de Login y Registro
*(Aquí puedes poner la captura de tu página de login, la que tiene el personaje)*

### Dashboard de Usuario
*(Aquí puedes poner la captura de tu dashboard final)*

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.0+
* **Base de Datos:** MySQL
* **Frontend:** HTML5, CSS3, JavaScript (vanilla)
* **Gestor de Dependencias:** Composer
* **APIs Externas:** Google People API (a través de la biblioteca `google/apiclient`)

## 📋 Requisitos Previos

Antes de empezar, asegúrate de tener instalado lo siguiente en tu máquina:

* Un entorno de servidor local como [XAMPP](https://www.apachefriends.org/index.html) o [UniServerZ](https://www.uniformserver.com/).
* [Composer](https://getcomposer.org/) instalado y accesible desde la terminal.
* Una cuenta de Google y un proyecto configurado en la [Google Cloud Platform](https://console.cloud.google.com/).

## ⚙️ Guía de Instalación y Configuración

Sigue estos pasos para poner el proyecto en funcionamiento:

**1. Base de Datos:**
   - Abre phpMyAdmin.
   - Crea una nueva base de datos llamada `auth_db` con el cotejamiento `utf8mb4_general_ci`.
   - Ejecuta el siguiente script SQL para crear la tabla `users`:
     ```sql
     CREATE TABLE `users` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `google_id` varchar(255) DEFAULT NULL,
       `username` varchar(50) NOT NULL,
       `email` varchar(100) NOT NULL,
       `password` varchar(255) NOT NULL,
       `profile_picture` varchar(255) DEFAULT NULL,
       `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
       PRIMARY KEY (`id`),
       UNIQUE KEY `google_id` (`google_id`),
       UNIQUE KEY `username` (`username`),
       UNIQUE KEY `email` (`email`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
     ```

**2. Dependencias de PHP:**
   - Abre una terminal en la raíz de la carpeta del proyecto.
   - Ejecuta el siguiente comando para instalar la librería de Google:
     ```bash
     composer install
     ```

**3. Configuración de Google Cloud:**
   - Ve a la Consola de Google Cloud y crea un nuevo proyecto.
   - En "APIs y servicios", habilita la **Google People API**.
   - Ve a "Credenciales", crea un **ID de cliente de OAuth** para "Aplicación web".
   - En la sección **"URIs de redireccionamiento autorizados"**, añade la siguiente URL:
     ```
     http://localhost/google_auth/dashboard.php
     ```
     *(Si usas un puerto diferente, como el 8080, añádelo: `http://localhost:8080/google_auth/dashboard.php`)*
   - Copia tu **ID de cliente** y tu **Secreto del cliente**.

**4. Archivos de Configuración:**
   - **`db_connect.php`**: Asegúrate de que las credenciales de tu base de datos local sean correctas.
     ```php
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', ''); // O la contraseña que hayas configurado
     ```
   - **`google-config.php`**: Pega las credenciales que copiaste de Google Cloud.
     ```php
     $client->setClientId('TU_ID_DE_CLIENTE_AQUI');
     $client->setClientSecret('TU_SECRETO_DE_CLIENTE_AQUI');
     ```

**5. Ejecutar la Aplicación:**
   - Inicia los servicios de Apache y MySQL desde tu panel de control (UniServerZ/XAMPP).
   - Abre tu navegador y ve a:
     ```
     http://localhost/google_auth/
     ```
     *(Reemplaza `google_auth` por el nombre de tu carpeta si es diferente)*.

## ✒️ Autor

Creado por **[me]**.

---
Este proyecto fue desarrollado como parte de un proceso de aprendizaje guiado.