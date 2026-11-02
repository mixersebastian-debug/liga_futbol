<?php

// Busca un boletín por identificador y transmite su PDF al navegador.
require_once __DIR__ . "/../modelos/conexion.php";

// Exige un identificador entero positivo.
$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("El boletín indicado no es válido.");
}

// Obtiene el título y la ruta del documento mediante una consulta parametrizada.
$consulta = mysqli_prepare($conexion, "SELECT titulo, archivo_pdf FROM boletines WHERE id = ? LIMIT 1");
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo consultar el boletín.");
}

mysqli_stmt_bind_param($consulta, "i", $id);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar el boletín.");
}

mysqli_stmt_bind_result($consulta, $titulo, $ruta);
$encontrado = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);
if (!$encontrado || !preg_match('#^uploads/boletines/[a-f0-9]{32}\.pdf$#', $ruta)) {
    http_response_code(404);
    exit("No se encontró el boletín.");
}

// Comprueba el patrón de ruta y verifica que el archivo esté en el directorio previsto.
$directorio = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "boletines");
$archivo = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta));
if ($directorio === false || $archivo === false || strpos($archivo, $directorio . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    exit("No se encontró el archivo del boletín.");
}

// Lee el PDF y lo envía como contenido en línea con cabeceras de seguridad.
$contenido = file_get_contents($archivo);
if ($contenido === false) {
    http_response_code(500);
    exit("No se pudo leer el boletín.");
}

header("Content-Type: application/pdf");
header("Content-Length: " . strlen($contenido));
header("Content-Disposition: inline; filename=\"boletin-" . $id . ".pdf\"");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store");
echo $contenido;

?>
