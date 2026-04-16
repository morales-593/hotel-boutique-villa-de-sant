
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id` int(11) NOT NULL,
  `habitacion_tipo` varchar(50) DEFAULT NULL COMMENT 'Aplica a todo un tipo. NULL = global',
  `codigo` varchar(20) NOT NULL,
  `descuento` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cupones`
--

INSERT INTO `cupones` (`id`, `habitacion_tipo`, `codigo`, `descuento`, `fecha_inicio`, `fecha_fin`, `activo`, `created_at`) VALUES
(1, 'two_beds', 'DOSHABITAICION10', 10, '2026-04-10', '2026-08-31', 1, '2026-04-11 00:17:46'),
(2, 'queen', 'QUEEN15SAN', 15, '2026-04-10', '2026-09-30', 1, '2026-04-11 00:17:46'),
(3, 'single', 'SINGLE21AN', 20, '2026-05-01', '2026-10-31', 1, '2026-04-11 00:17:46'),
(4, 'three_beds', 'FAMILY25SAN', 25, '2026-05-01', '2026-11-30', 1, '2026-04-11 00:17:46'),
(5, 'suite', 'SUITE30SAN', 26, '2026-04-10', '2026-12-20', 1, '2026-04-11 00:17:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL COMMENT 'Tipo de habitación (Single, Queen, etc.)',
  `numero` varchar(10) NOT NULL COMMENT 'Número de habitación ej: 101',
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` enum('disponible','ocupado','mantenimiento') DEFAULT 'disponible',
  `imagen` varchar(255) DEFAULT NULL,
  `caracteristicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`caracteristicas`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `tipo`, `numero`, `nombre`, `descripcion`, `precio`, `estado`, `imagen`, `caracteristicas`, `created_at`) VALUES
(1, 'single', '101', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(2, 'single', '102', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(3, 'single', '103', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(4, 'single', '104', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(5, 'single', '105', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(6, 'single', '106', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-wifi:WiFi Incluido\",\"fa-mountain-sun:Vista Panorámica\",\"fa-martini-glass:Minibar Premium\",\"fa-snowflake:Aire Acondicionado\"]', '2026-04-11 00:17:46'),
(7, 'queen', '201', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(8, 'queen', '202', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(9, 'queen', '203', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(10, 'queen', '204', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(11, 'queen', '205', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(12, 'queen', '206', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 80.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:Cama Queen Size\",\"fa-door-open:Acceso al Patio\",\"fa-tv:Smart TV\",\"fa-box:King Bed disponible\"]', '2026-04-11 00:17:46'),
(13, 'two_beds', 'abc', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(14, 'two_beds', '302', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(15, 'two_beds', '303', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(16, 'two_beds', '304', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(17, 'two_beds', '305', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(18, 'two_beds', '306', 'Habitación con dos camas - No fumadores', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 20.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:2 Camas Queen\",\"fa-building-columns:Techos Altos\",\"fa-desktop:Escritorio\",\"fa-vault:Caja Fuerte\"]', '2026-04-11 00:17:46'),
(19, 'three_beds', '401', 'Three Beds - Non Smoking', 'Nuestra habitación más amplia para grupos familiares. Cuenta con una cama Queen, una plaza y media y una plaza individual, ofreciendo flexibilidad para toda la familia.', 160.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-bed:1 Cama Queen\",\"fa-bed:1 Plaza y Media\",\"fa-bed:1 Plaza\",\"fa-bath:Baño Doble\",\"fa-people-group:Sala Común\"]', '2026-04-11 00:17:46'),
(20, 'suite', '501', 'Suite - Non Smoking', 'Nuestra suite colonial insignia. Una experiencia de lujo completo con mobiliario de época, ambiente insonorizado, balcón privado y detalles de confort que la hacen única.', 85.00, 'disponible', 'assets/img/hero_home.png', '[\"fa-gem:Mobiliario de Lujo\",\"fa-volume-xmark:Insonorizada\",\"fa-archway:Habitación Colonial\",\"fa-door-open:Balcón Privado\"]', '2026-04-11 00:17:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `habitacion_id` int(11) DEFAULT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(100) NOT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `num_huespedes` int(11) DEFAULT 1,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  `cupon_codigo` varchar(20) DEFAULT NULL,
  `descuento_aplicado` int(11) DEFAULT 0,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `habitacion_id`, `nombre_cliente`, `email_cliente`, `telefono_cliente`, `fecha_entrada`, `fecha_salida`, `num_huespedes`, `total`, `estado`, `cupon_codigo`, `descuento_aplicado`, `notas`, `created_at`) VALUES
(1, 13, 'wiliam', 'wiliampuntomorales45@gmail.com', '1234567889', '2026-04-11', '2026-04-11', 2, 0.09, 'pendiente', NULL, 0, NULL, '2026-04-12 02:18:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrador', 'admin@villadesant.com', '$2y$10$nUP/7WsUcPhj9rjhMxrv1O.4WVkc/Sta5zNLRCB8YnRONXlUmMyKu', 'admin', '2026-04-11 00:17:46'),
(2, 'wiliam', 'wiliampuntomorales45@gmail.com', '$2y$10$eSqpSjux/oTlpVAUJxd8fuZaR5LOqhKM41ExZoX5k5KO5CvrWxoz2', 'admin', '2026-04-12 02:09:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_numero` (`numero`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `habitacion_id` (`habitacion_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
