CREATE TABLE goles_partido (
    partido_id INT(11) NOT NULL,
    jugador_cedula VARCHAR(20) NOT NULL,
    cantidad SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (partido_id, jugador_cedula),
    INDEX idx_goles_partido_jugador (jugador_cedula),
    CONSTRAINT fk_goles_partido_partido
        FOREIGN KEY (partido_id) REFERENCES partidos (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_goles_partido_jugador
        FOREIGN KEY (jugador_cedula) REFERENCES jugadores (cedula)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
