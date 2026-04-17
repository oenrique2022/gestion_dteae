-- Detalle por línea en contratos_equipos: modelo y descripción del ítem (además de marca).
-- Ejecutar una vez en la base de datos del proyecto.
-- Si aparece error "Duplicate column name", la columna ya existe: omita esa sentencia.

ALTER TABLE contratos_equipos
    ADD COLUMN modelo VARCHAR(150) NULL DEFAULT NULL COMMENT 'Modelo del ítem de la línea' AFTER marca;

ALTER TABLE contratos_equipos
    ADD COLUMN descripcion TEXT NULL COMMENT 'Descripción u observaciones de la línea de equipo' AFTER modelo;
