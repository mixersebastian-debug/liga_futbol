<?php

// Comprueba el partido seleccionado y guarda el marcador recibido.
require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . "/../modelos/validacion_fecha_partido.php";
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

if (
    $partido_id === false ||
    $partido_id === null ||
    $partido_id <= 0 ||
    !preg_match('/^[0-9]{1,3}\s*-\s*[0-9]{1,3}$/D', $resultado)
) {
    volverConMensaje(
        'Debes seleccionar un partido e introducir su resultado.'
    );
}

if ($fechaIngresada !== "") {
    if (!fechaPartidoPermitida($fechaIngresada)) {
        volverConMensaje("La fecha del partido debe ser del año actual o de un año posterior.");
    }
}

$sql_comprobar = "
    SELECT fecha
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
mysqli_stmt_bind_result($stmt_comprobar, $fechaActual);
$existe = mysqli_stmt_fetch($stmt_comprobar);
mysqli_stmt_close($stmt_comprobar);

if (!$existe) {
    volverConMensaje('El partido seleccionado no existe.');
}

$fechaPartido = $fechaIngresada !== "" ? $fechaIngresada : $fechaActual;
if ($fechaPartido === null) {
    volverConMensaje('Indica la fecha real del partido antes de registrar su resultado.');
}
if (!fechaPartidoPermitida($fechaPartido)) {
    volverConMensaje('La fecha del partido debe ser del año actual o de un año posterior.');
}

// Actualizar el resultado
$sql_actualizar = "
    UPDATE partidos
    SET fecha = ?, resultado = ?, estado = 'jugado'
    WHERE id = ?
";

$stmt_actualizar = mysqli_prepare($conexion, $sql_actualizar);

if (!$stmt_actualizar) {
    error_log('Error al preparar la actualización del resultado.');
    volverConMensaje('No se pudo preparar la actualización.');
}

mysqli_stmt_bind_param(
    $stmt_actualizar,
    "ssi",
    $fechaPartido,
    $resultado,
    $partido_id
);

if (mysqli_stmt_execute($stmt_actualizar)) {

    mysqli_stmt_close($stmt_actualizar);
    mysqli_close($conexion);

    volverConMensaje('Resultado registrado correctamente.');

} else {

    error_log(
        'Error al actualizar el resultado: ' .
        mysqli_stmt_error($stmt_actualizar)
    );

    mysqli_stmt_close($stmt_actualizar);
    mysqli_close($conexion);

    volverConMensaje('No se pudo actualizar el resultado.');
}
?>