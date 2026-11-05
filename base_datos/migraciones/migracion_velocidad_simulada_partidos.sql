ALTER TABLE partidos
    ADD COLUMN velocidad_maxima_balon_kmh SMALLINT UNSIGNED NULL AFTER resultado;

UPDATE partidos
SET velocidad_maxima_balon_kmh = FLOOR(25 + RAND() * 41)
WHERE resultado REGEXP '^[[:space:]]*[0-9]{1,3}[[:space:]]*-[[:space:]]*[0-9]{1,3}[[:space:]]*$'
  AND (
      CAST(TRIM(SUBSTRING_INDEX(resultado, '-', 1)) AS UNSIGNED)
      + CAST(TRIM(SUBSTRING_INDEX(resultado, '-', -1)) AS UNSIGNED)
  ) > 0;
