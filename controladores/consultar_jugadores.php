<?php

// Recupera los jugadores, su club y el carnet de aptitud más reciente.
require_once __DIR__ . '/../modelos/conexion.php';
$club_id = current_club_id();

// La subconsulta elige el último carnet por jugador y conserva jugadores sin carnet.
$sql = "
SELECT jugadores.*, clubes.nombre AS nombre_club,
       carnets_aptitud.archivo AS archivo_aptitud
FROM jugadores
INNER JOIN clubes ON jugadores.club_id = clubes.id
LEFT JOIN (
    SELECT cedula_jugador, MAX(id) AS carnet_id
    FROM carnets_aptitud
    GROUP BY cedula_jugador
) AS ultimo_carnet ON ultimo_carnet.cedula_jugador = jugadores.cedula
LEFT JOIN carnets_aptitud ON carnets_aptitud.id = ultimo_carnet.carnet_id
";
if ($club_id !== null) {
    $sql .= " WHERE jugadores.club_id = " . $club_id;
}

// Detiene la respuesta si no se pudo consultar la lista.
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {

    echo "<p>Error al consultar los jugadores: " . mysqli_error($conexion) . "</p>";
    exit;

}

$jugadores = [];
$jugadoresSinAptitud = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $jugadores[] = $fila;
    if (empty($fila['archivo_aptitud'])) {
        $jugadoresSinAptitud[] = $fila;
    }
}

// Estilos de la tabla y del enlace que abre la ficha imprimible.
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

.tabla-jugadores-contenedor {
    overflow-x: auto;
}

.tabla-jugadores-contenedor table {
    min-width: 1400px;
}

.aviso-aptitud{
    margin:0 0 16px;
    padding:12px 14px;
    background:#382b12;
    border:1px solid #d5a83b;
    border-left:4px solid #d5a83b;
    border-radius:5px;
    color:#fff2cc;
}

.aviso-aptitud h3{
    margin:0 0 8px;
}

.aviso-aptitud ul{
    margin:8px 0 0;
    padding-left:22px;
}

.boton-formulario{
    display:inline-block;
    padding:8px;
    border-radius:4px;
    background:#4b91df;
    color:#08111c;
    line-height:0;
    text-decoration:none;
}

.boton-formulario svg{
    width:20px;
    height:20px;
    fill:currentColor;
}

.boton-formulario:hover{
    background:#70a9e8;
}

</style>
";

if ($jugadoresSinAptitud) {
    echo '<section class="aviso-aptitud" role="status">';
    echo '<h3>Aviso: ' . count($jugadoresSinAptitud) . ' jugador(es) sin aptitud física registrada</h3>';
    echo '<ul>';
    foreach ($jugadoresSinAptitud as $jugadorSinAptitud) {
        $nombreCompleto = $jugadorSinAptitud['nombre'] . ' ' . $jugadorSinAptitud['apellido'];
        echo '<li>' . htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8')
            . ' — ' . htmlspecialchars($jugadorSinAptitud['nombre_club'], ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul></section>';
}

// Escribe los encabezados de la lista de jugadores.
echo '<div class="tabla-jugadores-contenedor">';
echo "<table>";

echo "
<tr>
    <th>Cédula</th>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Fecha de nacimiento</th>
    <th>Masa (kg)</th>
    <th>Altura (m)</th>
    <th>Foto</th>
    <th>Aptitud física</th>
    <th>Club</th>
    <th>Formulario</th>
</tr>
";

// Renderiza los datos del jugador y los enlaces a sus archivos si existen.
foreach ($jugadores as $fila) {
    echo "
    <tr>
        <td>".htmlspecialchars($fila['cedula'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['apellido'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['fecha_nacimiento'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['masa'] ?? 'Sin registrar', ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['altura'] ?? 'Sin registrar', ENT_QUOTES, 'UTF-8')."</td>
        <td>".(!empty($fila['foto'])
            ? "<img src=\"servir_archivo_jugador.php?tipo=foto&amp;cedula=".rawurlencode($fila['cedula'])."\" alt=\"Foto de ".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."\" width=\"72\" height=\"72\" style=\"object-fit:cover;border-radius:50%;\">"
            : "Sin foto")."</td>
        <td>".(!empty($fila['archivo_aptitud'])
            ? "<a href=\"servir_archivo_jugador.php?tipo=aptitud&amp;cedula=".rawurlencode($fila['cedula'])."\" target=\"_blank\" rel=\"noopener\">Ver documento</a>"
            : "Sin documento")."</td>
        <td>".htmlspecialchars($fila['nombre_club'], ENT_QUOTES, 'UTF-8')."</td>
        <td><a class=\"boton-formulario\" href=\"imprimir_jugador.php?cedula=".rawurlencode($fila['cedula'])."\" target=\"_blank\" rel=\"noopener\" aria-label=\"Abrir formulario de impresión de ".htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido'], ENT_QUOTES, 'UTF-8')."\" title=\"Abrir formulario\"><svg viewBox=\"0 0 24 24\" aria-hidden=\"true\"><path d=\"M19 8H5a3 3 0 0 0-3 3v5h4v4h12v-4h4v-5a3 3 0 0 0-3-3Zm-3 10H8v-5h8Zm3-7a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM18 3H6v4h12Z\"/></svg></a></td>
    </tr>
    ";
}

echo "</table>";
echo "</div>";

?>