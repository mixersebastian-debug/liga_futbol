<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/validacion_fecha_partido.php";
require_once __DIR__ . "/../modelos/calculos_deportivos.php";
require_once __DIR__ . "/../modelos/goleadores_partido.php";
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
$copa = $_POST["copa"] ?? "";
$estado = $_POST["estado"] ?? "";
$fecha = trim($_POST["fecha"] ?? "");
$resultado = trim($_POST["resultado"] ?? "");
$velocidadMaximaSimulada = null;
$goleadoresLocal = [];
$goleadoresVisitante = [];
$asistenciasLocal = [];
$asistenciasVisitante = [];
$copasPermitidas = ["oro", "plata", "bronce"];
$estadosPermitidos = ["sin_fecha", "programado", "jugado", "pendiente", "suspendido"];

if (
    $clubLocal === false || $clubVisitante === false || $categoriaId === false
    || $clubLocal === null || $clubVisitante === null || $categoriaId === null
    || !in_array($copa, $copasPermitidas, true)
    || !in_array($estado, $estadosPermitidos, true)
    || ($estado === "sin_fecha" && $fecha !== "")
    || ($estado !== "sin_fecha" && $fecha === "")
    || ($estado === "jugado" && !preg_match('/^([0-9]{1,3})\s*-\s*([0-9]{1,3})$/D', $resultado, $marcador))
    || ($estado !== "jugado" && $resultado !== "")
) {
    volverConMensajePartido("Completa los datos del partido correctamente.");
}

if ($estado === "jugado") {
    $goleadoresLocal = normalizarGoleadores($_POST["goleadores_local"] ?? [], (int) $marcador[1]);
    $goleadoresVisitante = normalizarGoleadores($_POST["goleadores_visitante"] ?? [], (int) $marcador[2]);
    $asistenciasLocal = normalizarAsistencias($_POST["asistencias_local"] ?? [], (int) $marcador[1]);
    $asistenciasVisitante = normalizarAsistencias($_POST["asistencias_visitante"] ?? [], (int) $marcador[2]);
    if ($goleadoresLocal === null || $goleadoresVisitante === null) {
        volverConMensajePartido(
            "Asigna exactamente los goles indicados en el marcador a cada equipo. "
            . "Las asistencias se cargan por separado y pueden corresponder a otros jugadores."
        );
    }
    if ($asistenciasLocal === null || $asistenciasVisitante === null) {
        $equipoAsistencias = $asistenciasLocal === null ? "local" : "visitante";
        volverConMensajePartido(
            "Revisa las asistencias del equipo " . $equipoAsistencias
            . ": la cantidad no puede superar los goles de ese equipo y cada asistencia debe tener un jugador seleccionado."
        );
    }
    $velocidadMaximaSimulada = generarVelocidadMaximaGolSimulada($resultado);
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

if ($estado === "jugado") {
    $goleadoresLocalesValidos = validarGoleadoresDelEquipo($conexion, $goleadoresLocal, $clubLocal, $categoriaId);
    $goleadoresVisitantesValidos = validarGoleadoresDelEquipo($conexion, $goleadoresVisitante, $clubVisitante, $categoriaId);
    $asistentesLocalesValidos = validarGoleadoresDelEquipo($conexion, $asistenciasLocal, $clubLocal, $categoriaId);
    $asistentesVisitantesValidos = validarGoleadoresDelEquipo($conexion, $asistenciasVisitante, $clubVisitante, $categoriaId);
    if (
        $goleadoresLocalesValidos === null || $goleadoresVisitantesValidos === null
        || $asistentesLocalesValidos === null || $asistentesVisitantesValidos === null
    ) {
        http_response_code(500);
        exit("No se pudieron validar los jugadores del partido.");
    }
    if (
        !$goleadoresLocalesValidos || !$goleadoresVisitantesValidos
        || !$asistentesLocalesValidos || !$asistentesVisitantesValidos
    ) {
        volverConMensajePartido("Cada goleador y asistente debe pertenecer al club y categoría del partido.");
    }
}

$fechaPartido = $fecha === "" ? null : $fecha;
$resultadoPartido = $estado === "jugado" ? $resultado : null;
if (!mysqli_begin_transaction($conexion)) {
    http_response_code(500);
    exit("No se pudo iniciar el registro del partido.");
}

$insertar = mysqli_prepare(
    $conexion,
    "INSERT INTO partidos (club_local, club_visitante, categoria_id, copa, fecha, estado, resultado, velocidad_maxima_balon_kmh)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$insertar) {
    mysqli_rollback($conexion);
    http_response_code(500);
    exit("No se pudo preparar el registro del partido.");
}

mysqli_stmt_bind_param(
    $insertar,
    "iiissssi",
    $clubLocal,
    $clubVisitante,
    $categoriaId,
    $copa,
    $fechaPartido,
    $estado,
    $resultadoPartido,
    $velocidadMaximaSimulada
);
if (!mysqli_stmt_execute($insertar)) {
    error_log("Error al registrar partido: " . mysqli_stmt_error($insertar));
    mysqli_stmt_close($insertar);
    mysqli_rollback($conexion);
    http_response_code(500);
    exit("No se pudo registrar el partido.");
}

$partidoId = mysqli_insert_id($conexion);
mysqli_stmt_close($insertar);
if (
    $estado === "jugado"
    && (
        !guardarGoleadoresPartido($conexion, $partidoId, $goleadoresLocal, $goleadoresVisitante, "goles_partido")
        || !guardarGoleadoresPartido($conexion, $partidoId, $asistenciasLocal, $asistenciasVisitante, "asistencias_partido")
    )
) {
    error_log("Error al guardar goles o asistencias del partido " . $partidoId . ": " . mysqli_error($conexion));
    mysqli_rollback($conexion);
    http_response_code(500);
    exit("No se pudieron guardar los goles y las asistencias del partido.");
}

if (!mysqli_commit($conexion)) {
    mysqli_rollback($conexion);
    http_response_code(500);
    exit("No se pudo confirmar el registro del partido.");
}

mysqli_close($conexion);
volverConMensajePartido("Partido creado correctamente.");

?>
