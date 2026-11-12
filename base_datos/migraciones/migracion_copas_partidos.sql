ALTER TABLE partidos
    ADD COLUMN copa ENUM('oro', 'plata', 'bronce') NOT NULL DEFAULT 'oro' AFTER categoria_id;
