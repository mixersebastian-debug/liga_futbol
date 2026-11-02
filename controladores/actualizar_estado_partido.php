<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/validacion_fecha_partido.php";
require_admin();

function responderEstadoPartido(string $mensaje): void
{
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='consultar_fixture.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

$partidoId = filter_var($_POST["partido_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$estado = $_POST["estado"] ?? "";
$fecha = trim($_POST["fecha"] ?? "");
$estadosPermitidos = ["sin_fecha", "programado", "jugado", "pendiente", "suspendido"];

if (
    $partidoId === false || $partidoId === null
    || !in_array($estado, $estadosPermitidos, true)
    || ($estado === "sin_fecha" && $fecha !== "")
    || ($estado !== "sin_fecha" && $fecha === "")
) {
    responderEstadoPartido("Completa el estado y la fecha del partido correctamente.");
}

if ($fecha !== "") {
    if (!fechaPartidoPermitida($fecha)) {
        responderEstadoPartido("La fecha del partido debe ser del año actual o de un año posterior.");
    }
}

$consulta = mysqli_prepare($conexion, "SELECT resultado FROM partidos WHERE id = ?");
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo consultar el partido.");
}
mysqli_stmt_bind_param($consulta, "i", $partidoId);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar el partido.");
}
mysqli_stmt_bind_result($consulta, $resultadoActual);
$existe = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);
if (!$existe) {
    responderEstadoPartido("No se encontró el partido seleccionado.");
}
if ($estado === "jugado" && trim((string) $resultadoActual) === "") {
    responderEstadoPartido("Registra el resultado antes de marcar el partido como jugado.");
}

$fechaPartido = $fecha === "" ? null : $fecha;
$actualizar = mysqli_prepare(
    $conexion,
    "UPDATE partidos
     SET estado = ?, fecha = ?, resultado = CASE WHEN ? = 'jugado' THEN resultado ELSE NULL END
     WHERE id = ?"
);
if (!$actualizar) {
    http_response_code(500);
    exit("No se pudo preparar la actualización del partido.");
}
mysqli_stmt_bind_param($actualizar, "sssi", $estado, $fechaPartido, $estado, $partidoId);
if (!mysqli_stmt_execute($actualizar)) {
    error_log("Error al actualizar el estado del partido: " . mysqli_stmt_error($actualizar));
    mysqli_stmt_close($actualizar);
    http_response_code(500);
    exit("No se pudo actualizar el estado del partido.");
}

mysqli_stmt_close($actualizar);
mysqli_close($conexion);
responderEstadoPartido("Estado del partido actualizado correctamente.");

?>
