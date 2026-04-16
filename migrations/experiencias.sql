-- Tabla para el contenido editable de la página de experiencias
-- Excluye los iframes de 360° que son estáticos

CREATE TABLE IF NOT EXISTS `experiencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seccion` varchar(50) NOT NULL COMMENT 'transporte, tours, galeria',
  `subtitulo` varchar(255) DEFAULT NULL COMMENT 'Texto superior dorado',
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tags` text DEFAULT NULL COMMENT 'JSON array de tags',
  `lista` text DEFAULT NULL COMMENT 'JSON array de {icono, texto}',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen principal',
  `orden` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `seccion` (`seccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para las 12 fotos de la galería
CREATE TABLE IF NOT EXISTS `experiencias_galeria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `posicion` int(11) NOT NULL COMMENT 'Posición 1-12',
  `imagen` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT 'Experiencia',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `posicion` (`posicion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos iniciales de las secciones
INSERT INTO `experiencias` (`seccion`, `subtitulo`, `titulo`, `descripcion`, `tags`, `lista`, `imagen`, `orden`) VALUES
('transporte', 'Comodidad desde su Arribo', 'Transporte al Aeropuerto', 'Inicie su estancia con la tranquilidad y el confort que merece. Brindamos un servicio de traslado privado desde y hacia el Aeropuerto Internacional Mariscal Sucre en vehículos de alta gama, conducidos por personal profesional.', '["#TrasladoPrivado","#Seguridad","#Confort","#24/7","#AeropuertoQuito"]', '[{"icono":"fa-solid fa-check","texto":"Monitoreo de vuelos en tiempo real."},{"icono":"fa-solid fa-check","texto":"Asistencia con el equipaje y bienvenida personalizada."},{"icono":"fa-solid fa-check","texto":"Vehículos climatizados con WiFi y agua de cortesía."}]', 'assets/img/experiencia/transporte.png', 1),
('tours', 'Descubra los Tesoros de Quito', 'Tours y Aventuras', 'Déjenos guiarle por los rincones más mágicos de la ciudad y sus alrededores. Desde la majestuosidad del Centro Histórico hasta la aventura en el Teleférico o la Mitad del Mundo, organizamos experiencias a su medida.', '["#QuitoPatrimonial","#MitadDelMundo","#Cultura","#Historia","#FullDay"]', '[{"icono":"fa-solid fa-map-location-dot","texto":"Tours privados con guías certificados."},{"icono":"fa-solid fa-mountain-city","texto":"Visitas a museos, iglesias y miradores icónicos."},{"icono":"fa-solid fa-car-side","texto":"Excursiones de un día a Otavalo, Cotopaxi o Mindo."}]', 'assets/img/experiencia/tours.png', 2)
ON DUPLICATE KEY UPDATE seccion = seccion;

-- Insertar las 12 fotos iniciales de la galería
INSERT INTO `experiencias_galeria` (`posicion`, `imagen`, `alt_text`) VALUES
(1, 'assets/img/experiencia/experiencia1.jpg', 'Experiencia 1'),
(2, 'assets/img/experiencia/experiencia9.jpg', 'Experiencia 2'),
(3, 'assets/img/experiencia/experiencia3.jpg', 'Experiencia 3'),
(4, 'assets/img/experiencia/experiencia4.jpg', 'Experiencia 4'),
(5, 'assets/img/experiencia/experiencia5.jpg', 'Experiencia 5'),
(6, 'assets/img/experiencia/experiencia6.jpg', 'Experiencia 6'),
(7, 'assets/img/experiencia/experiencia10.jpg', 'Experiencia 7'),
(8, 'assets/img/experiencia/experiencia11.jpg', 'Experiencia 8'),
(9, 'assets/img/experiencia/experiencia2.jpg', 'Experiencia 9'),
(10, 'assets/img/experiencia/experiencia7.jpg', 'Experiencia 10'),
(11, 'assets/img/experiencia/experiencia8.jpg', 'Experiencia 11'),
(12, 'assets/img/experiencia/experiencia12.jpg', 'Experiencia 12')
ON DUPLICATE KEY UPDATE posicion = posicion;
