<?php

// Recupera los clubes para las listas de selección de formularios.
require_once __DIR__ . '/../modelos/conexion.php';

// Solo se consultan los campos requeridos para identificar cada opción.
$sql = "SELECT id, nombre FROM clubes ORDER BY nombre";

$resultado = mysqli_query($conexion, $sql);

// Devuelve un texto de error compatible con la lista desplegable.
if (!$resultado) {
    echo "Error al consultar clubes";
    exit;
}

// Convierte cada club en una opción con su identificador como valor.
while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "<option value='".(int) $fila['id']."'>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</option>";

}

?>