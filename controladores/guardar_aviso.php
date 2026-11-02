<?php

// Valida y guarda un comunicado general enviado por un administrador.
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/respuesta_json.php";
require_admin();

// Recoge los campos y comprueba el formato estricto de fecha calendario.
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$fecha = trim($_POST["fecha"] ?? "");
$fechaParsed = DateTime::createFromFormat("!Y-m-d", $fecha);
$erroresFecha = DateTime::getLastErrors();
$fechaValida = $fechaParsed !== false
    && ($erroresFecha === false || ($erroresFecha["warning_count"] === 0 && $erroresFecha["error_count"] === 0))
    && $fechaParsed->format("Y-m-d") === $fecha;

// Rechaza datos incompletos, títulos largos o fechas inválidas.
if ($titulo === "" || mb_strlen($titulo, "UTF-8") > 100 || $descripcion === "" || !$fechaValida) {
    responderJson(["error" => "Completá un título, una descripción y una fecha válida."], 400);
}

// Inserta el aviso con parámetros para separar valores de la consulta SQL.
$consulta = mysqli_prepare($conexion, "INSERT INTO avisos (titulo, descripcion, fecha) VALUES (?, ?, ?)");
if (!$consulta) {
    responderJson(["error" => "No se pudo preparar el comunicado."], 500);
}

mysqli_stmt_bind_param($consulta, "sss", $titulo, $descripcion, $fecha);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    responderJson(["error" => "No se pudo guardar el comunicado."], 500);
}

mysqli_stmt_close($consulta);
responderJson(["ok" => true]);

?>
