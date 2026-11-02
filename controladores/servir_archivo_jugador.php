<?php

// Entrega al navegador una foto o documento de aptitud asociado a un jugador.
require_once __DIR__ . "/../modelos/conexion.php";

// Valida la identidad del jugador y limita los tipos de archivo permitidos.
$cedula = trim($_GET['cedula'] ?? '');
$tipo = $_GET['tipo'] ?? '';
current_club_id();

if (!preg_match('/^[0-9]{8}$/D', $cedula) || !in_array($tipo, ['foto', 'aptitud'], true)) {
    http_response_code(400);
    exit("Solicitud de archivo no válida.");
}

// Selecciona la columna adecuada según se solicite una foto o un documento.
if ($tipo === 'foto') {
    $consulta = mysqli_prepare($conexion, "SELECT foto FROM jugadores WHERE cedula = ? LIMIT 1");
} else {
    $consulta = mysqli_prepare(
        $conexion,
        "SELECT archivo FROM carnets_aptitud WHERE cedula_jugador = ? ORDER BY fecha_subida DESC, id DESC LIMIT 1"
    );
}

if (!$consulta) {
    http_response_code(500);
    exit("No se pudo consultar el archivo solicitado.");
}

mysqli_stmt_bind_param($consulta, "s", $cedula);
if (!mysqli_stmt_execute($consulta)) {
    http_response_code(500);
    exit("No se pudo consultar el archivo solicitado.");
}

mysqli_stmt_bind_result($consulta, $ruta);
$encontrado = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

// Comprueba que la ruta guardada siga el patrón de archivos internos esperado.
$carpetaEsperada = $tipo === 'foto' ? 'fotos' : 'aptitud';
$patronRuta = '#^uploads/' . $carpetaEsperada . '/[a-f0-9]{32}\.(jpg|png|webp|pdf)$#';
if (!$encontrado || !is_string($ruta) || !preg_match($patronRuta, $ruta)) {
    http_response_code(404);
    exit("No se encontró el archivo solicitado.");
}

// Resuelve las rutas reales y confirma que el archivo está dentro de la carpeta permitida.
$rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
$directorioReal = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $carpetaEsperada);
$archivoReal = realpath($rutaCompleta);

if ($directorioReal === false || $archivoReal === false || strpos($archivoReal, $directorioReal . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    exit("No se encontró el archivo solicitado.");
}

// Lee el archivo y responde con el tipo MIME y cabeceras apropiadas.
$contenido = file_get_contents($archivoReal);
if ($contenido === false) {
    http_response_code(500);
    exit("No se pudo leer el archivo solicitado.");
}

$extension = strtolower(pathinfo($archivoReal, PATHINFO_EXTENSION));
$tiposMime = [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf'
];

header('Content-Type: ' . $tiposMime[$extension]);
header('Content-Length: ' . strlen($contenido));
header('Content-Disposition: inline; filename="' . ($tipo === 'foto' ? 'foto-jugador.' : 'aptitud-fisica.') . $extension . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
echo $contenido;

?>
