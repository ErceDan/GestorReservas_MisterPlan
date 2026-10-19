-- Inicializador de desarrollo. Al reimportarlo se eliminan y recrean
-- exclusivamente las tablas de la aplicación dentro de reservas_alojamiento.
CREATE DATABASE IF NOT EXISTS reservas_alojamiento
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE reservas_alojamiento;

DROP TABLE IF EXISTS reserva_evento;
DROP TABLE IF EXISTS reserva;

CREATE TABLE reserva (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_huesped VARCHAR(120) NOT NULL,
    email_huesped VARCHAR(254) NOT NULL,
    nombre_alojamiento VARCHAR(150) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado VARCHAR(12) NOT NULL DEFAULT 'pendiente',
    importe DECIMAL(10, 2) UNSIGNED NOT NULL,
    notas TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_reserva_estado
        CHECK (estado IN ('pendiente', 'confirmada', 'cancelada')),
    CONSTRAINT chk_reserva_fechas
        CHECK (fecha_salida > fecha_entrada),
    CONSTRAINT chk_reserva_importe
        CHECK (importe > 0),

    INDEX idx_reserva_estado_entrada (estado, fecha_entrada),
    INDEX idx_reserva_entrada (fecha_entrada),
    INDEX idx_reserva_salida (fecha_salida)
) ENGINE = InnoDB;

CREATE TABLE reserva_evento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_id BIGINT UNSIGNED NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_reserva_evento_tipo
        CHECK (tipo IN ('reserva_creada', 'reserva_confirmada', 'reserva_cancelada')),
    CONSTRAINT fk_reserva_evento_reserva
        FOREIGN KEY (reserva_id) REFERENCES reserva(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_reserva_evento_reserva_fecha (reserva_id, fecha_creacion)
) ENGINE = InnoDB;

START TRANSACTION;

INSERT INTO reserva (
    id,
    nombre_huesped,
    email_huesped,
    nombre_alojamiento,
    fecha_entrada,
    fecha_salida,
    estado,
    importe,
    notas,
    fecha_creacion
) VALUES
    (1, 'Lucía Martínez', 'lucia.martinez@example.com', 'Apartamento Sol', '2026-11-12', '2026-11-16', 'pendiente', 480.00, 'Llegada prevista por la tarde.', '2026-10-15 09:30:00'),
    (2, 'Javier López', 'javier.lopez@example.com', 'Casa del Mar', '2026-12-03', '2026-12-08', 'confirmada', 875.50, NULL, '2026-10-10 14:10:00'),
    (3, 'Elena García', 'elena.garcia@example.com', 'Estudio Centro', '2026-10-28', '2026-10-31', 'cancelada', 320.00, 'Solicitó cama supletoria.', '2026-10-05 11:45:00'),
    (4, 'Carlos Sánchez', 'carlos.sanchez@example.com', 'Villa Pinar', '2027-01-15', '2027-01-22', 'pendiente', 1540.00, 'Viaja con dos menores.', '2026-10-17 16:20:00'),
    (5, 'Marta Ruiz', 'marta.ruiz@example.com', 'Apartamento Sol', '2026-11-20', '2026-11-23', 'confirmada', 360.00, NULL, '2026-10-18 08:15:00');

INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion) VALUES
    (1, 'reserva_creada', 'Reserva creada con estado pendiente.', '2026-10-15 09:30:00'),
    (2, 'reserva_creada', 'Reserva creada con estado pendiente.', '2026-10-10 14:10:00'),
    (2, 'reserva_confirmada', 'Reserva confirmada.', '2026-10-11 10:00:00'),
    (3, 'reserva_creada', 'Reserva creada con estado pendiente.', '2026-10-05 11:45:00'),
    (3, 'reserva_cancelada', 'Reserva cancelada a petición del huésped.', '2026-10-06 12:20:00'),
    (4, 'reserva_creada', 'Reserva creada con estado pendiente.', '2026-10-17 16:20:00'),
    (5, 'reserva_creada', 'Reserva creada con estado pendiente.', '2026-10-18 08:15:00'),
    (5, 'reserva_confirmada', 'Reserva confirmada.', '2026-10-18 09:00:00');

COMMIT;
