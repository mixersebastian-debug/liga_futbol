<?php

// Obtiene las categorías ordenadas para completar listas desplegables.
require_once __DIR__ . '/../modelos/conexion.php';

// Consulta los identificadores y nombres que se necesitan en el formulario.
$club_id_usuario = current_club_id();
$club_id = $club_id_usuario;
if (array_key_exists('club_id', $_GET)) {
    $club_solicitado = $_GET['club_id'];
    if (!is_string($club_solicitado) || !ctype_digit($club_solicitado) || (int) $club_solicitado < 1) {
        http_response_code(400);
        exit("<option value=''>El club indicado no es válido.</option>");
    }
    $club_id = (int) $club_solicitado;
}
$sql = "SELECT id, nombre FROM categorias";
if ($club_id !== null) {
    $sql .= " WHERE id IN (SELECT DISTINCT categoria_id FROM jugadores WHERE club_id = ?)";
}
$sql .= " ORDER BY nombre";

$consulta = mysqli_prepare($conexion, $sql);
if (!$consulta) {
    http_response_code(500);
    exit("<option value=''>Error al cargar categorías</option>");
}
if ($club_id !== null) {
    mysqli_stmt_bind_param($consulta, "i", $club_id);
}
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("<option value=''>Error al cargar categorías</option>");
}
$resultado = mysqli_stmt_get_result($consulta);

// Informa del fallo al control que solicitó las opciones.
if (!$resultado) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    echo "<option value=''>Error al cargar categorías</option>";
    exit;
}

// Devuelve cada categoría como una opción HTML.
if (mysqli_num_rows($resultado) === 0 && $club_id !== null) {
    echo "<option value=''>Este club aún no tiene categorías.</option>";
    mysqli_stmt_close($consulta);
    exit;
}

while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "<option value='".(int) $fila['id']."'>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</option>";

}
mysqli_stmt_close($consulta);

?>