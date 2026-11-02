<?php

// Endpoint administrativo para borrar un boletín y su PDF almacenado.
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/respuesta_json.php";
require_admin();

// Comprueba que el identificador del boletín sea válido.
$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    responderJson(["error" => "El boletín indicado no es válido."], 400);
}

// Lee la ruta del PDF antes de borrar el registro de la base de datos.
$consulta = mysqli_prepare($conexion, "SELECT archivo_pdf FROM boletines WHERE id = ? LIMIT 1");
if (!$consulta) {
    responderJson(["error" => "No se pudo consultar el boletín."], 500);
}

mysqli_stmt_bind_param($consulta, "i", $id);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    responderJson(["error" => "No se pudo consultar el boletín."], 500);
}

mysqli_stmt_bind_result($consulta, $ruta);
$encontrado = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);
if (!$encontrado) {
    responderJson(["error" => "No se encontró el boletín."], 404);
}

// Elimina el registro del boletín con un identificador parametrizado.
$eliminar = mysqli_prepare($conexion, "DELETE FROM boletines WHERE id = ?");
if (!$eliminar) {
    responderJson(["error" => "No se pudo preparar la eliminación."], 500);
}

mysqli_stmt_bind_param($eliminar, "i", $id);
if (!mysqli_stmt_execute($eliminar)) {
    mysqli_stmt_close($eliminar);
    responderJson(["error" => "No se pudo eliminar el boletín."], 500);
}
mysqli_stmt_close($eliminar);

// Solo borra archivos con la ruta interna esperada y registra fallos de limpieza.
if (preg_match('#^uploads/boletines/[a-f0-9]{32}\.pdf$#', $ruta)) {
    $rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta);
    if (is_file($rutaCompleta) && !unlink($rutaCompleta)) {
        error_log("No se pudo limpiar PDF eliminado: " . $rutaCompleta);
    }
}

responderJson(["ok" => true]);

?>
