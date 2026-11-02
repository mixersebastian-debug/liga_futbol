<?php

// Actualiza los datos personales y deportivos asociados a una cédula.
require_once __DIR__ . '/../modelos/conexion.php';
require_admin();

// Recoge los campos obligatorios del formulario.
$cedula = trim($_POST['cedula'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$club_id = trim($_POST['club_id'] ?? '');
$categoria_id = trim($_POST['categoria_id'] ?? '');

// Rechaza la actualización si falta alguno de los campos requeridos.
if ($cedula == '' || $nombre == '' || $apellido == '' || $fecha_nacimiento == '' || $club_id == '' || $categoria_id == '') {

    echo "
    <script>
        alert('Faltan datos por completar. Complete todos los campos.');
        window.location.href = 'index.php';
    </script>
    ";

    exit;
}

// Modifica el registro usando una consulta preparada y valores enlazados.
$sql = mysqli_prepare(
    $conexion,
    "UPDATE jugadores
    SET nombre = ?, apellido = ?, fecha_nacimiento = ?, club_id = ?, categoria_id = ?
    WHERE cedula = ?"
);

if (!$sql) {
    die("Error al preparar la modificación del jugador: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($sql, "ssssss", $nombre, $apellido, $fecha_nacimiento, $club_id, $categoria_id, $cedula);

// Cuando no hay filas afectadas, distingue entre datos iguales y jugador inexistente.
if (mysqli_stmt_execute($sql)) {

    if (mysqli_stmt_affected_rows($sql) > 0) {
        $mensaje = 'Jugador modificado correctamente';
    } else {
        $consulta = mysqli_prepare($conexion, "SELECT cedula FROM jugadores WHERE cedula = ? LIMIT 1");

        if (!$consulta) {
            die("Error al verificar el jugador: " . mysqli_error($conexion));
        }

        mysqli_stmt_bind_param($consulta, "s", $cedula);
        mysqli_stmt_execute($consulta);
        mysqli_stmt_store_result($consulta);
        $jugadorExiste = mysqli_stmt_num_rows($consulta) > 0;
        mysqli_stmt_close($consulta);

        $mensaje = $jugadorExiste
            ? 'Jugador modificado correctamente'
            : 'No se encontró ningún jugador con esa cédula.';
    }

    echo "
    <script>
        alert(" . json_encode($mensaje) . ");
        window.location.href = 'index.php';
    </script>
    ";

} else {

    echo "
    <script>
        alert(" . json_encode("Error al modificar jugador: " . mysqli_stmt_error($sql)) . ");
        window.location.href = 'index.php';
    </script>
    ";

}

mysqli_stmt_close($sql);

?>