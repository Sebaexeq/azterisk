-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2023 a las 22:15:07
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rapibnb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

CREATE TABLE `alquileres` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `etiquetas` text DEFAULT NULL,
  `galeria_fotos` text DEFAULT NULL,
  `servicios` text DEFAULT NULL,
  `costo_alquiler` decimal(10,2) NOT NULL,
  `tiempo_minimo` int(11) NOT NULL,
  `tiempo_maximo` int(11) NOT NULL,
  `cupo` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 0,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`id`, `usuario_id`, `titulo`, `descripcion`, `ubicacion`, `etiquetas`, `galeria_fotos`, `servicios`, `costo_alquiler`, `tiempo_minimo`, `tiempo_maximo`, `cupo`, `fecha_inicio`, `fecha_fin`, `activa`, `fecha_publicacion`) VALUES
(29, 9, 'Hotel Aiello', 'Uno de los hoteles más conocidos y reconocidos por el mundo entero que viaja a San Luis. Es de los más económicos.', 'Av. Illia 431, D5700AXT San Luis', 'Hotel, Ciudad', '[\"galeria\\/Aiello1.jpg\",\"galeria\\/Aiello2.jpg\",\"galeria\\/Aiello3.jpg\"]', '[\"Cocina\",\"Piscina\",\"Spa\",\"Wi-Fi\",\"Desayuno\"]', 23718.00, 1, 7, 2, '2023-10-18', '2023-11-05', 0, '2023-10-02 03:00:00'),
(30, 9, 'Hotel Potrero de los Funes', 'Este moderno complejo turístico se encuentra en el lago Potrero de los Funes, a 4 km del Monumento al Pueblo Puntano de la Independencia.', 'Ruta 18 km 16, Potrero de los Funes, San Luis', 'Hotel, Mar, Spa', '[\"galeria\\/651b4913f403c_Potrero1.jpg\",\"galeria\\/651b49141086a_Potrero2.jpg\",\"galeria\\/651b49141fa57_Potrero3.jpg\"]', '[\"Limpieza\"]', 51554.00, 1, 7, 2, '2023-10-29', '2023-11-03', 0, '2023-10-02 03:00:00'),
(31, 13, 'Cabaña en Santa Rosa', 'Cabaña a 2 cuadras del Balneario Santa Rosa del Conlara, muy turistico, cualquier consulta llamar al dueño: 2664001527', 'Santa Rosa del Conlara', 'Balneario Cabaña Vacaciones', '[\"galeria\\/651b4b6c112e1_2.jpg\",\"galeria\\/651b4b6c2b6b8_3.jpg\",\"galeria\\/651b4b6c3d513_rio.jpg\"]', NULL, 7500.00, 1, 7, 2, '0000-00-00', '0000-00-00', 1, '2023-10-02 03:00:00'),
(32, 9, 'Epic Hotel San Luis', 'Este hotel de lujo, ubicado en un moderno edificio con vistas a las montañas de los alrededores y parte del hipódromo La Punta.', 'Bv. las Cañadas, D5710 San Luis', 'Hotel, Lujo, Edificio, Modernidad, Spa, Piscina', '[\"galeria\\/651b4ebb601ff_Epic1.jpg\",\"galeria\\/651b4ebb860d5_Epic2.jpg\",\"galeria\\/651b4ebba12ba_Epic3.jpg\"]', '[\"Cocina\",\"Piscina\",\"Spa\",\"Wi-Fi\"]', 45419.00, 1, 7, 4, '2023-10-19', '2023-10-31', 0, '2023-10-02 03:00:00'),
(33, 9, 'Departamento Villa Mercedes San Luis FUCO 3', 'El Departamento Villa Mercedes San Luis FUCO 3 se encuentra en Villa Mercedes y ofrece terraza. El alojamiento cuenta con balcón, estacionamiento privado gratuito y wifi gratis.', 'Fuerte Constitucional 73, 5730 Villa Mercedes, Argentina', 'Departamento, SanLuis, VillaMercedes', '[\"galeria\\/65248290aec3e_Villa1.jpg\",\"galeria\\/6524829108796_Villa2.jpg\",\"galeria\\/6524829122d9a_Villa3.jpg\",\"galeria\\/652482913c8f6_Villa4.jpg\"]', '[\"Cocina\",\"Aire acondicionado\",\"Limpieza\",\"Merienda\"]', 60376.00, 1, 7, 2, '2023-10-29', '2023-12-02', 1, '2023-10-09 03:00:00'),
(34, 9, 'Escapada de Praia do Forte', 'Comodidad, refinamiento y ocio en el paraíso. Ubicado en el condominio de piscinas naturales.', 'Alojamiento entero en Mata de São João, Brasil', 'Brasil, Spa, Hotel', '[\"galeria\\/Praia1.png\",\"galeria\\/Praia2.png\",\"galeria\\/Praia3.png\"]', '[\"Cocina\",\"Piscina\",\"Spa\",\"Aire acondicionado\",\"Limpieza\",\"Desayuno\",\"Merienda\"]', 10000.00, 1, 30, 2, '2023-11-08', '2023-12-31', 1, '2023-10-09 03:00:00'),
(35, 9, '¡Increible cabaña en el Delta!', 'Un paraíso natural a 30 minutos de la ciudad. Naturaleza, tranquilidad, rio para zambullirse y espacio para fogón.\r\nLa cabaña es nueva de excelente diseño, priorizando el confort y las vistas.\r\nTiene terreno y muelle propios.\r\nUbicada sobre el Arroyo Arroyón, uno de los mas tranquilos y bellos del delta.\r\nUn entorno mágico para conectar con la naturaleza y olvidarse de los problemas de la ciudad.', 'Argentina', 'Argentina, Tigre, Imperdible', '[\"galeria\\/Tigre1.webp\",\"galeria\\/Tigre2.webp\",\"galeria\\/Tigre3.webp\"]', '[\"Limpieza\",\"Wi-Fi\",\"Desayuno\"]', 15000.00, 1, 90, 2, '2023-10-28', '2023-11-28', 1, '2023-10-09 03:00:00'),
(51, 9, 'Bello depto Yesyhouses', 'Disfruta de la sencillez de este alojamiento tranquilo, cómodo, espacioso, moderno y céntrico.\r\nCerca de los bares de palermo, del centro y plaza de mayo.\r\nLa mejor localización.\r\nAdemás cuenta con lavadero, piscina, sauna, jacuzzi, gimnasio, gym y parrilla con salón de usos múltiples.\r\nTiene 2 baños, uno con bañera en suite, otro de servicio, living comedor, cocina, balcón, 2 plasmas grandes, sillón cama, 2 black out, vajilla, cafetera, microondas, heladera con freezer, horno, etc.', 'Argentina - Buenos Aires', 'Argentina, Departamento, Habitación, Buenos Aires', '[\"galeria\\/BuenosAires1.webp\",\"galeria\\/BuenosAires2.webp\",\"galeria\\/BuenosAires3.webp\",\"galeria\\/AzTeRisk.hdr\"]', '[\"Cocina\",\"Piscina\",\"Spa\",\"Aire acondicionado\",\"Limpieza\"]', 60000.00, 1, 7, 4, '2023-10-29', '2024-11-29', 1, '2023-10-29 03:00:00'),
(52, 9, 'Exclusivo Dpto en capital/ parrilla/ terraza', 'PH de dos ambientes en triplex\r\nTodo para vos, cuenta con un living/comedor con cocina integrada, toilet y balcón en la primera planta', 'Buenos Aires', 'Buenos Aires, Argentina', '[\"galeria\\/2.webp\",\"galeria\\/3.webp\",\"galeria\\/1.webp\"]', '[\"Aire acondicionado\",\"Desayuno\",\"Merienda\"]', 8000.00, 1, 10, 2, '2023-11-08', '2023-12-31', 1, '2023-10-29 03:00:00'),
(53, 9, 'Hermoso Depto en Puerto Madero', 'Disfrutá de una experiencia con estilo en este alojamiento céntrico.\r\nHermoso departamento para dos o tres personas, consta de cocina, living comedor con Divan, un baño y una habitación con dos camas de una plaza o una cama de dos plazas.\r\nEn planta baja nos encontramos con un amplio espacio con vista al dique, con pileta climatizada, jacuzzi, sauna de vapor y sauna seco. Tambien cuenta con otro espacio con gimnasio totalmente equipado y en la terraza una pileta al aire libre con solarium', 'Argentina - Buenos Aires', 'Buenos Aires, Argentina, Departamento, Hermoso, Puerto Madero', '[\"galeria\\/hermoso1.webp\",\"galeria\\/hermoso3.webp\",\"galeria\\/hermoso2.webp\"]', '[\"Piscina\",\"Aire acondicionado\"]', 19000.00, 1, 7, 2, '0000-00-00', '0000-00-00', 1, '2023-10-29 03:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aplicaciones_alquiler`
--

CREATE TABLE `aplicaciones_alquiler` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `alquiler_id` int(11) NOT NULL,
  `fecha_aplicacion` date NOT NULL DEFAULT curdate(),
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('pendiente','aceptado','completado') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aplicaciones_alquiler`
--

INSERT INTO `aplicaciones_alquiler` (`id`, `usuario_id`, `alquiler_id`, `fecha_aplicacion`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(14, 17, 34, '2023-10-26', '2023-10-20', '2023-10-26', 'completado'),
(16, 17, 34, '2023-10-26', '2023-10-25', '2023-10-26', 'completado'),
(23, 13, 30, '2023-10-30', '2023-10-30', '2023-11-02', 'completado'),
(24, 17, 30, '2023-10-30', '2023-11-01', '2023-11-03', 'completado'),
(28, 13, 51, '2023-11-03', '2023-11-02', '2023-11-03', 'completado'),
(35, 9, 31, '2023-11-13', '2023-11-12', '2023-11-13', 'completado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenia`
--

CREATE TABLE `resenia` (
  `id` int(11) NOT NULL,
  `id_oferta` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `puntuacion` int(11) NOT NULL CHECK (`puntuacion` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `fecha_creacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resenia`
--

INSERT INTO `resenia` (`id`, `id_oferta`, `id_usuario`, `puntuacion`, `comentario`, `fecha_creacion`) VALUES
(4, 30, 10, 5, 'atención maravillosa, me encantó', '0000-00-00'),
(11, 32, 15, 2, 'nada', '0000-00-00'),
(12, 31, 16, 4, 'El mejor hotel que he visitado.', '0000-00-00'),
(13, 35, 16, 5, 'Si este hotel fuera China, entonces China sería mi hotel favorito.', '0000-00-00'),
(14, 34, 16, 1, 'Muy mala atención.', '0000-00-00'),
(15, 29, 16, 3, 'Excelente.', '0000-00-00'),
(16, 32, 16, 5, 'Es el mejor', '0000-00-00'),
(17, 30, 16, 4, 'dxdxd', '0000-00-00'),
(20, 34, 17, 5, 'Muy buena atención.', '0000-00-00'),
(21, 31, 17, 4, 'Estuvo bueno, pero la comida fue una kk', '0000-00-00'),
(25, 31, 9, 3, 'dwqdq', '0000-00-00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(11) NOT NULL,
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `respuesta` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id`, `id_resena`, `id_usuario`, `respuesta`, `fecha_creacion`) VALUES
(2, 4, 9, 'Gracias!', '2023-10-23 17:29:53'),
(23, 11, 9, 'Qué lástima que no te haya gustado :c', '2023-10-25 20:39:05'),
(24, 13, 9, 'Gracias por preferir este lugar.', '2023-10-25 21:19:25'),
(25, 15, 9, 'Gracias.', '2023-10-25 21:20:27'),
(26, 14, 9, 'VREWFGVERWVERW', '2023-10-25 21:30:06'),
(27, 20, 9, 'Me alegra que te haya gustado.', '2023-10-28 19:39:45'),
(28, 16, 9, 'Okaz', '2023-10-29 01:56:53'),
(29, 17, 9, 'dxdd', '2023-10-29 01:59:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `intereses` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0,
  `fecha_verificacion` date DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `contraseña`, `intereses`, `foto_perfil`, `verificado`, `fecha_verificacion`, `bio`, `admin`) VALUES
(9, 'Sebastian', 'García', 'seba.garcia_11@hotmail.com', '$2y$10$NMJFvjjRix2/HvmPAao0juMZg932wmHQmIpBbVdCxC.WBtT/fDnUK', 'Programación, Anime, Música, Juegos.', 'galeria/65372b3ab7d6e-fot.png', 1, '2023-11-18', 'Soy el creador de esta página.', 1),
(10, 'Ana', 'Barroso', 'ana.barroso16810@gmail.com', '$2y$10$NMJFvjjRix2/HvmPAao0juMZg932wmHQmIpBbVdCxC.WBtT/fDnUK', 'Música.', 'galeria/653de7f615d4a-Mamor.png', 0, NULL, 'Soy Diseñadora de Interiores.', 0),
(11, 'Martín', 'Moccia', 'mocciamartizn@gzmail.com', '$2y$10$DCkHKfGpOOzxpvZQxzskGupXdlINeQOdXxhlHShg6oZUUtu2U36jW', 'qwerasd', 'imagenes/650a60dec054f_c975bb5bd138598716924b8c2673a4c700c06a5f.png', 0, NULL, 'qwetrsad', 0),
(12, 'Matías', 'García', 'dwqdqwdq@hotmail.com', '$2y$10$NMJFvjjRix2/HvmPAao0juMZg932wmHQmIpBbVdCxC.WBtT/fDnUK', 'dsadasdsa', 'imagenes/65174ec9bb3b8_anime.png', 0, NULL, 'dwqdqwdq', 0),
(13, 'Leonardo', 'Gallardo', 'leoja00@gmail.com', '$2y$10$ubju/FM18KD5GTo98LfzruqMF9/XqLxA1zdz9zgh9O1yufVqW.m/e', 'NADA', 'imagenes/651b3f679b693_IMG_4992.jpg', 0, NULL, 'Estudiante de TUW', 0),
(15, 'Angelo', 'Whitelust', 'AngeloWhitedemon@gmail.com', '$2y$10$FG16tiTqfPegX85KBOf3J.MkfuDTaWx2urDDWvJagssiK802vOyWC', 'ninguno.', 'imagenes/65398608bf6f3_Cassiopeia Chibi 2.jpg', 0, NULL, 'nada.', 1),
(16, 'Maxi', 'Illesca', 'maxi.illesca@hotmail.com', '$2y$10$1fxmPiNHnxT5cUfLIbhmcuUqyp2heJT04HafIYBSsX/0J9nSWsRWS', 'Fútbol, Fiestas.', 'galeria/653de84aca358-maxidebronce.png', 0, NULL, 'Vivo en Malargüe/Mendoza.', 0),
(17, 'Paola', 'Gil', 'gilpaola@yahoo.com.ar', '$2y$10$24/g2a4LNkasuWnTVDQOHegFmn5EwfOMBImVO4L2/pVZIrY7CYsjG', 'Música, Arte, Libros.', 'galeria/653de78c2c6a4-Ma.png', 0, NULL, 'Soy mujer.', 0),
(18, 'Sol', 'Illesca', 'sol.illesca@hotmail.com', '$2y$10$d6p0wpR9aXbtsshbzSlce.srMzMhIUwjcyTwORua1XrNq4ncjf/de', 'Música, Series.', 'imagenes/653deec8b4fe3_Sol.png', 0, NULL, 'Soy una chica de Malargüe.', 0),
(20, 'Juan', 'Gil', 'juangil@hotmail.com', '$2y$10$DcVkqkljR1dr9ZFn8IRDsOmSkTyluKFR5AZkbRtj7qXz5FWgkqFUS', 'Juegos, Mortal Kombat.', 'imagenes/653defba654fe_Juan.png', 0, NULL, 'Soy un sayajín.', 0),
(21, 'Agustín', 'GIl', 'gilagustin@hotmail.com', '$2y$10$6mXW2VAaw1vYp05a9jXrYOZCEmj6W91EX77ZEbs8huPWJkGWIz2AS', 'Juegos, Birras.', 'imagenes/653df0582db6b_Agustin.png', 0, NULL, 'Eu sou do Brazil.', 0),
(22, 'Agusín', 'Galdeano', 'agustingaldeano@hotmail.com', '$2y$10$y2fkp5Lgau31u0ug2lY5JuJ6U5B1/urjPLTs81vStGAHgW3bB8MHu', 'Religión, Fútbol, Política.', 'imagenes/653df0dcef045_imagen_2023-10-29_024251565.png', 0, NULL, 'Soy de Argentina.', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verificaciones`
--

CREATE TABLE `verificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni_frente` varchar(255) DEFAULT NULL,
  `dni_dorso` varchar(255) DEFAULT NULL,
  `selfie` varchar(255) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `verificaciones`
--

INSERT INTO `verificaciones` (`id`, `usuario_id`, `dni_frente`, `dni_dorso`, `selfie`, `fecha_solicitud`) VALUES
(18, 13, 'Aiello1.jpg', 'Aiello2.jpg', 'Aiello3.jpg', '2023-10-30 22:35:02');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `aplicaciones_alquiler`
--
ALTER TABLE `aplicaciones_alquiler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `alquiler_id` (`alquiler_id`);

--
-- Indices de la tabla `resenia`
--
ALTER TABLE `resenia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_oferta` (`id_oferta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_resena` (`id_resena`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `verificaciones`
--
ALTER TABLE `verificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `aplicaciones_alquiler`
--
ALTER TABLE `aplicaciones_alquiler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `resenia`
--
ALTER TABLE `resenia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `verificaciones`
--
ALTER TABLE `verificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `alquileres_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `aplicaciones_alquiler`
--
ALTER TABLE `aplicaciones_alquiler`
  ADD CONSTRAINT `aplicaciones_alquiler_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `aplicaciones_alquiler_ibfk_2` FOREIGN KEY (`alquiler_id`) REFERENCES `alquileres` (`id`);

--
-- Filtros para la tabla `resenia`
--
ALTER TABLE `resenia`
  ADD CONSTRAINT `resenia_ibfk_1` FOREIGN KEY (`id_oferta`) REFERENCES `alquileres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenia_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `respuestas_ibfk_1` FOREIGN KEY (`id_resena`) REFERENCES `resenia` (`id`),
  ADD CONSTRAINT `respuestas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `verificaciones`
--
ALTER TABLE `verificaciones`
  ADD CONSTRAINT `verificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
