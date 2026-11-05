<?php

require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . '/../modelos/validacion_cedula.php';
require_admin();

function responderModificacionJugador(string $mensaje, int $estado = 200): void
{
    http_response_code($estado);
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

function validarArchivoJugadorModificar(array $archivo, array $tiposPermitidos, int $tamanoMaximo, string $nombre): ?string
{
    $error = $archivo["error"] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($archivo["tmp_name"] ?? "")) {
        throw new RuntimeException("No se pudo recibir " . $nombre . ". Intenta subirlo nuevamente.");
    }
    if (($archivo["size"] ?? 0) <= 0 || $archivo["size"] > $tamanoMaximo) {
        throw new RuntimeException("El archivo de " . $nombre . " supera el tamaño permitido.");
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo["tmp_name"]);
    if (!isset($tiposPermitidos[$mime])) {
        throw new RuntimeException("El formato del archivo de " . $nombre . " no está permitido.");
    }

    return $tiposPermitidos[$mime];
}

function guardarArchivoJugadorModificar(array $archivo, string $carpeta, string $extension): string
{
    $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $carpeta;
    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        throw new RuntimeException("No se pudo preparar el almacenamiento de los archivos.");
    }

    $nombre = bin2hex(random_bytes(16)) . "." . $extension;
    $destino = $directorio . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($archivo["tmp_name"], $destino)) {
        throw new RuntimeException("No se pudo guardar uno de los archivos subidos.");
    }

    return "uploads/" . $carpeta . "/" . $nombre;
}

function eliminarArchivoJugadorModificar(?string $ruta): void
{
    if ($ruta === null) {
        return;
    }

    $rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta);
    if (is_file($rutaCompleta) && !unlink($rutaCompleta)) {
        error_log("No se pudo limpiar el archivo subido: " . $rutaCompleta);
    }
}

