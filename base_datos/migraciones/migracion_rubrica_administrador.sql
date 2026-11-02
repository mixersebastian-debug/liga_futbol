ALTER TABLE clubes
    ADD COLUMN logo VARCHAR(255) NULL AFTER ciudad;

ALTER TABLE partidos
    MODIFY fecha DATE NULL,
    ADD COLUMN estado ENUM(
        'sin_fecha',
        'programado',
        'jugado',
        'pendiente',
        'suspendido'
    ) NOT NULL DEFAULT 'pendiente' AFTER fecha;

UPDATE partidos
SET estado = CASE
    WHEN resultado IS NOT NULL AND TRIM(resultado) <> '' THEN 'jugado'
    WHEN fecha IS NULL THEN 'sin_fecha'
    ELSE 'programado'
END;
