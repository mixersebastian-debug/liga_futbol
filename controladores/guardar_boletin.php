<?php

// Recibe un PDF de boletín, lo almacena y registra el autor y sus metadatos.
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/respuesta_json.php";
require_admin();

// Valida el título y comprueba que se haya seleccionado un archivo subido.
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$archivo = $_FILES["archivo_pdf"] ?? null;

if ($titulo === "" || mb_strlen($titulo, "UTF-8") > 150) {
    responderJson(["error" => "Ingresá un título de hasta 150 caracteres."], 400);
}

if (!is_array($archivo) || ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    responderJson(["error" => "Seleccioná el boletín en formato PDF."], 400);
}

// Comprueba tamaño, tipo MIME y firma del contenido para aceptar solo PDF.
if (($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($archivo["tmp_name"] ?? "")) {
    responderJson(["error" => "No se pudo recibir el PDF. Intentá subirlo nuevamente."], 400);
}

if (($archivo["size"] ?? 0) <= 0 || $archivo["size"] > 10 * 1024 * 1024) {
    responderJson(["error" => "El PDF no puede superar los 10 MB."], 400);
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
if ($finfo->file($archivo["tmp_name"]) !== "application/pdf") {
    responderJson(["error" => "El archivo seleccionado no es un PDF válido."], 400);
}

$pdfInicio = file_get_contents($archivo["tmp_name"], false, null, 0, 5);
if ($pdfInicio !== "%PDF-") {
    responderJson(["error" => "El contenido del archivo no corresponde a un PDF válido."], 400);
}

// Confirma que la sesión corresponde a una cuenta administradora registrada.
$consultaUsuario = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE usuario = ? AND tipo = 'Administrador' LIMIT 1");
if (!$consultaUsuario) {
    responderJson(["error" => "No se pudo validar el administrador."], 500);
}

$usuario = $_SESSION["usuario"];
mysqli_stmt_bind_param($consultaUsuario, "s", $usuario);
if (!mysqli_stmt_execute($consultaUsuario)) {
    mysqli_stmt_close($consultaUsuario);
    responderJson(["error" => "No se pudo validar el administrador."], 500);
}

mysqli_stmt_bind_result($consultaUsuario, $usuarioId);
$usuarioExiste = mysqli_stmt_fetch($consultaUsuario);
mysqli_stmt_close($consultaUsuario);
if (!$usuarioExiste) {
    responderJson(["error" => "No se encontró la cuenta administradora."], 403);
}

// Prepara el directorio, asigna un nombre aleatorio y mueve el archivo recibido.
$directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "boletines";
if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
    responderJson(["error" => "No se pudo preparar el almacenamiento de boletines."], 500);
}

$nombreArchivo = bin2hex(random_bytes(16)) . ".pdf";
$rutaRelativa = "uploads/boletines/" . $nombreArchivo;
$rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $nombreArchivo;
if (!move_uploaded_file($archivo["tmp_name"], $rutaCompleta)) {
    responderJson(["error" => "No se pudo guardar el PDF."], 500);
}

// Registra en la base la publicación, el archivo y el administrador autor.
$consulta = mysqli_prepare(
    $conexion,
    "INSERT INTO boletines (titulo, descripcion, archivo_pdf, usuario_id) VALUES (?, ?, ?, ?)"
);
if (!$consulta) {
    unlink($rutaCompleta);
    responderJson(["error" => "No se pudo preparar el registro del boletín."], 500);
}

mysqli_stmt_bind_param($consulta, "sssi", $titulo, $descripcion, $rutaRelativa, $usuarioId);
if (!mysqli_stmt_execute($consulta)) {
    $error = mysqli_stmt_error($consulta);
    mysqli_stmt_close($consulta);
    if (is_file($rutaCompleta) && !unlink($rutaCompleta)) {
        error_log("No se pudo limpiar PDF de boletín: " . $rutaCompleta);
    }
    responderJson(["error" => "No se pudo guardar el boletín: " . $error], 500);
}

mysqli_stmt_close($consulta);
responderJson(["ok" => true]);

?>
