<?php

// Endpoint administrativo que elimina un comunicado y responde en JSON.
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/respuesta_json.php";
require_admin();

// Valida que el identificador recibido sea un entero positivo.
$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($id === false || $id === null) {
    responderJson(["error" => "El comunicado indicado no es válido."], 400);
}

// Prepara y ejecuta la eliminación usando una consulta parametrizada.
$consulta = mysqli_prepare($conexion, "DELETE FROM avisos WHERE id = ?");
if (!$consulta) {
    responderJson(["error" => "No se pudo preparar la eliminación del comunicado."], 500);
}

mysqli_stmt_bind_param($consulta, "i", $id);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    responderJson(["error" => "No se pudo eliminar el comunicado."], 500);
}

// Distingue entre eliminación exitosa y un identificador que no existe.
$eliminado = mysqli_stmt_affected_rows($consulta) > 0;
mysqli_stmt_close($consulta);
if (!$eliminado) {
    responderJson(["error" => "No se encontró el comunicado indicado."], 404);
}

responderJson(["ok" => true]);

?>
