-- Planificación y trazabilidad de rutas por contrato y centro educativo.
CREATE TABLE IF NOT EXISTS rutas_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    id_institucion INT NOT NULL,
    responsable_entrega VARCHAR(150) NOT NULL,
    motorista VARCHAR(150) NOT NULL,
    vehiculo VARCHAR(120) NOT NULL,
    placas VARCHAR(60) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Programada',
    fecha_programada DATE NULL,
    fecha_en_ruta DATETIME NULL,
    fecha_entregado DATETIME NULL,
    comentarios TEXT NULL,
    fecha_creacion DATETIME NOT NULL,
    fecha_actualizacion DATETIME NOT NULL,
    UNIQUE KEY uk_ruta_contrato_centro (contrato_id, id_institucion),
    INDEX idx_ruta_estado (estado),
    INDEX idx_ruta_fecha_programada (fecha_programada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
