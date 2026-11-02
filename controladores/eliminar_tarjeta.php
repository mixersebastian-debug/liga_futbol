<?php

// Elimina una tarjeta identificada desde el formulario del panel.
require_once __DIR__ . '/../modelos/conexion.php';
require_admin();

// Lee el identificador enviado por el formulario.
$id = trim($_POST['id'] ?? '');

// Rechaza la solicitud si no se proporcionó un identificador.
if ($id == '') {

    echo "
    <script>
        alert('Debe ingresar el ID de la tarjeta.');
        window.location.href = 'index.php';
    </script>
    ";

    exit;
}

// Elimina el registro y presenta un aviso según el resultado de la operación.
$sql = "
DELETE FROM tarjetas
WHERE id='$id'
";

if (mysqli_query($conexion, $sql)) {

    if (mysqli_affected_rows($conexion) > 0) {

        echo "
        <script>
            alert('Tarjeta eliminada correctamente');
            window.location.href = 'index.php';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('No se encontró ninguna tarjeta con ese ID.');
            window.location.href = 'index.php';
        </script>
        ";

    }

} else {

    echo "
    <script>
        alert('Error al eliminar la tarjeta: " . mysqli_error($conexion) . "');
        window.location.href = 'index.php';
    </script>
    ";

}

?>