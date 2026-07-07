-- Migración: Agregar campos de extras a la tabla reservas
-- Hotel Boutique Villa de Sant
-- Fecha: 2026-07-07

ALTER TABLE reservas
    ADD COLUMN IF NOT EXISTS camas_extra INT DEFAULT 0 COMMENT 'Número de camas extra ($10 c/u)',
    ADD COLUMN IF NOT EXISTS parqueadero TINYINT(1) DEFAULT 0 COMMENT 'Incluye parqueadero ($10)';

-- Verificar la estructura actualizada
-- SELECT id, nombre_cliente, camas_extra, parqueadero FROM reservas LIMIT 5;
