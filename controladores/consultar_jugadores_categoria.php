<?php

// Consulta jugadores de una categoría, junto con club y documentos asociados.
require_once __DIR__ . '/../modelos/conexion.php';
$club_id_usuario = current_club_id();
$club_id_consultado = $club_id_usuario;
if (array_key_exists('club_id', $_GET)) {
    $club_solicitado = $_GET['club_id'];
    if (!is_string($club_solicitado) || !ctype_digit($club_solicitado) || (int) $club_solicitado < 1) {
        http_response_code(400);
        exit("El club indicado no es válido.");
    }
    $club_id_consultado = (int) $club_solicitado;
}

// Lee el filtro que identifica la categoría solicitada.
$categoria_id = $_GET['categoria_id'] ?? '';

// No ejecuta la consulta si la petición no indicó categoría.
if (!is_string($categoria_id) || !ctype_digit($categoria_id) || (int) $categoria_id < 1) {
    http_response_code(400);
    echo "No se seleccionó ninguna categoría.";
    exit;
}

// Incluye el carnet más reciente cuando existe y ordena por apellido y nombre.
$sql = "
SELECT jugadores.cedula,
       jugadores.nombre,
       jugadores.apellido,
       jugadores.masa,
       jugadores.altura,
       jugadores.foto,
       carnets_aptitud.archivo AS archivo_aptitud,
       carnets_aptitud.fecha_vencimiento,
       clubes.nombre AS nombre_club
FROM jugadores
INNER JOIN clubes ON jugadores.club_id = clubes.id
LEFT JOIN (
    SELECT cedula_jugador, MAX(id) AS carnet_id
    FROM carnets_aptitud
    GROUP BY cedula_jugador
) AS ultimo_carnet ON ultimo_carnet.cedula_jugador = jugadores.cedula
LEFT JOIN carnets_aptitud ON carnets_aptitud.id = ultimo_carnet.carnet_id
WHERE jugadores.categoria_id = ?
";
$tipos = "i";
if ($club_id_consultado !== null) {
    $sql .= " AND jugadores.club_id = ?";
    $tipos .= "i";
}
$sql .= " ORDER BY jugadores.apellido, jugadores.nombre";

$consulta = mysqli_prepare($conexion, $sql);
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo preparar la consulta de jugadores.");
}
$categoria_id_numero = (int) $categoria_id;
if ($club_id_consultado !== null) {
    mysqli_stmt_bind_param($consulta, $tipos, $categoria_id_numero, $club_id_consultado);
} else {
    mysqli_stmt_bind_param($consulta, $tipos, $categoria_id_numero);
}
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar la lista de jugadores.");
}
$resultado = mysqli_stmt_get_result($consulta);

if (!$resultado) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    echo "Error al consultar jugadores.";
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

// Define la presentación de la lista incrustada en la página.
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

