-- ============================================================
-- VILLA DE SANT - Database Schema v2
-- ============================================================
CREATE DATABASE IF NOT EXISTS hotel_villa_de_sant;
USE hotel_villa_de_sant;

-- ============================================================
-- TABLA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLA: habitaciones (20 unidades totales)
-- ============================================================
CREATE TABLE IF NOT EXISTS habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo de habitación (Single, Queen, etc.)',
    numero VARCHAR(10) NOT NULL COMMENT 'Número de habitación ej: 101',
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    estado ENUM('disponible', 'ocupado', 'mantenimiento') DEFAULT 'disponible',
    imagen VARCHAR(255),
    caracteristicas JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_numero (numero)
);

-- ============================================================
-- TABLA: cupones
-- ============================================================
CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_tipo VARCHAR(50) DEFAULT NULL COMMENT 'Aplica a todo un tipo. NULL = global',
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descuento INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLA: reservas
-- ============================================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT,
    nombre_cliente VARCHAR(100) NOT NULL,
    email_cliente VARCHAR(100) NOT NULL,
    telefono_cliente VARCHAR(20),
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    num_huespedes INT DEFAULT 1,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    cupon_codigo VARCHAR(20) DEFAULT NULL,
    descuento_aplicado INT DEFAULT 0,
    notas TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id)
);

-- ============================================================
-- DATOS: Admin (password: admin123)
-- ============================================================
INSERT INTO usuarios (nombre, email, password, role)
VALUES ('Administrador', 'admin@villadesant.com', '$2y$10$nUP/7WsUcPhj9rjhMxrv1O.4WVkc/Sta5zNLRCB8YnRONXlUmMyKu', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================================
-- DATOS: 20 Habitaciones (6+6+6+1+1)
-- ============================================================
INSERT INTO habitaciones (tipo, numero, nombre, descripcion, precio, estado, imagen, caracteristicas) VALUES

-- === SINGLE ROOM × 6 ===
('single', '101', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room1.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),
('single', '102', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room2.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),
('single', '103', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room3.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),
('single', '104', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room1.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),
('single', '105', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room2.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),
('single', '106', 'Single Room - Non Smoking', 'Habitación individual con diseño único y personalizado. Ideal para viajeros solos que buscan una experiencia íntima y auténtica en el corazón colonial de Quito.', 75.00, 'disponible', 'assets/img/single/single room3.jpg', '["fa-wifi:WiFi Incluido","fa-mountain-sun:Vista Panorámica","fa-martini-glass:Minibar Premium","fa-snowflake:Aire Acondicionado"]'),

-- === QUEEN ROOM × 6 ===
('queen', '201', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen1.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),
('queen', '202', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen2.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),
('queen', '203', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen1.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),
('queen', '204', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen2.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),
('queen', '205', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen1.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),
('queen', '206', 'Queen Room - Non Smoking', 'Elegante habitación con cama Queen Size. Perfecta para parejas que desean una estancia romántica rodeados de arquitectura colonial y comodidades modernas.', 110.00, 'disponible', 'assets/img/queen/queen2.jpg', '["fa-bed:Cama Queen Size","fa-door-open:Acceso al Patio","fa-tv:Smart TV","fa-box:King Bed disponible"]'),

-- === TWO BEDS × 6 ===
('two_beds', '301', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),
('two_beds', '302', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds2.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),
('two_beds', '303', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds3.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),
('two_beds', '304', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),
('two_beds', '305', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds2.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),
('two_beds', '306', 'Two Beds Room - Non Smoking', 'Espaciosa habitación con dos camas Queen Size. Ideal para familias o amigos que viajan juntos y quieren el máximo confort con espacio propio para cada uno.', 135.00, 'disponible', 'assets/img/two beds/two beds3.jpg', '["fa-bed:2 Camas Queen","fa-building-columns:Techos Altos","fa-desktop:Escritorio","fa-vault:Caja Fuerte"]'),

-- === THREE BEDS × 1 ===
('three_beds', '401', 'Three Beds - Non Smoking', 'Nuestra habitación más amplia para grupos familiares. Cuenta con una cama Queen, una plaza y media y una plaza individual, ofreciendo flexibilidad para toda la familia.', 160.00, 'disponible', 'assets/img/three beds/three beds.jpg', '["fa-bed:1 Cama Queen","fa-bed:1 Plaza y Media","fa-bed:1 Plaza","fa-bath:Baño Doble","fa-people-group:Sala Común"]'),

-- === SUITE × 1 ===
('suite', '501', 'Suite - Non Smoking', 'Nuestra suite colonial insignia. Una experiencia de lujo completo con mobiliario de época, ambiente insonorizado, balcón privado y detalles de confort que la hacen única.', 200.00, 'disponible', 'assets/img/suite/suites.jpg', '["fa-gem:Mobiliario de Lujo","fa-volume-xmark:Insonorizada","fa-archway:Habitación Colonial","fa-door-open:Balcón Privado"]')

ON DUPLICATE KEY UPDATE
    estado = VALUES(estado),
    precio = VALUES(precio),
    descripcion = VALUES(descripcion),
    imagen = VALUES(imagen),
    caracteristicas = VALUES(caracteristicas);

-- ============================================================
-- DATOS: Cupones por tipo de habitación
-- ============================================================
INSERT INTO cupones (habitacion_tipo, codigo, descuento, fecha_inicio, fecha_fin, activo) VALUES
('single',     'SINGLE10SAN',  10, '2026-04-10', '2026-08-31', 1),
('queen',      'QUEEN15SAN',   15, '2026-04-10', '2026-09-30', 1),
('two_beds',   'TWINS20SAN',   20, '2026-05-01', '2026-10-31', 1),
('three_beds', 'FAMILY25SAN',  25, '2026-05-01', '2026-11-30', 1),
('suite',      'SUITE30SAN',   30, '2026-04-10', '2026-12-20', 1)
ON DUPLICATE KEY UPDATE
    descuento    = VALUES(descuento),
    fecha_inicio = VALUES(fecha_inicio),
    fecha_fin    = VALUES(fecha_fin),
    activo       = VALUES(activo);
