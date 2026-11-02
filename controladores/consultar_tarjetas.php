<?php

// Consulta sanciones y datos del jugador y club cuando sus relaciones existen.
require_once __DIR__ . '/../modelos/conexion.php';
$club_id = current_club_id();

// Ordena las tarjetas por fecha e identificador, de la más reciente a la anterior.
$sql = "
SELECT
    tarjetas.id,
    jugadores.cedula,
    jugadores.nombre,
    jugadores.apellido,
    clubes.nombre AS nombre_club,
    categorias.nombre AS nombre_categoria,
    tarjetas.tipo,
    tarjetas.fecha
FROM tarjetas
LEFT JOIN jugadores
    ON tarjetas.jugador_cedula = jugadores.cedula
LEFT JOIN clubes
    ON jugadores.club_id = clubes.id
LEFT JOIN categorias
    ON jugadores.categoria_id = categorias.id
";
if ($club_id !== null) {
    $sql .= " WHERE jugadores.club_id = " . $club_id;
}
$sql .= " ORDER BY tarjetas.fecha DESC, tarjetas.id DESC";

// Informa de los errores antes de generar la tabla HTML.
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    echo "<p>Error al consultar las tarjetas: " . mysqli_error($conexion) . "</p>";
    exit;
}

// Mantiene la tabla legible dentro de la vista que la solicita.
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
";

// Escribe los nombres de las columnas de sanciones.
echo "<table>";

echo "
<tr>
    <th>ID</th>
    <th>Cédula</th>
    <th>Jugador</th>
    <th>Club</th>
    <th>Categoría</th>
    <th>Tarjeta</th>
    <th>Fecha</th>
</tr>
";

// Presenta una fila vacía informativa cuando no hay tarjetas.
if (mysqli_num_rows($resultado) == 0) {

    echo "
    <tr>
        <td colspan='7'>
            No hay tarjetas registradas.
        </td>
    </tr>
    ";

} else {

    // Completa datos faltantes con etiquetas entendibles y dibuja cada fila.
    while ($fila = mysqli_fetch_assoc($resultado)) {

        if ($fila['nombre'] == null) {
            $jugador = "Jugador no encontrado";
        } else {
            $jugador = $fila['nombre'] . " " . $fila['apellido'];
        }

        if ($fila['nombre_club'] == null) {
            $club = "Sin club";
        } else {
            $club = $fila['nombre_club'];
        }

        echo "
        <tr>
            <td>".$fila['id']."</td>
            <td>".htmlspecialchars((string) $fila['cedula'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $jugador, ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $club, ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) ($fila['nombre_categoria'] ?? 'Sin categoría'), ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $fila['tipo'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars((string) $fila['fecha'], ENT_QUOTES, 'UTF-8')."</td>
        </tr>
        ";
    }
}

echo "</table>";

?>