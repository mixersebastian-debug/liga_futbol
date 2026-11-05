<?php

// Consulta el calendario con los nombres de equipos y categorías relacionados.
require_once __DIR__ . '/../modelos/conexion.php';
$club_id = current_club_id();
$estado = $_GET["estado"] ?? "";
$esAdministrador = ($_SESSION["tipo"] ?? "") === "Administrador";

// Combina las tablas relacionadas para mostrar cada partido de forma legible.
$sql = "
SELECT 
    partidos.id,
    clubes_local.nombre AS nombre_local,
    clubes_visitante.nombre AS nombre_visitante,
    categorias.nombre AS nombre_categoria,
    partidos.fecha,
    partidos.estado,
    partidos.resultado,
    partidos.velocidad_maxima_balon_kmh
FROM partidos

INNER JOIN clubes AS clubes_local
    ON partidos.club_local = clubes_local.id

INNER JOIN clubes AS clubes_visitante
    ON partidos.club_visitante = clubes_visitante.id

INNER JOIN categorias
    ON partidos.categoria_id = categorias.id
";

$condiciones = [];
if ($club_id !== null) {
    $condiciones[] = "(partidos.club_local = " . $club_id . " OR partidos.club_visitante = " . $club_id . ")";
}
if ($estado === "pendientes") {
    $condiciones[] = "partidos.estado <> 'jugado'";
} elseif ($estado === "jugados") {
    $condiciones[] = "partidos.estado = 'jugado'";
} elseif (in_array($estado, ["sin_fecha", "programado", "jugado", "pendiente", "suspendido"], true)) {
    $condiciones[] = "partidos.estado = '" . $estado . "'";
}
if ($condiciones) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY partidos.fecha IS NULL ASC, partidos.fecha ASC";

// Informa el error de consulta en vez de generar una tabla incompleta.
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {

    echo "<p>Error al consultar el fixture: " . mysqli_error($conexion) . "</p>";
    exit;

}

// Define la presentación de la tabla que se incrusta en la interfaz.
echo "
<style>

body {
    margin: 0;
    background-color: #10151d;
    color: #f5f7fa;
    font-family: Arial, sans-serif;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #10151d;
    color: #f5f7fa;
}

th {
    background-color: #171e28;
    color: #f5f7fa;
    padding: 10px;
    border-bottom: 2px solid #d5a83b;
}

td {
    padding: 10px;
    text-align: center;
    border: 1px solid #283342;
}

tr:nth-child(even) td {
    background-color: #0b1017;
}

</style>

<p>La velocidad indicada es simulada automáticamente (25–65 km/h); no es una medición real del partido.</p>

";

// Presenta las columnas que describen un encuentro.
echo "<table>";

echo "
<tr>
    <th>Fecha</th>
    <th>Categoría</th>
    <th>Local</th>
    <th>Visitante</th>
    <th>Resultado</th>
    <th>Velocidad máxima de la pelota (simulada)</th>
    <th>Estado</th>
    ".($esAdministrador ? "<th>Actualizar</th>" : "")."
</tr>
";

// Muestra una fila informativa cuando todavía no hay partidos.
if (mysqli_num_rows($resultado) == 0) {

    echo "
    <tr>
        <td colspan='".($esAdministrador ? "8" : "7")."'>
            No hay partidos registrados.
        </td>
    </tr>
    ";

} else {

    // Recorre los partidos y sustituye los resultados vacíos por su estado.
    while ($fila = mysqli_fetch_assoc($resultado)) {

        $resultado_partido = $fila['resultado'] ?? '';
        $estadoEtiqueta = [
            "sin_fecha" => "Sin fecha",
            "programado" => "Programado",
            "jugado" => "Jugado",
            "pendiente" => "Pendiente",
            "suspendido" => "Suspendido"
        ][$fila["estado"]] ?? "Pendiente";
        $fechaEtiqueta = $fila["fecha"] ?: "Sin fecha";
        $velocidadSimulada = $fila["velocidad_maxima_balon_kmh"] !== null
            ? (int) $fila["velocidad_maxima_balon_kmh"] . " km/h (simulada)"
            : ($resultado_partido !== "" ? "Sin goles" : "Sin dato");
        $filaAdmin = "";
        if ($esAdministrador) {
            $opcionesEstado = "";
            foreach ([
                "sin_fecha" => "Sin fecha",
                "programado" => "Programado",
                "jugado" => "Jugado",
                "pendiente" => "Pendiente",
                "suspendido" => "Suspendido"
            ] as $valorEstado => $textoEstado) {
                $seleccionado = $fila["estado"] === $valorEstado ? " selected" : "";
                $opcionesEstado .= "<option value='" . $valorEstado . "'" . $seleccionado . ">" . $textoEstado . "</option>";
            }
            $fechaValor = htmlspecialchars((string) ($fila["fecha"] ?? ""), ENT_QUOTES, "UTF-8");
            $idPartido = (int) $fila["id"];
            $filaAdmin = "
                <td>
                    <form class='estado-partido-form' action='actualizar_estado_partido.php' method='POST'>
                        <input type='hidden' name='partido_id' value='" . $idPartido . "'>
                        <select name='estado' aria-label='Estado del partido " . $idPartido . "' onchange='const fecha=this.form.elements.fecha;fecha.disabled=this.value===\"sin_fecha\";fecha.required=!fecha.disabled;if(fecha.disabled)fecha.value=\"\";'>" . $opcionesEstado . "</select>
                        <input type='date' name='fecha' value='" . $fechaValor . "' min='" . date("Y") . "-01-01' aria-label='Fecha del partido " . $idPartido . "'" . ($fechaValor === "" ? " disabled" : "") . ">
                        <button type='submit'>Guardar</button>
                    </form>
                </td>";
        }

        echo "
        <tr>
            <td>".htmlspecialchars((string) $fechaEtiqueta, ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $fila['nombre_categoria'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $fila['nombre_local'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $fila['nombre_visitante'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $resultado_partido, ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($velocidadSimulada, ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($estadoEtiqueta, ENT_QUOTES, 'UTF-8')."</td>
            ".$filaAdmin."
        </tr>
        ";

    }

}

echo "</table>";

?>