.tabla-jugadores-contenedor{
    overflow-x:auto;
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

.aptitud-vigente{
    color:#2d9a5b;
    font-weight:bold;
}

.aptitud-vencida,
.aptitud-sin-vigencia{
    color:#c45c66;
    font-weight:bold;
}

.formulario-aptitud{
    min-width:190px;
    padding:8px;
    background:#0b1017;
    border:1px solid #283342;
    border-radius:5px;
}

.formulario-aptitud input{
    max-width:180px;
    margin:4px 0;
}

.formulario-aptitud button{
    padding:7px 10px;
    border:0;
    border-radius:4px;
    background:#4b91df;
    color:#08111c;
    cursor:pointer;
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

.estado-subida-aptitud{
    display:block;
    margin-top:5px;
}

</style>
";

if ($jugadoresSinAptitud) {
    echo '<section class="aviso-aptitud" role="status">';
    echo '<h3>Aviso: ' . count($jugadoresSinAptitud) . ' jugador(es) sin aptitud física registrada</h3>';
    echo '<ul>';
    foreach ($jugadoresSinAptitud as $jugadorSinAptitud) {
        $nombreCompleto = $jugadorSinAptitud['nombre'] . ' ' . $jugadorSinAptitud['apellido'];
        echo '<li>' . htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul></section>';
}

// Presenta las columnas que se mostrarán para cada jugador.
echo '<div class="tabla-jugadores-contenedor">';
echo "<table>";

echo "
<tr>
    <th>Cédula</th>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Masa (kg)</th>
    <th>Altura (m)</th>
    <th>Foto</th>
    <th>Aptitud física / vigencia</th>
    <th>Club</th>
    <th>Formulario</th>
    ".(($club_id_usuario !== null && $club_id_consultado === $club_id_usuario)
        ? "<th>Actualizar aptitud</th>"
        : "")."
</tr>
";

// Informa cuando la categoría no tiene jugadores registrados.
if (count($jugadores) === 0) {

    echo "
    <tr>
        <td colspan='".(($club_id_usuario !== null && $club_id_consultado === $club_id_usuario) ? "10" : "9")."'>No hay jugadores registrados en esta categoría.</td>
    </tr>
    ";

} else {

    // Renderiza cada jugador y los enlaces disponibles a sus documentos.
    foreach ($jugadores as $fila) {
        $nombreCompleto = htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido'], ENT_QUOTES, 'UTF-8');
        $fechaVencimiento = $fila['fecha_vencimiento'] ?? '';
        if (empty($fila['archivo_aptitud'])) {
            $estadoAptitud = '<span class="aptitud-sin-vigencia">Sin carnet</span>';
        } elseif ($fechaVencimiento === '') {
            $estadoAptitud = '<span class="aptitud-sin-vigencia">Vigencia no registrada</span>';
        } elseif ($fechaVencimiento < date('Y-m-d')) {
            $estadoAptitud = '<span class="aptitud-vencida">Vencida: ' . htmlspecialchars($fechaVencimiento, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            $estadoAptitud = '<span class="aptitud-vigente">Vigente hasta ' . htmlspecialchars($fechaVencimiento, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        echo "
        <tr>
            <td>".htmlspecialchars($fila['cedula'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($fila['apellido'], ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($fila['masa'] ?? 'Sin registrar', ENT_QUOTES, 'UTF-8')."</td>
            <td>".htmlspecialchars($fila['altura'] ?? 'Sin registrar', ENT_QUOTES, 'UTF-8')."</td>
            <td>".(!empty($fila['foto'])
                ? "<img src=\"servir_archivo_jugador.php?tipo=foto&amp;cedula=".rawurlencode($fila['cedula'])."\" alt=\"Foto de ".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."\" width=\"72\" height=\"72\" style=\"object-fit:cover;border-radius:50%;\">"
                : "Sin foto")."</td>
            <td>".(!empty($fila['archivo_aptitud'])
                ? "<a href=\"servir_archivo_jugador.php?tipo=aptitud&amp;cedula=".rawurlencode($fila['cedula'])."\" target=\"_blank\" rel=\"noopener\">Ver carnet</a><br>"
                : "").$estadoAptitud."</td>
            <td>".htmlspecialchars($fila['nombre_club'], ENT_QUOTES, 'UTF-8')."</td>
            <td><a class=\"boton-formulario\" href=\"imprimir_jugador.php?cedula=".rawurlencode($fila['cedula'])."\" target=\"_blank\" rel=\"noopener\" aria-label=\"Abrir formulario de impresión de ".$nombreCompleto."\" title=\"Abrir formulario\"><svg viewBox=\"0 0 24 24\" aria-hidden=\"true\"><path d=\"M19 8H5a3 3 0 0 0-3 3v5h4v4h12v-4h4v-5a3 3 0 0 0-3-3Zm-3 10H8v-5h8Zm3-7a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM18 3H6v4h12Z\"/></svg></a></td>
            ".(($club_id_usuario !== null && $club_id_consultado === $club_id_usuario)
                ? "<td><form class=\"formulario-aptitud\" enctype=\"multipart/form-data\" onsubmit=\"return subirAptitudClub(event, this)\"><input type=\"hidden\" name=\"cedula_jugador\" value=\"".htmlspecialchars($fila['cedula'], ENT_QUOTES, 'UTF-8')."\"><label>Vencimiento: <input type=\"date\" name=\"fecha_vencimiento\" required></label><input type=\"file\" name=\"aptitud_fisica\" accept=\"application/pdf,image/jpeg,image/png\" required><button type=\"submit\">Subir carnet</button><span class=\"estado-subida-aptitud\" role=\"status\"></span></form></td>"
                : "")."
        </tr>
        ";

    }

}

echo "</table>";
echo "</div>";
mysqli_stmt_close($consulta);

?>