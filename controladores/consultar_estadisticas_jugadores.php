<?php

require_once __DIR__ . "/../modelos/conexion.php";
$club_id = current_club_id();

$sql = "
    SELECT
        jugadores.cedula,
        jugadores.nombre,
        jugadores.apellido,
        clubes.nombre AS club,
        categorias.nombre AS categoria,
        COALESCE(goles.goles, 0) AS goles,
        COALESCE(asistencias.asistencias, 0) AS asistencias,
        COALESCE(sanciones.amarillas, 0) AS amarillas,
        COALESCE(sanciones.rojas, 0) AS rojas,
        COALESCE(sanciones.total, 0) AS total
    FROM jugadores
    INNER JOIN clubes ON clubes.id = jugadores.club_id
    INNER JOIN categorias ON categorias.id = jugadores.categoria_id
    LEFT JOIN (
        SELECT jugador_cedula, SUM(cantidad) AS goles
        FROM goles_partido
        GROUP BY jugador_cedula
    ) AS goles ON goles.jugador_cedula = jugadores.cedula
    LEFT JOIN (
        SELECT jugador_cedula, SUM(cantidad) AS asistencias
        FROM asistencias_partido
        GROUP BY jugador_cedula
    ) AS asistencias ON asistencias.jugador_cedula = jugadores.cedula
    LEFT JOIN (
        SELECT
            jugador_cedula,
            COUNT(CASE WHEN tipo = 'Amarilla' THEN 1 END) AS amarillas,
            COUNT(CASE WHEN tipo = 'Roja' THEN 1 END) AS rojas,
            COUNT(*) AS total
        FROM tarjetas
        GROUP BY jugador_cedula
    ) AS sanciones ON sanciones.jugador_cedula = jugadores.cedula
";

if ($club_id !== null) {
    $sql .= " WHERE jugadores.club_id = " . (int) $club_id;
}

$sql .= "
    ORDER BY goles DESC, total DESC, rojas DESC, amarillas DESC, jugadores.apellido, jugadores.nombre
";

$resultado = mysqli_query($conexion, $sql);
if (!$resultado) {
    error_log("Error al consultar estadísticas de jugadores: " . mysqli_error($conexion));
    http_response_code(500);
    exit("No se pudieron consultar las estadísticas de jugadores.");
}

function escaparEstadisticaJugador($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$jugadores = [];
$totales = ["jugadores" => 0, "goles" => 0, "asistencias" => 0, "amarillas" => 0, "rojas" => 0, "tarjetas" => 0];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $jugadores[] = $fila;
    $totales["jugadores"]++;
    $totales["goles"] += (int) $fila["goles"];
    $totales["asistencias"] += (int) $fila["asistencias"];
    $totales["amarillas"] += (int) $fila["amarillas"];
    $totales["rojas"] += (int) $fila["rojas"];
    $totales["tarjetas"] += (int) $fila["total"];
}

echo "
<style>
.estadisticas-jugadores-resumen{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin:16px 0;
}
.estadisticas-jugadores-resumen p{
    margin:0;
    padding:12px 16px;
    background:#171e28;
    border:1px solid #283342;
    border-radius:5px;
}
.estadisticas-jugadores-contenedor{overflow-x:auto}
.tabla-estadisticas-jugadores{
    width:100%;
    min-width:650px;
    border-collapse:collapse;
    background:#10151d;
    color:#f5f7fa;
}
.tabla-estadisticas-jugadores th,
.tabla-estadisticas-jugadores td{
    padding:11px 12px;
    border:1px solid #283342;
    text-align:left;
}
.tabla-estadisticas-jugadores th{
    background:#171e28;
    border-bottom:2px solid #d5a83b;
}
.tabla-estadisticas-jugadores tbody tr:nth-child(even){background:#0b1017}
.tabla-estadisticas-jugadores td:nth-child(n+5){text-align:center}
.modo-claro .estadisticas-jugadores-resumen p,
.modo-claro .tabla-estadisticas-jugadores{
    background:#fff;
    color:#17212f;
}
.modo-claro .tabla-estadisticas-jugadores th{
    background:#dce6f0;
    color:#17212f;
    border-color:#8b9bad;
}
.modo-claro .tabla-estadisticas-jugadores td{
    border-color:#8b9bad;
}
.modo-claro .tabla-estadisticas-jugadores tbody tr:nth-child(even){background:#eaf0f6}
</style>
";

echo "<div class='estadisticas-jugadores-resumen'>";
echo "<p>Jugadores: <strong data-estadistica-total='jugadores'>" . $totales["jugadores"] . "</strong></p>";
echo "<p>Goles: <strong data-estadistica-total='goles'>" . $totales["goles"] . "</strong></p>";
echo "<p>Asistencias: <strong data-estadistica-total='asistencias'>" . $totales["asistencias"] . "</strong></p>";
echo "<p>Amarillas: <strong data-estadistica-total='amarillas'>" . $totales["amarillas"] . "</strong></p>";
echo "<p>Rojas: <strong data-estadistica-total='rojas'>" . $totales["rojas"] . "</strong></p>";
echo "<p>Total de tarjetas: <strong data-estadistica-total='tarjetas'>" . $totales["tarjetas"] . "</strong></p>";
echo "</div>";

echo "<div class='estadisticas-jugadores-contenedor'>";
echo "<table class='tabla-estadisticas-jugadores'><thead><tr>";
echo "<th>Jugador</th><th>Club</th><th>Categoría</th><th>Goles</th><th>Asistencias</th><th>Amarillas</th><th>Rojas</th><th>Total de tarjetas</th>";
echo "</tr></thead><tbody>";

if (count($jugadores) === 0) {
    echo "<tr data-liga-sin-resultados><td colspan='8'>No hay jugadores registrados.</td></tr>";
} else {
    foreach ($jugadores as $jugador) {
        $criterioBusqueda = escaparEstadisticaJugador(
            $jugador["cedula"] . " " . $jugador["nombre"] . " " . $jugador["apellido"]
            . " " . $jugador["apellido"] . " " . $jugador["nombre"]
        );
        echo "<tr data-busqueda='" . $criterioBusqueda . "'>";
        echo "<td>" . escaparEstadisticaJugador($jugador["nombre"] . " " . $jugador["apellido"]) . "</td>";
        echo "<td>" . escaparEstadisticaJugador($jugador["club"]) . "</td>";
        echo "<td>" . escaparEstadisticaJugador($jugador["categoria"]) . "</td>";
        echo "<td><strong>" . (int) $jugador["goles"] . "</strong></td>";
        echo "<td><strong>" . (int) $jugador["asistencias"] . "</strong></td>";
        echo "<td>" . (int) $jugador["amarillas"] . "</td>";
        echo "<td>" . (int) $jugador["rojas"] . "</td>";
        echo "<td><strong>" . (int) $jugador["total"] . "</strong></td>";
        echo "</tr>";
    }
}

echo "</tbody></table></div>";
echo "<p>Los goles de los partidos registrados antes de esta función no pueden asignarse automáticamente a jugadores, porque solo se guardó el marcador del equipo.</p>";

?>
