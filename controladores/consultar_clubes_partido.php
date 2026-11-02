    <?php

// Devuelve las opciones de clubes usadas al crear un partido.
require_once __DIR__ . '/../modelos/conexion.php';

// Obtiene identificadores y nombres en orden alfabético.
$sql = "SELECT id, nombre FROM clubes ORDER BY nombre";

$resultado = mysqli_query($conexion, $sql);

// Informa al formulario si no fue posible consultar los clubes.
if (!$resultado) {
    echo "<option value=''>Error al cargar clubes</option>";
    exit;
}

// Construye una opción por cada equipo disponible.
while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "<option value='".$fila['id']."'>".$fila['nombre']."</option>";

}

?>