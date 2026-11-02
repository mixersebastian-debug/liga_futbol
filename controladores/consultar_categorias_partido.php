<?php

// Proporciona las categorías disponibles para el formulario de partidos.
require_once __DIR__ . '/../modelos/conexion.php';

// Lee los campos mínimos para construir las opciones de la lista.
$sql = "SELECT id, nombre FROM categorias ORDER BY nombre";

$resultado = mysqli_query($conexion, $sql);

// Emite una opción de error si no se pudo leer la tabla.
if (!$resultado) {
    echo "<option value=''>Error al cargar categorías</option>";
    exit;
}

// Genera una opción HTML por cada categoría encontrada.
while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "<option value='".(int) $fila['id']."'>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</option>";

}

?>