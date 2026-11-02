<?php

// Valida las credenciales y dirige al usuario al panel que corresponde a su perfil.
session_start();

// Permite abrir la conexión sin exigir una sesión previa durante el acceso.
define("VALIDANDO_CREDENCIALES", true);
require_once __DIR__ . '/../modelos/conexion.php';

// Lee usuario y contraseña enviados por el formulario de acceso.
$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";

// Busca una cuenta que coincida con ambas credenciales.
$consulta = mysqli_prepare(
    $conexion,
    "SELECT usuarios.usuario, usuarios.tipo, clubes.id, clubes.nombre
     FROM usuarios
     LEFT JOIN clubes ON clubes.usuario_id = usuarios.id
     WHERE usuarios.usuario = ? AND usuarios.contraseña = ?
     LIMIT 1"
);

if (!$consulta) {
    http_response_code(500);
    exit("No se pudo validar el usuario.");
}

mysqli_stmt_bind_param($consulta, "ss", $usuario, $password);

if (!mysqli_stmt_execute($consulta)) {
    http_response_code(500);
    exit("No se pudo validar el usuario.");
}

mysqli_stmt_bind_result($consulta, $usuarioAutenticado, $tipo, $clubId, $clubNombre);

// Si la cuenta existe, renueva la sesión y guarda su perfil autenticado.
if (mysqli_stmt_fetch($consulta)) {
    if ($tipo === "club" && $clubId === null) {
        mysqli_stmt_close($consulta);
        echo "<script>
        alert('Esta cuenta no tiene un club asociado. Contacta al administrador.');
        window.location='login.php?tipo=club';
        </script>";
        exit();
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

    echo "<script>
    alert('Usuario o contraseña incorrectos');
    window.location='login.php';
    </script>";

}

?>