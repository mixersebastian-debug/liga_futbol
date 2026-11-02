<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/respuesta_json.php";

$club_id = current_club_id();
if ($club_id === null) {
    responderJson(["error" => "Solo las cuentas de club pueden cargar carnets."], 403);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderJson(["error" => "Método no permitido."], 405);
}

$cedula = trim($_POST["cedula_jugador"] ?? "");
$fecha_vencimiento = trim($_POST["fecha_vencimiento"] ?? "");
$fechaValida = DateTime::createFromFormat("!Y-m-d", $fecha_vencimiento);
$erroresFecha = DateTime::getLastErrors();
if (
    !preg_match('/^[0-9]{8}$/D', $cedula)
    || !$fechaValida
    || ($erroresFecha !== false && ($erroresFecha["warning_count"] > 0 || $erroresFecha["error_count"] > 0))
    || $fechaValida->format("Y-m-d") !== $fecha_vencimiento
) {
    responderJson(["error" => "Indica un jugador y una fecha de vencimiento válidos."], 400);
}

$archivo = $_FILES["aptitud_fisica"] ?? null;
if (!is_array($archivo) || ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    responderJson(["error" => "Selecciona el carnet de aptitud física."], 400);
}
if (($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($archivo["tmp_name"] ?? "")) {
    responderJson(["error" => "No se pudo recibir el carnet. Intenta subirlo nuevamente."], 400);
}
if (($archivo["size"] ?? 0) <= 0 || $archivo["size"] > 10 * 1024 * 1024) {
    responderJson(["error" => "El carnet no puede superar los 10 MB."], 400);
}

$tiposPermitidos = [
    "application/pdf" => "pdf",
    "image/jpeg" => "jpg",
    "image/png" => "png"
];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($archivo["tmp_name"]);
if (!isset($tiposPermitidos[$mime])) {
    responderJson(["error" => "El carnet debe ser PDF, JPG o PNG."], 400);
}

$consultaJugador = mysqli_prepare(
    $conexion,
    "SELECT cedula FROM jugadores WHERE cedula = ? AND club_id = ? LIMIT 1"
);
if (!$consultaJugador) {
    responderJson(["error" => "No se pudo verificar el jugador."], 500);
}
mysqli_stmt_bind_param($consultaJugador, "si", $cedula, $club_id);
if (!mysqli_stmt_execute($consultaJugador)) {
    mysqli_stmt_close($consultaJugador);
    responderJson(["error" => "No se pudo verificar el jugador."], 500);
}
mysqli_stmt_store_result($consultaJugador);
$jugadorExiste = mysqli_stmt_num_rows($consultaJugador) === 1;
mysqli_stmt_close($consultaJugador);
if (!$jugadorExiste) {
    responderJson(["error" => "Solo puedes cargar carnets de jugadores de tu club."], 403);
}

$directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "aptitud";
if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
    responderJson(["error" => "No se pudo preparar el almacenamiento de carnets."], 500);
}

$nombreArchivo = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$mime];
$rutaRelativa = "uploads/aptitud/" . $nombreArchivo;
$rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
if (!move_uploaded_file($archivo["tmp_name"], $rutaCompleta)) {
    responderJson(["error" => "No se pudo guardar el carnet."], 500);
}

$consultaAptitud = mysqli_prepare(
    $conexion,
    "INSERT INTO carnets_aptitud (cedula_jugador, archivo, fecha_vencimiento) VALUES (?, ?, ?)"
);
if (!$consultaAptitud) {
    if (!unlink($rutaCompleta)) {
        error_log("No se pudo limpiar el carnet sin registrar: " . $rutaCompleta);
    }
    responderJson(["error" => "No se pudo preparar el registro del carnet."], 500);
}

mysqli_stmt_bind_param($consultaAptitud, "sss", $cedula, $rutaRelativa, $fecha_vencimiento);
if (!mysqli_stmt_execute($consultaAptitud)) {
    $error = mysqli_stmt_error($consultaAptitud);
    mysqli_stmt_close($consultaAptitud);
    if (!unlink($rutaCompleta)) {
        error_log("No se pudo limpiar el carnet sin registrar: " . $rutaCompleta);
    }
    error_log("Error al registrar carnet de aptitud: " . $error);
    responderJson(["error" => "No se pudo registrar el carnet."], 500);
}

mysqli_stmt_close($consultaAptitud);
responderJson(["ok" => true, "mensaje" => "Carnet cargado correctamente."]);

?>
