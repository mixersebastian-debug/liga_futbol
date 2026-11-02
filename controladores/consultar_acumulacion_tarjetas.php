<?php

require_once __DIR__ . "/../modelos/conexion.php";
$club_id = current_club_id();

$sql = "
    SELECT jugadores.nombre, jugadores.apellido, categorias.nombre AS categoria,
           COUNT(CASE WHEN tarjetas.tipo = 'Amarilla' THEN 1 END) AS amarillas,
           COUNT(CASE WHEN tarjetas.tipo = 'Roja' THEN 1 END) AS rojas,
           COUNT(tarjetas.id) AS total
    FROM jugadores
    INNER JOIN tarjetas ON tarjetas.jugador_cedula = jugadores.cedula
    INNER JOIN categorias ON categorias.id = jugadores.categoria_id
";

if ($club_id !== null) {
    $sql .= " WHERE jugadores.club_id = " . $club_id;
}

$sql .= " GROUP BY jugadores.cedula, jugadores.nombre, jugadores.apellido, categorias.nombre
          ORDER BY total DESC, jugadores.apellido, jugadores.nombre";

$resultado = mysqli_query($conexion, $sql);
if (!$resultado) {
    http_response_code(500);
    exit("No se pudo consultar la acumulación de tarjetas.");
}

function escaparAcumulacion($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

echo "
<style>
.tabla-acumulacion-contenedor{overflow-x:auto}
.tabla-acumulacion{
    width:100%;
    min-width:480px;
    border-collapse:separate;
    border-spacing:0 7px;
    text-align:center;
}
.tabla-acumulacion th{
    padding:10px 14px;
    border-bottom:2px solid #d5a83b;
    white-space:nowrap;
}
.tabla-acumulacion td{
    padding:11px 14px;
    background:#171e28;
    border-top:1px solid #283342;
    border-bottom:1px solid #283342;
}
.tabla-acumulacion th:first-child,
.tabla-acumulacion td:first-child{
    min-width:140px;
    text-align:left;
}
.tabla-acumulacion td:first-child{border-left:1px solid #283342}
.tabla-acumulacion td:last-child{border-right:1px solid #283342}
.tabla-acumulacion td+td{border-left:1px solid #283342}
.tabla-acumulacion .sin-tarjetas{text-align:center}
.modo-claro .tabla-acumulacion td{
    background:#eaf0f6;
    border-color:#8b9bad;
}
</style>
<div class='tabla-acumulacion-contenedor'>
<table class='tabla-acumulacion'><thead><tr><th>Jugador</th><th>Categoría</th><th>Amarillas</th><th>Rojas</th><th>Total</th></tr></thead><tbody>
";

if (mysqli_num_rows($resultado) === 0) {
    echo "<tr><td class='sin-tarjetas' colspan='5'>No hay tarjetas registradas para este club.</td></tr>";
} else {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "<tr>";
        echo "<td>" . escaparAcumulacion($fila["nombre"] . " " . $fila["apellido"]) . "</td>";
        echo "<td>" . escaparAcumulacion($fila["categoria"]) . "</td>";
        echo "<td>" . (int) $fila["amarillas"] . "</td>";
        echo "<td>" . (int) $fila["rojas"] . "</td>";
        echo "<td>" . (int) $fila["total"] . "</td>";
        echo "</tr>";
    }
}

echo "</tbody></table></div>";

?>
