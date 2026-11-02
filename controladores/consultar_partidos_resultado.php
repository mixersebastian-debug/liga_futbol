<?php

// Prepara la lista de partidos a los que se puede asignar un resultado.
require_once __DIR__ . '/../modelos/conexion.php';

// Relaciona equipos y categoría para describir claramente cada partido.
$sql = "
SELECT
    partidos.id,
    clubes_local.nombre AS nombre_local,
    clubes_visitante.nombre AS nombre_visitante,
    categorias.nombre AS nombre_categoria,
    partidos.fecha,
    partidos.estado
FROM partidos

INNER JOIN clubes AS clubes_local
    ON partidos.club_local = clubes_local.id

INNER JOIN clubes AS clubes_visitante
    ON partidos.club_visitante = clubes_visitante.id

INNER JOIN categorias
    ON partidos.categoria_id = categorias.id

WHERE partidos.estado <> 'jugado'
ORDER BY partidos.fecha IS NULL DESC, partidos.fecha ASC
";

$resultado = mysqli_query($conexion, $sql);

// Emite una opción de error si no se pudo recuperar el fixture.
if (!$resultado) {

    echo "<option value=''>Error al cargar partidos</option>";
    exit;

}

// Crea opciones con fecha, categoría y enfrentamiento.
while ($fila = mysqli_fetch_assoc($resultado)) {
    $etiquetasEstado = [
        "sin_fecha" => "Sin fecha",
        "programado" => "Programado",
        "pendiente" => "Pendiente",
        "suspendido" => "Suspendido"
    ];
    $estadoEtiqueta = $etiquetasEstado[$fila["estado"]] ?? "Pendiente";

    echo "
    <option value='".$fila['id']."'>
        ".htmlspecialchars((string) ($fila['fecha'] ?? 'Sin fecha'), ENT_QUOTES, 'UTF-8')." - ".
        htmlspecialchars($fila['nombre_categoria'], ENT_QUOTES, 'UTF-8')." - ".
        htmlspecialchars($fila['nombre_local'], ENT_QUOTES, 'UTF-8')." vs ".
        htmlspecialchars($fila['nombre_visitante'], ENT_QUOTES, 'UTF-8')." (".htmlspecialchars($estadoEtiqueta, ENT_QUOTES, 'UTF-8').")
    </option>
    ";

}

?>      