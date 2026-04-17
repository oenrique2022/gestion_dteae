-- PDFs de respaldo por ruta de entrega.
CREATE TABLE IF NOT EXISTS documentos_rutas_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(512) NOT NULL,
    comentario TEXT NULL,
    fecha_subida DATETIME NOT NULL,
    INDEX idx_documentos_ruta_fecha (ruta_id, fecha_subida),
    CONSTRAINT fk_documentos_ruta_entrega
        FOREIGN KEY (ruta_id) REFERENCES rutas_entrega(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
