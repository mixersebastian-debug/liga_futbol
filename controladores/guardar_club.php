<?php

// Registra un club y su usuario en una única transacción de base de datos.
require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . "/../modelos/club_logos.php";
require_admin();

// Recoge los valores del formulario y centraliza los mensajes de respuesta.
$nombre = trim($_POST['nombre'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contraseña'] ?? '');
$rutaLogo = null;

function mostrarMensaje($mensaje) {
    echo "<script>
        alert(" . json_encode($mensaje) . ");
        window.location.href = 'index.php';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

// Detiene el proceso cuando faltan datos obligatorios.
if (
    $nombre === '' ||
    $ciudad === '' ||
    $usuario === '' ||
    $contrasena === ''
) {
    mostrarMensaje('Completa todos los campos.');
}

try {
    $rutaLogo = guardarLogoClub($_FILES['logo'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
} catch (RuntimeException $error) {
    mostrarMensaje($error->getMessage());
}

// Agrupa ambos INSERT para evitar dejar creado solo el usuario o solo el club.
if (!mysqli_begin_transaction($conexion)) {
    eliminarLogoClub($rutaLogo);
    mysqli_close($conexion);
    mostrarMensaje("No se pudo iniciar el registro del club.");
}

// Crea la cuenta del club y obtiene su identificador para asociarla al equipo.
try {

    $sql_usuario = "
        INSERT INTO usuarios (usuario, contraseña, tipo)
        VALUES (?, ?, 'club')
    ";

    $stmt_usuario = mysqli_prepare($conexion, $sql_usuario);

    if (!$stmt_usuario) {
        throw new Exception('No se pudo preparar el registro del usuario.');
    }

    mysqli_stmt_bind_param(
        $stmt_usuario,
        "ss",
        $usuario,
        $contrasena
    );

    if (!mysqli_stmt_execute($stmt_usuario)) {
        throw new Exception('No se pudo registrar el usuario. Comprueba que no exista previamente.');
    }

    $usuario_id = mysqli_insert_id($conexion);

    mysqli_stmt_close($stmt_usuario);

    $sql_club = "
        INSERT INTO clubes (nombre, ciudad, usuario_id, logo)
        VALUES (?, ?, ?, ?)
    ";

    $stmt_club = mysqli_prepare($conexion, $sql_club);

    if (!$stmt_club) {
        throw new Exception('No se pudo preparar el registro del club.');
    }

    mysqli_stmt_bind_param(
        $stmt_club,
        "ssis",
        $nombre,
        $ciudad,
        $usuario_id,
        $rutaLogo
    );

    if (!mysqli_stmt_execute($stmt_club)) {
        throw new Exception('No se pudo registrar el club.');
    }

    mysqli_stmt_close($stmt_club);

    if (!mysqli_commit($conexion)) {
        throw new Exception('No se pudo confirmar el registro del club.');
    }
    mysqli_close($conexion);

    mostrarMensaje('Club y usuario registrados correctamente.');

// Revierte la transacción y registra el error si cualquier paso falla.
} catch (Throwable $e) {

    mysqli_rollback($conexion);
    eliminarLogoClub($rutaLogo);
    mysqli_close($conexion);

    error_log('Error al registrar club: ' . $e->getMessage());

    mostrarMensaje(
        'No se pudo registrar el club. Comprueba los datos e inténtalo nuevamente.'
    );

}
?>