<?php

// Registra una tarjeta roja para el jugador y la fecha indicados.
require_once __DIR__ . '/../modelos/conexion.php';
require_admin();

// Recupera los campos enviados por el formulario.
$jugador_cedula = trim($_POST['jugador_cedula'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');

// Evita crear una sanción si faltan el jugador o la fecha.
if ($jugador_cedula == '' || $fecha == '') {

    echo "
    <script>
        alert('Faltan datos por completar.');
        window.location.href = 'index.php';
    </script>
    ";

    exit;
}

// Define el tipo que identifica la sanción como tarjeta roja.
$tipo = "Roja";

// Inserta la tarjeta y presenta el resultado de la operación.
$sql = "
INSERT INTO tarjetas
(jugador_cedula, tipo, fecha)
VALUES
('$jugador_cedula', '$tipo', '$fecha')
";

if (mysqli_query($conexion, $sql)) {

    echo "
    <script>
        alert('Tarjeta roja registrada correctamente');
        window.location.href = 'index.php';
    </script>
    ";

} else {

    echo "
    <script>
        alert('Error al registrar la tarjeta: " . mysqli_error($conexion) . "');
        window.location.href = 'index.php';
    </script>
    ";

}

?>