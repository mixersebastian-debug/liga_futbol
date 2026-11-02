<?php

require_once __DIR__ . "/../modelos/auth.php";
require_login();

if (($_SESSION["tipo"] ?? "") === "Administrador") {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . "/../modelos/conexion.php";
$club_id_sesion = current_club_id();

require __DIR__ . "/../vistas/user.php";

?>
