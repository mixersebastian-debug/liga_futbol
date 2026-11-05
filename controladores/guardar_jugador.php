<?php

// Valida los datos y archivos del jugador, y registra ambos en forma consistente.
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/validacion_cedula.php";
require_admin();

// Devuelve un mensaje y regresa al panel ante errores de validación.
function regresarConError($mensaje) {
    http_response_code(400);
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

// Verifica carga, tamaño y tipo MIME del archivo antes de aceptar su extensión.
function validarArchivo($archivo, $tiposPermitidos, $tamanoMaximo, $nombreCampo) {
    if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'] ?? '')) {
        regresarConError("No se pudo recibir " . $nombreCampo . ". Intente subirlo nuevamente.");
    }

    if (($archivo['size'] ?? 0) <= 0 || $archivo['size'] > $tamanoMaximo) {
        regresarConError("El archivo de " . $nombreCampo . " supera el tamaño permitido.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    if (!isset($tiposPermitidos[$mime])) {
        regresarConError("El formato de " . $nombreCampo . " no está permitido.");
    }

    return $tiposPermitidos[$mime];
}

// Guarda cada archivo usando un nombre aleatorio dentro de su carpeta destino.
function guardarArchivo($archivo, $carpeta, $extension) {
    $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . $carpeta;

    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        throw new RuntimeException("No se pudo preparar el almacenamiento de los archivos.");
    }

    $nombre = bin2hex(random_bytes(16)) . "." . $extension;
    $destino = $directorio . DIRECTORY_SEPARATOR . $nombre;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new RuntimeException("No se pudo guardar uno de los archivos subidos.");
    }

    return "uploads/" . $carpeta . "/" . $nombre;
}

// Intenta limpiar un archivo temporal ya guardado cuando el registro falla.
function eliminarArchivoGuardado($ruta) {
    if ($ruta !== null) {
        $rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta);
        if (is_file($rutaCompleta)) {
            if (!unlink($rutaCompleta)) {
                error_log("No se pudo limpiar el archivo subido: " . $rutaCompleta);
            }
        }
    }
}

