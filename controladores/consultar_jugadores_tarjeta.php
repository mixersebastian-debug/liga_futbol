<?php

// Carga los jugadores para elegir a quién asociar una tarjeta.
require_once __DIR__ . '/../modelos/conexion.php';

// Ordena por apellido y nombre para facilitar la búsqueda visual.
$sql = "
SELECT cedula, nombre, apellido
FROM jugadores
ORDER BY apellido, nombre
";

$resultado = mysqli_query($conexion, $sql);

// Devuelve una opción de error cuando no se pudo cargar la lista.
if (!$resultado) {

    echo "<option value=''>Error al cargar jugadores</option>";
    exit;

}

// Forma las opciones con nombre completo y cédula identificadora.
while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "
    <option value='".$fila['cedula']."'>
        ".$fila['apellido'].", ".$fila['nombre']." - ".$fila['cedula']."
    </option>
    ";

}

?>