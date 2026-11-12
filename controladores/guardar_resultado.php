<?php

// Comprueba el partido seleccionado y guarda el marcador recibido.
require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . "/../modelos/validacion_fecha_partido.php";
require_once __DIR__ . "/../modelos/calculos_deportivos.php";
require_once __DIR__ . "/../modelos/goleadores_partido.php";
require_admin();

// Muestra un aviso y finaliza el flujo de escritura.
function volverConMensaje($mensaje) {
    echo "<script>
        alert(" . json_encode($mensaje) . ");
        window.location.href = 'index.php';
    </script>";
    exit;
}

// Limita este endpoint a envíos POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

// Valida el identificador y lee el marcador del formulario.
$partido_id = filter_var(
    $_POST['partido_id'] ?? '',
    FILTER_VALIDATE_INT
);

$resultado = trim($_POST['resultado'] ?? '');
$fechaIngresada = trim($_POST['fecha'] ?? '');
$velocidadMaximaSimulada = null;
$goleadoresLocal = [];
$goleadoresVisitante = [];
$asistenciasLocal = [];
$asistenciasVisitante = [];

if (
    $partido_id === false ||
    $partido_id === null ||
    $partido_id <= 0 ||
    !preg_match('/^([0-9]{1,3})\s*-\s*([0-9]{1,3})$/D', $resultado)
) {
    volverConMensaje(
        'Debes seleccionar un partido e introducir su resultado.'
    );
}
$marcador = [];
preg_match('/^([0-9]{1,3})\s*-\s*([0-9]{1,3})$/D', $resultado, $marcador);
$goleadoresLocal = normalizarGoleadores($_POST["goleadores_local"] ?? [], (int) $marcador[1]);
$goleadoresVisitante = normalizarGoleadores($_POST["goleadores_visitante"] ?? [], (int) $marcador[2]);
$asistenciasLocal = normalizarAsistencias($_POST["asistencias_local"] ?? [], (int) $marcador[1]);
$asistenciasVisitante = normalizarAsistencias($_POST["asistencias_visitante"] ?? [], (int) $marcador[2]);
if ($goleadoresLocal === null || $goleadoresVisitante === null) {
    volverConMensaje(
        'Asigna exactamente los goles indicados en el marcador a cada equipo. '
        . 'Las asistencias se cargan por separado y pueden corresponder a otros jugadores.'
    );
}
if ($asistenciasLocal === null || $asistenciasVisitante === null) {
    $equipoAsistencias = $asistenciasLocal === null ? 'local' : 'visitante';
    volverConMensaje(
        'Revisa las asistencias del equipo ' . $equipoAsistencias
        . ': la cantidad no puede superar los goles de ese equipo y cada asistencia debe tener un jugador seleccionado.'
    );
}

$velocidadMaximaSimulada = generarVelocidadMaximaGolSimulada($resultado);

if ($fechaIngresada !== "") {
    if (!fechaPartidoPermitida($fechaIngresada)) {
        volverConMensaje("La fecha del partido debe ser del año actual o de un año posterior.");
    }
}

$sql_comprobar = "
    SELECT fecha, club_local, club_visitante, categoria_id
    FROM partidos
    WHERE id = ?
";

$stmt_comprobar = mysqli_prepare($conexion, $sql_comprobar);

if (!$stmt_comprobar) {
    error_log('Error al preparar la consulta del partido.');
    volverConMensaje('No se pudo comprobar el partido.');
}

mysqli_stmt_bind_param(
    $stmt_comprobar,
    "i",
    $partido_id
);

if (!mysqli_stmt_execute($stmt_comprobar)) {
    mysqli_stmt_close($stmt_comprobar);
    volverConMensaje('No se pudo comprobar el partido.');
}
$clubLocal = 0;
$clubVisitante = 0;
$categoriaId = 0;
mysqli_stmt_bind_result($stmt_comprobar, $fechaActual, $clubLocal, $clubVisitante, $categoriaId);
$existe = mysqli_stmt_fetch($stmt_comprobar);
mysqli_stmt_close($stmt_comprobar);

if (!$existe) {
    volverConMensaje('El partido seleccionado no existe.');
}

$goleadoresLocalesValidos = validarGoleadoresDelEquipo(
    $conexion,
    $goleadoresLocal,
    (int) $clubLocal,
    (int) $categoriaId
);
$goleadoresVisitantesValidos = validarGoleadoresDelEquipo(
    $conexion,
    $goleadoresVisitante,
    (int) $clubVisitante,
    (int) $categoriaId
);
$asistentesLocalesValidos = validarGoleadoresDelEquipo(
    $conexion,
    $asistenciasLocal,
    (int) $clubLocal,
    (int) $categoriaId
);
$asistentesVisitantesValidos = validarGoleadoresDelEquipo(
    $conexion,
    $asistenciasVisitante,
    (int) $clubVisitante,
    (int) $categoriaId
);
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
    volverConMensaje('Cada goleador y asistente debe pertenecer al club y categoría del partido.');
}

$fechaPartido = $fechaIngresada !== "" ? $fechaIngresada : $fechaActual;
if ($fechaPartido === null) {
    volverConMensaje('Indica la fecha real del partido antes de registrar su resultado.');
}
if (!fechaPartidoPermitida($fechaPartido)) {
    volverConMensaje('La fecha del partido debe ser del año actual o de un año posterior.');
}

if (!mysqli_begin_transaction($conexion)) {
    volverConMensaje('No se pudo iniciar el registro del resultado.');
}

$sql_actualizar = "
    UPDATE partidos
    SET fecha = ?, resultado = ?, velocidad_maxima_balon_kmh = ?, estado = 'jugado'
    WHERE id = ?
";

$stmt_actualizar = mysqli_prepare($conexion, $sql_actualizar);

if (!$stmt_actualizar) {
    error_log('Error al preparar la actualización del resultado.');
    mysqli_rollback($conexion);
    volverConMensaje('No se pudo preparar la actualización.');
}

mysqli_stmt_bind_param(
    $stmt_actualizar,
    "sssi",
    $fechaPartido,
    $resultado,
    $velocidadMaximaSimulada,
    $partido_id
);

if (mysqli_stmt_execute($stmt_actualizar)) {

    mysqli_stmt_close($stmt_actualizar);
    if (
        !guardarGoleadoresPartido($conexion, $partido_id, $goleadoresLocal, $goleadoresVisitante, "goles_partido")
        || !guardarGoleadoresPartido($conexion, $partido_id, $asistenciasLocal, $asistenciasVisitante, "asistencias_partido")
    ) {
        error_log("Error al guardar goles o asistencias del partido " . $partido_id . ": " . mysqli_error($conexion));
        mysqli_rollback($conexion);
        volverConMensaje('No se pudieron guardar los goles y las asistencias del partido.');
    }
    if (!mysqli_commit($conexion)) {
        mysqli_rollback($conexion);
        volverConMensaje('No se pudo confirmar el resultado y sus goleadores.');
    }
    mysqli_close($conexion);

    volverConMensaje('Resultado registrado correctamente.');

} else {

    error_log(
        'Error al actualizar el resultado: ' .
        mysqli_stmt_error($stmt_actualizar)
    );

    mysqli_stmt_close($stmt_actualizar);
    mysqli_rollback($conexion);
    mysqli_close($conexion);

    volverConMensaje('No se pudo actualizar el resultado.');
}
?>