// Recoge los datos del formulario y valida formato, medidas y fecha de nacimiento.
$cedula = trim($_POST['cedula'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$masa = trim($_POST['masa'] ?? '');
$altura = trim($_POST['altura'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$club_id = trim($_POST['club_id'] ?? '');
$categoria_id = trim($_POST['categoria_id'] ?? '');
$mensajeCedulaInvalida = 'La cédula no es válida. Ingrese una correcta.';

if (!validarCedulaUruguaya($cedula)) {
    regresarConError($mensajeCedulaInvalida);
}

$fechaNacimiento = DateTime::createFromFormat('!Y-m-d', $fecha_nacimiento);
$erroresFecha = DateTime::getLastErrors();
$fechaNacimientoValida = $fechaNacimiento !== false
    && ($erroresFecha === false || ($erroresFecha['warning_count'] === 0 && $erroresFecha['error_count'] === 0))
    && $fechaNacimiento->format('Y-m-d') === $fecha_nacimiento
    && $fechaNacimiento->format('Y') >= '2013'
    && $fechaNacimiento->format('Y') <= '2019';

if (
    preg_match('/^.{3,}$/us', $nombre) !== 1
    || preg_match('/^.{3,}$/us', $apellido) !== 1
    || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $masa)
    || !preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/D', $altura)
    || (float) $masa <= 0
    || (float) $masa > 999.99
    || (float) $altura < 0.01
    || (float) $altura > 3
    || !$fechaNacimientoValida
    || $club_id === ''
    || $categoria_id === ''
) {
    regresarConError(
        'Verifique los datos personales, las medidas y la fecha de nacimiento entre 2013 y 2019.'
    );
}

// Define los tipos permitidos para la fotografía y el documento de aptitud.
$tiposFoto = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];
$tiposAptitud = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png'
];
$extensionFoto = validarArchivo($_FILES['foto'] ?? null, $tiposFoto, 5 * 1024 * 1024, 'la foto');
$extensionAptitud = validarArchivo(
    $_FILES['aptitud_fisica'] ?? null,
    $tiposAptitud,
    10 * 1024 * 1024,
    'el documento de aptitud física'
);

// Comprueba en la base que no exista otro jugador con la misma cédula.
$consulta = mysqli_prepare($conexion, "SELECT cedula FROM jugadores WHERE cedula = ? LIMIT 1");
if (!$consulta) {
    http_response_code(500);
    exit("Error al verificar la cédula: " . htmlspecialchars(mysqli_error($conexion), ENT_QUOTES, 'UTF-8'));
}

mysqli_stmt_bind_param($consulta, "s", $cedula);
if (!mysqli_stmt_execute($consulta)) {
    http_response_code(500);
    exit("Error al verificar la cédula: " . htmlspecialchars(mysqli_stmt_error($consulta), ENT_QUOTES, 'UTF-8'));
}

mysqli_stmt_store_result($consulta);
$jugadorExiste = mysqli_stmt_num_rows($consulta) > 0;
mysqli_stmt_close($consulta);

if ($jugadorExiste) {
    regresarConError('Ese jugador ya fue registrado. La cédula ya está en uso.');
}

// Guarda los documentos y limpia cualquiera que ya se haya movido si ocurre un error.
$rutaFoto = null;
$rutaAptitud = null;

try {
    if ($extensionFoto !== null) {
        $rutaFoto = guardarArchivo($_FILES['foto'], 'fotos', $extensionFoto);
    }
    if ($extensionAptitud !== null) {
        $rutaAptitud = guardarArchivo($_FILES['aptitud_fisica'], 'aptitud', $extensionAptitud);
    }
} catch (RuntimeException $error) {
    eliminarArchivoGuardado($rutaFoto);
    eliminarArchivoGuardado($rutaAptitud);
    regresarConError($error->getMessage());
}

// Inserta la ficha del jugador dentro de una transacción.
if (!mysqli_begin_transaction($conexion)) {
    eliminarArchivoGuardado($rutaFoto);
    eliminarArchivoGuardado($rutaAptitud);
    http_response_code(500);
    exit("No se pudo iniciar el registro del jugador.");
}

$sqlJugador = mysqli_prepare(
    $conexion,
    "INSERT INTO jugadores
    (cedula, nombre, apellido, fecha_nacimiento, masa, altura, club_id, categoria_id, foto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$sqlJugador) {
    mysqli_rollback($conexion);
    eliminarArchivoGuardado($rutaFoto);
    eliminarArchivoGuardado($rutaAptitud);
    http_response_code(500);
    exit("Error al preparar el registro del jugador: " . htmlspecialchars(mysqli_error($conexion), ENT_QUOTES, 'UTF-8'));
}

mysqli_stmt_bind_param(
    $sqlJugador,
    "sssssssss",
    $cedula,
    $nombre,
    $apellido,
    $fecha_nacimiento,
    $masa,
    $altura,
    $club_id,
    $categoria_id,
    $rutaFoto
);

if (!mysqli_stmt_execute($sqlJugador)) {
    $mensaje = mysqli_stmt_errno($sqlJugador) === 1062
        ? 'Ese jugador ya fue registrado. La cédula ya está en uso.'
        : "Error al agregar jugador: " . mysqli_stmt_error($sqlJugador);
    mysqli_stmt_close($sqlJugador);
    mysqli_rollback($conexion);
    eliminarArchivoGuardado($rutaFoto);
    eliminarArchivoGuardado($rutaAptitud);
    regresarConError($mensaje);
}
mysqli_stmt_close($sqlJugador);

// Registra el documento solo cuando se adjuntó uno.
if ($rutaAptitud !== null) {
    $sqlAptitud = mysqli_prepare(
        $conexion,
        "INSERT INTO carnets_aptitud (cedula_jugador, archivo) VALUES (?, ?)"
    );

    if (!$sqlAptitud) {
        mysqli_rollback($conexion);
        eliminarArchivoGuardado($rutaFoto);
        eliminarArchivoGuardado($rutaAptitud);
        http_response_code(500);
        exit("Error al preparar el registro de la aptitud física: " . htmlspecialchars(mysqli_error($conexion), ENT_QUOTES, 'UTF-8'));
    }

    mysqli_stmt_bind_param($sqlAptitud, "ss", $cedula, $rutaAptitud);

    if (!mysqli_stmt_execute($sqlAptitud)) {
        $error = mysqli_stmt_error($sqlAptitud);
        mysqli_stmt_close($sqlAptitud);
        mysqli_rollback($conexion);
        eliminarArchivoGuardado($rutaFoto);
        eliminarArchivoGuardado($rutaAptitud);
        http_response_code(500);
        exit("Error al guardar la aptitud física: " . htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
    }
    mysqli_stmt_close($sqlAptitud);
}

// Confirma todos los registros antes de informar que el alta fue exitosa.
if (!mysqli_commit($conexion)) {
    mysqli_rollback($conexion);
    eliminarArchivoGuardado($rutaFoto);
    eliminarArchivoGuardado($rutaAptitud);
    http_response_code(500);
    exit("No se pudo completar el registro del jugador.");
}

echo "<script>alert('Jugador agregado correctamente');window.location.href='index.php';</script>";

?>
