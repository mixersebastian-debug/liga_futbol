<?php

require_once __DIR__ . "/../modelos/conexion.php";
current_club_id();

$clubId = filter_var(
    $_GET["club_id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);
if ($clubId === false || $clubId === null) {
    http_response_code(400);
    exit("Club no válido.");
}

$consulta = mysqli_prepare($conexion, "SELECT logo FROM clubes WHERE id = ? LIMIT 1");
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo consultar el escudo.");
}
mysqli_stmt_bind_param($consulta, "i", $clubId);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar el escudo.");
}
mysqli_stmt_bind_result($consulta, $ruta);
$encontrado = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);

if (
    !$encontrado
    || !is_string($ruta)
    || !preg_match('~^uploads/escudos/[a-f0-9]{32}\.(jpg|png|webp)$~D', $ruta)
) {
    http_response_code(404);
    exit("Este club no tiene un escudo disponible.");
}

$directorioReal = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "escudos");
$archivoReal = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta));
if (
    $directorioReal === false
    || $archivoReal === false
    || strpos($archivoReal, $directorioReal . DIRECTORY_SEPARATOR) !== 0
) {
    http_response_code(404);
    exit("No se encontró el escudo.");
}

$contenido = file_get_contents($archivoReal);
if ($contenido === false) {
    http_response_code(500);
    exit("No se pudo leer el escudo.");
}

$mimePorExtension = [
    "jpg" => "image/jpeg",
    "png" => "image/png",
    "webp" => "image/webp"
];
$extension = strtolower(pathinfo($archivoReal, PATHINFO_EXTENSION));
header("Content-Type: " . $mimePorExtension[$extension]);
header("Content-Length: " . strlen($contenido));
header("Content-Disposition: inline; filename=\"escudo-club." . $extension . "\"");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store");
echo $contenido;

?>
