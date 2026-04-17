-- Historial de comentarios adicionales por archivo de contrato.
CREATE TABLE IF NOT EXISTS comentarios_documentos_contrato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario DATETIME NOT NULL,
    INDEX idx_documento_fecha (documento_id, fecha_comentario),
    CONSTRAINT fk_comentario_documento_contrato
        FOREIGN KEY (documento_id) REFERENCES documentos_contratos(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
