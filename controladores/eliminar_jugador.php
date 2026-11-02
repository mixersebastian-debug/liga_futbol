<?php

// Elimina un jugador y limpia sus archivos asociados una vez borrado el registro.
require_once __DIR__ . "/../modelos/conexion.php";
require_admin();

// Presenta un aviso y vuelve al panel con el código HTTP correspondiente.
function responderEliminacion($mensaje, $codigo = 200) {
    http_response_code($codigo);
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

// Solo elimina archivos ubicados en las carpetas y con los nombres esperados.
function eliminarArchivoJugador($ruta, $carpeta) {
    $patron = '#^uploads/' . $carpeta . '/[a-f0-9]{32}\.(jpg|png|webp|pdf)$#';
    if (!is_string($ruta) || !preg_match($patron, $ruta)) {
        return;
    }

    $rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta);
    if (is_file($rutaCompleta) && !unlink($rutaCompleta)) {
        error_log("No se pudo limpiar el archivo del jugador: " . $rutaCompleta);
    }
}

// Valida la cédula antes de consultar al jugador.
$cedula = trim($_POST['cedula'] ?? '');

if (!preg_match('/^[0-9]{8}$/D', $cedula)) {
    responderEliminacion('Debe ingresar una cédula válida de 8 números.', 400);
}

// Obtiene la foto del jugador para eliminarla después de borrar su registro.
$consulta = mysqli_prepare($conexion, "SELECT foto FROM jugadores WHERE cedula = ? LIMIT 1");
if (!$consulta) {
    responderEliminacion('No se pudo consultar el jugador.', 500);
}

mysqli_stmt_bind_param($consulta, "s", $cedula);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    responderEliminacion('No se pudo consultar el jugador.', 500);
}
mysqli_stmt_bind_result($consulta, $foto);
$jugadorExiste = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

if (!$jugadorExiste) {
    responderEliminacion('No se encontró ningún jugador con esa cédula.', 404);
}

// Reúne las rutas de todos los documentos de aptitud relacionados.
$archivosAptitud = [];
$consulta = mysqli_prepare($conexion, "SELECT archivo FROM carnets_aptitud WHERE cedula_jugador = ?");
if (!$consulta) {
    responderEliminacion('No se pudieron consultar los documentos del jugador.', 500);
}

mysqli_stmt_bind_param($consulta, "s", $cedula);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    responderEliminacion('No se pudieron consultar los documentos del jugador.', 500);
}
mysqli_stmt_bind_result($consulta, $archivoAptitud);
while (mysqli_stmt_fetch($consulta)) {
    $archivosAptitud[] = $archivoAptitud;
}
mysqli_stmt_close($consulta);

// Elimina el registro mediante una consulta parametrizada.
$eliminar = mysqli_prepare($conexion, "DELETE FROM jugadores WHERE cedula = ?");
if (!$eliminar) {
    responderEliminacion('No se pudo preparar la eliminación del jugador.', 500);
}

mysqli_stmt_bind_param($eliminar, "s", $cedula);
if (!mysqli_stmt_execute($eliminar)) {
    $error = mysqli_stmt_error($eliminar);
    mysqli_stmt_close($eliminar);
    responderEliminacion('Error al eliminar jugador: ' . $error, 500);
}
mysqli_stmt_close($eliminar);

// Limpia la foto y los documentos que se recopilaron previamente.
eliminarArchivoJugador($foto, 'fotos');
foreach ($archivosAptitud as $archivo) {
    eliminarArchivoJugador($archivo, 'aptitud');
}

responderEliminacion('Jugador eliminado correctamente');

?>