function fallarModificacionJugador(
    mysqli $conexion,
    string $mensaje,
    int $estado,
    ?string $rutaFoto = null,
    ?string $rutaAptitud = null
): void {
    mysqli_rollback($conexion);
    eliminarArchivoJugadorModificar($rutaFoto);
    eliminarArchivoJugadorModificar($rutaAptitud);
    responderModificacionJugador($mensaje, $estado);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

$cedula = trim($_POST["cedula"] ?? "");
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$fechaNacimiento = trim($_POST["fecha_nacimiento"] ?? "");
$clubId = filter_var($_POST["club_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$categoriaId = filter_var($_POST["categoria_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$mensajeCedulaInvalida = "La cédula no es válida. Ingrese una correcta.";

if (!validarCedulaUruguaya($cedula)) {
    responderModificacionJugador($mensajeCedulaInvalida, 400);
}

if (
    preg_match('/^.{3,}$/us', $nombre) !== 1
    || preg_match('/^.{3,}$/us', $apellido) !== 1
    || $clubId === false
    || $categoriaId === false
) {
    responderModificacionJugador("Completa los datos del jugador correctamente.", 400);
}

$fechaValida = DateTime::createFromFormat("!Y-m-d", $fechaNacimiento);
$erroresFecha = DateTime::getLastErrors();
if (
    !$fechaValida
    || ($erroresFecha !== false && ($erroresFecha["warning_count"] > 0 || $erroresFecha["error_count"] > 0))
    || $fechaValida->format("Y-m-d") !== $fechaNacimiento
    || $fechaValida->format("Y") < "2013"
    || $fechaValida->format("Y") > "2019"
) {
    responderModificacionJugador("Indica una fecha de nacimiento válida entre 2013 y 2019.", 400);
}

$tiposFoto = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];
$tiposAptitud = [
    "application/pdf" => "pdf",
    "image/jpeg" => "jpg",
    "image/png" => "png"
];
try {
    $extensionFoto = validarArchivoJugadorModificar(
        $_FILES["foto"] ?? ["error" => UPLOAD_ERR_NO_FILE],
        $tiposFoto,
        5 * 1024 * 1024,
        "la foto"
    );
    $extensionAptitud = validarArchivoJugadorModificar(
        $_FILES["aptitud_fisica"] ?? ["error" => UPLOAD_ERR_NO_FILE],
        $tiposAptitud,
        10 * 1024 * 1024,
        "el carnet de aptitud física"
    );
} catch (RuntimeException $error) {
    responderModificacionJugador($error->getMessage(), 400);
}

$fechaVencimiento = trim($_POST["fecha_vencimiento"] ?? "");
if ($extensionAptitud !== null) {
    $fechaVencimientoValida = DateTime::createFromFormat("!Y-m-d", $fechaVencimiento);
    $erroresFechaVencimiento = DateTime::getLastErrors();
    if (
        !$fechaVencimientoValida
        || ($erroresFechaVencimiento !== false
            && ($erroresFechaVencimiento["warning_count"] > 0 || $erroresFechaVencimiento["error_count"] > 0))
        || $fechaVencimientoValida->format("Y-m-d") !== $fechaVencimiento
    ) {
        responderModificacionJugador("Indica la fecha de vencimiento del carnet.", 400);
    }
}

if (!mysqli_begin_transaction($conexion)) {
    http_response_code(500);
    exit("No se pudo iniciar la modificación del jugador.");
}

$consultaJugador = mysqli_prepare($conexion, "SELECT foto FROM jugadores WHERE cedula = ? FOR UPDATE");
if (!$consultaJugador) {
    fallarModificacionJugador($conexion, "No se pudo consultar el jugador.", 500);
}
mysqli_stmt_bind_param($consultaJugador, "s", $cedula);
if (!mysqli_stmt_execute($consultaJugador)) {
    mysqli_stmt_close($consultaJugador);
    fallarModificacionJugador($conexion, "No se pudo consultar el jugador.", 500);
}
mysqli_stmt_bind_result($consultaJugador, $fotoActual);
$jugadorExiste = mysqli_stmt_fetch($consultaJugador);
mysqli_stmt_close($consultaJugador);
if (!$jugadorExiste) {
    fallarModificacionJugador($conexion, "No se encontró ningún jugador con esa cédula.", 404);
}

$consultaAptitud = mysqli_prepare(
    $conexion,
    "SELECT archivo FROM carnets_aptitud WHERE cedula_jugador = ? ORDER BY id DESC LIMIT 1"
);
if (!$consultaAptitud) {
    fallarModificacionJugador($conexion, "No se pudo consultar el carnet del jugador.", 500);
}
mysqli_stmt_bind_param($consultaAptitud, "s", $cedula);
if (!mysqli_stmt_execute($consultaAptitud)) {
    mysqli_stmt_close($consultaAptitud);
    fallarModificacionJugador($conexion, "No se pudo consultar el carnet del jugador.", 500);
}
mysqli_stmt_bind_result($consultaAptitud, $archivoAptitudActual);
$tieneAptitud = mysqli_stmt_fetch($consultaAptitud);
mysqli_stmt_close($consultaAptitud);

if ($extensionFoto !== null && !empty($fotoActual)) {
    fallarModificacionJugador($conexion, "El jugador ya tiene una foto registrada.", 409);
}
if ($extensionAptitud !== null && $tieneAptitud) {
    fallarModificacionJugador($conexion, "El jugador ya tiene un carnet de aptitud registrado.", 409);
}

$rutaFoto = null;
$rutaAptitud = null;
try {
    if ($extensionFoto !== null) {
        $rutaFoto = guardarArchivoJugadorModificar($_FILES["foto"], "fotos", $extensionFoto);
    }
    if ($extensionAptitud !== null) {
        $rutaAptitud = guardarArchivoJugadorModificar($_FILES["aptitud_fisica"], "aptitud", $extensionAptitud);
    }
} catch (RuntimeException $error) {
    fallarModificacionJugador($conexion, $error->getMessage(), 500, $rutaFoto, $rutaAptitud);
}

$actualizar = mysqli_prepare(
    $conexion,
    "UPDATE jugadores
     SET nombre = ?, apellido = ?, fecha_nacimiento = ?, club_id = ?, categoria_id = ?,
         foto = COALESCE(foto, ?)
     WHERE cedula = ?"
);
if (!$actualizar) {
    fallarModificacionJugador(
        $conexion,
        "No se pudo preparar la modificación del jugador.",
        500,
        $rutaFoto,
        $rutaAptitud
    );
}
mysqli_stmt_bind_param(
    $actualizar,
    "sssssss",
    $nombre,
    $apellido,
    $fechaNacimiento,
    $clubId,
    $categoriaId,
    $rutaFoto,
    $cedula
);
if (!mysqli_stmt_execute($actualizar)) {
    error_log("Error al modificar el jugador: " . mysqli_stmt_error($actualizar));
    mysqli_stmt_close($actualizar);
    fallarModificacionJugador(
        $conexion,
        "No se pudo modificar el jugador.",
        500,
        $rutaFoto,
        $rutaAptitud
    );
}
mysqli_stmt_close($actualizar);

if ($rutaAptitud !== null) {
    $insertarAptitud = mysqli_prepare(
        $conexion,
        "INSERT INTO carnets_aptitud (cedula_jugador, archivo, fecha_vencimiento) VALUES (?, ?, ?)"
    );
    if (!$insertarAptitud) {
        fallarModificacionJugador(
            $conexion,
            "No se pudo preparar el registro del carnet.",
            500,
            $rutaFoto,
            $rutaAptitud
        );
    }
    mysqli_stmt_bind_param($insertarAptitud, "sss", $cedula, $rutaAptitud, $fechaVencimiento);
    if (!mysqli_stmt_execute($insertarAptitud)) {
        error_log("Error al registrar el carnet del jugador: " . mysqli_stmt_error($insertarAptitud));
        mysqli_stmt_close($insertarAptitud);
        fallarModificacionJugador(
            $conexion,
            "No se pudo registrar el carnet de aptitud física.",
            500,
            $rutaFoto,
            $rutaAptitud
        );
    }
    mysqli_stmt_close($insertarAptitud);
}

if (!mysqli_commit($conexion)) {
    fallarModificacionJugador(
        $conexion,
        "No se pudo completar la modificación del jugador.",
        500,
        $rutaFoto,
        $rutaAptitud
    );
}

$mensaje = "Jugador modificado correctamente.";
if ($rutaFoto !== null || $rutaAptitud !== null) {
    $mensaje .= " Se agregaron los archivos seleccionados.";
}
responderModificacionJugador($mensaje);

?>
