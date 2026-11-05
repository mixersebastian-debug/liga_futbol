INSERT INTO categorias (nombre)
SELECT '2013' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2013');

INSERT INTO categorias (nombre)
SELECT '2014' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2014');

INSERT INTO categorias (nombre)
SELECT '2015' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2015');

INSERT INTO categorias (nombre)
SELECT '2016' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2016');

INSERT INTO categorias (nombre)
SELECT '2017' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2017');

INSERT INTO categorias (nombre)
SELECT '2018' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2018');

INSERT INTO categorias (nombre)
SELECT '2019' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = '2019');

INSERT INTO categorias (nombre)
SELECT 'Femenino' WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE nombre = 'Femenino');
