CREATE DATABASE IF NOT EXISTS gym_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_control;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol ENUM('socio', 'admin') NOT NULL DEFAULT 'socio'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS planes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  duracion_meses TINYINT UNSIGNED NOT NULL,
  precio INT UNSIGNED NOT NULL,
  categoria VARCHAR(60) NOT NULL,
  descripcion VARCHAR(600) NOT NULL,
  CONSTRAINT planes_duracion CHECK (duracion_meses BETWEEN 1 AND 36),
  CONSTRAINT planes_precio CHECK (precio BETWEEN 1 AND 10000000)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS membresias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  plan_nombre VARCHAR(100) NOT NULL,
  precio_unitario INT UNSIGNED NOT NULL,
  cantidad TINYINT UNSIGNED NOT NULL,
  descuento INT UNSIGNED NOT NULL,
  total INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  direccion VARCHAR(160) NOT NULL,
  telefono VARCHAR(35) NOT NULL,
  medio_pago ENUM('tarjeta', 'efectivo', 'transferencia') NOT NULL,
  inicio DATE NOT NULL,
  fin DATE NOT NULL,
  solicitud CHAR(32) NOT NULL,
  UNIQUE KEY membresias_solicitud (usuario_id, solicitud),
  KEY membresias_vigencia (usuario_id, fin),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (plan_id) REFERENCES planes(id),
  CONSTRAINT membresias_cantidad CHECK (cantidad BETWEEN 1 AND 20),
  CONSTRAINT membresias_vigencia_valida CHECK (fin > inicio)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consultas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  mensaje VARCHAR(500) NOT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (plan_id) REFERENCES planes(id)
) ENGINE=InnoDB;

-- Los IDs 1 a 4 corresponden al catálogo inicial de la AE1. Importar para instalaciones nuevas.
INSERT IGNORE INTO planes (id, nombre, duracion_meses, precio, categoria, descripcion) VALUES
  (1, 'Plan Mensual', 1, 25000, 'Flexible', 'Acceso completo al gimnasio con renovación mes a mes.'),
  (2, 'Plan Trimestral', 3, 67500, 'Constancia', 'Tres meses de entrenamiento con un valor preferencial.'),
  (3, 'Plan Semestral', 6, 120000, 'Progreso', 'Continuidad semestral para sostener objetivos a mediano plazo.'),
  (4, 'Plan Anual', 12, 216000, 'Compromiso', 'La opción de mayor duración y mejor valor mensual.');
