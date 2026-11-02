<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/validacion_fecha_partido.php";
require_admin();

function volverConMensajePartido(string $mensaje): void
{
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

$clubLocal = filter_var($_POST["club_local"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$clubVisitante = filter_var($_POST["club_visitante"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$categoriaId = filter_var($_POST["categoria_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$estado = $_POST["estado"] ?? "";
$fecha = trim($_POST["fecha"] ?? "");
$estadosPermitidos = ["sin_fecha", "programado", "pendiente", "suspendido"];

if (
    $clubLocal === false || $clubVisitante === false || $categoriaId === false
    || $clubLocal === null || $clubVisitante === null || $categoriaId === null
    || !in_array($estado, $estadosPermitidos, true)
    || ($estado === "sin_fecha" && $fecha !== "")
    || ($estado !== "sin_fecha" && $fecha === "")
) {
    volverConMensajePartido("Completa los datos del partido correctamente.");
}

if ($clubLocal === $clubVisitante) {
    volverConMensajePartido("El club local y el visitante deben ser diferentes.");
}

if ($fecha !== "") {
    if (!fechaPartidoPermitida($fecha)) {
        volverConMensajePartido("La fecha del partido debe ser del año actual o de un año posterior.");
    }
}

$consultaEntidades = mysqli_prepare(
    $conexion,
    "SELECT
        (SELECT COUNT(*) FROM clubes WHERE id IN (?, ?)) AS clubes_validos,
        (SELECT COUNT(*) FROM categorias WHERE id = ?) AS categoria_valida"
);
if (!$consultaEntidades) {
    http_response_code(500);
    exit("No se pudieron validar los equipos y la categoría.");
}
mysqli_stmt_bind_param($consultaEntidades, "iii", $clubLocal, $clubVisitante, $categoriaId);
if (!mysqli_stmt_execute($consultaEntidades)) {
    mysqli_stmt_close($consultaEntidades);
    http_response_code(500);
    exit("No se pudieron validar los equipos y la categoría.");
}
mysqli_stmt_bind_result($consultaEntidades, $clubesValidos, $categoriaValida);
mysqli_stmt_fetch($consultaEntidades);
mysqli_stmt_close($consultaEntidades);
if ((int) $clubesValidos !== 2 || (int) $categoriaValida !== 1) {
    volverConMensajePartido("Selecciona clubes y una categoría existentes.");
}

$fechaPartido = $fecha === "" ? null : $fecha;
$insertar = mysqli_prepare(
    $conexion,
    "INSERT INTO partidos (club_local, club_visitante, categoria_id, fecha, estado)
     VALUES (?, ?, ?, ?, ?)"
);
if (!$insertar) {
    http_response_code(500);
    exit("No se pudo preparar el registro del partido.");
}

mysqli_stmt_bind_param($insertar, "iiiss", $clubLocal, $clubVisitante, $categoriaId, $fechaPartido, $estado);
if (!mysqli_stmt_execute($insertar)) {
    error_log("Error al registrar partido: " . mysqli_stmt_error($insertar));
    mysqli_stmt_close($insertar);
    http_response_code(500);
    exit("No se pudo registrar el partido.");
}

mysqli_stmt_close($insertar);
mysqli_close($conexion);
volverConMensajePartido("Partido creado correctamente.");

?>
