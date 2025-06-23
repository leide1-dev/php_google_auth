-- Script de Base de Datos para el Proyecto de Login Híbrido
-- Versión: 1.1
-- Autor: [Tu Nombre]

--
-- Base de datos: `auth_db`
--
CREATE DATABASE IF NOT EXISTS `auth_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `auth_db`;

-- --------------------------------------------------------

--
-- Estructura de la tabla para `users`
-- Guarda la información principal de los usuarios registrados localmente o con Google.
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'ID único autoincremental para cada usuario.',
  `google_id` varchar(255) DEFAULT NULL COMMENT 'ID único proporcionado por Google para usuarios OAuth.',
  `username` varchar(50) NOT NULL COMMENT 'Nombre de usuario único para login local.',
  `email` varchar(100) NOT NULL COMMENT 'Correo electrónico único del usuario.',
  `password` varchar(255) NOT NULL COMMENT 'Contraseña hasheada para usuarios locales.',
  `profile_picture` varchar(255) DEFAULT NULL COMMENT 'URL de la foto de perfil (principalmente de Google).',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha y hora de creación del registro.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indices de la tabla `users`
--

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de la tabla `users`
--

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID único autoincremental para cada usuario.';
COMMIT;