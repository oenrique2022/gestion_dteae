-- Documentos PDF asociados a cada entrega por contrato y centro educativo.
-- Se enlaza por id_contrato + id_institucion para que sobreviva a recrear filas en `entregas` al guardar el contrato.
CREATE TABLE IF NOT EXISTS documentos_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_contrato INT NOT NULL,
    id_institucion INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(512) NOT NULL,
    comentario TEXT,
    fecha_subida DATETIME NOT NULL,
    INDEX idx_contrato_centro (id_contrato, id_institucion),
    INDEX idx_contrato (id_contrato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
