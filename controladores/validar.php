<?php

// Valida las credenciales y dirige al usuario al panel que corresponde a su perfil.
session_start();

// Permite abrir la conexión sin exigir una sesión previa durante el acceso.
define("VALIDANDO_CREDENCIALES", true);
require_once __DIR__ . '/../modelos/conexion.php';
require_once __DIR__ . '/../modelos/contrasenas.php';

// Lee usuario y contraseña enviados por el formulario de acceso.
$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
if (!is_string($password)) {
    $password = "";
}

// Obtiene la contraseña almacenada para verificarla con password_verify.
$consulta = mysqli_prepare(
    $conexion,
    "SELECT usuarios.id, usuarios.usuario, usuarios.contraseña, usuarios.tipo, clubes.id, clubes.nombre
     FROM usuarios
     LEFT JOIN clubes ON clubes.usuario_id = usuarios.id
     WHERE usuarios.usuario = ?
     LIMIT 1"
);

if (!$consulta) {
    http_response_code(500);
    exit("No se pudo validar el usuario.");
}

mysqli_stmt_bind_param($consulta, "s", $usuario);

if (!mysqli_stmt_execute($consulta)) {
    http_response_code(500);
    exit("No se pudo validar el usuario.");
}

mysqli_stmt_bind_result(
    $consulta,
    $usuarioId,
    $usuarioAutenticado,
    $hashAlmacenado,
    $tipo,
    $clubId,
    $clubNombre
);

// Si la cuenta existe, renueva la sesión y guarda su perfil autenticado.
if (mysqli_stmt_fetch($consulta) && verificarContrasena($password, $hashAlmacenado)) {
    mysqli_stmt_close($consulta);

    if ($tipo === "club" && $clubId === null) {
        echo "<script>
        alert('Esta cuenta no tiene un club asociado. Contacta al administrador.');
        window.location='login.php?tipo=club';
        </script>";
        exit();
    }

    if (necesitaActualizarHashContrasena($hashAlmacenado)) {
        try {
            $hashNuevo = crearHashContrasena($password);
        } catch (RuntimeException $error) {
            error_log('No se pudo actualizar el hash de contraseña: ' . $error->getMessage());
            http_response_code(500);
            exit("No se pudo completar el acceso. Inténtalo nuevamente.");
        }

        $actualizarHash = mysqli_prepare(
            $conexion,
            "UPDATE usuarios SET contraseña = ? WHERE id = ?"
        );
        if (!$actualizarHash) {
            error_log('No se pudo preparar la actualización del hash de contraseña: ' . mysqli_error($conexion));
            http_response_code(500);
            exit("No se pudo completar el acceso. Inténtalo nuevamente.");
        }

        mysqli_stmt_bind_param($actualizarHash, "si", $hashNuevo, $usuarioId);
        if (!mysqli_stmt_execute($actualizarHash)) {
            error_log('No se pudo actualizar el hash de contraseña: ' . mysqli_stmt_error($actualizarHash));
            mysqli_stmt_close($actualizarHash);
            http_response_code(500);
            exit("No se pudo completar el acceso. Inténtalo nuevamente.");
        }
        mysqli_stmt_close($actualizarHash);
    }

    session_regenerate_id(true);
    $_SESSION["usuario"] = $usuarioAutenticado;
    $_SESSION["tipo"] = $tipo;
    if ($tipo === "club") {
        $_SESSION["club_id"] = (int) $clubId;
        $_SESSION["club_nombre"] = $clubNombre;
    } else {
        unset($_SESSION["club_id"], $_SESSION["club_nombre"]);
    }

    if ($tipo === "Administrador") {

        header("Location: index.php");
        exit();

    } else {

        header("Location: user.php");
        exit();

    }

} else {
    mysqli_stmt_close($consulta);

    echo "<script>
    alert('Usuario o contraseña incorrectos');
    window.location='login.php';
    </script>";

}

?>