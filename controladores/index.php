<?php

require_once __DIR__ . "/../modelos/auth.php";
require_login();

if (($_SESSION["tipo"] ?? "") !== "Administrador") {
    header("Location: user.php");
    exit();
}

require __DIR__ . "/../vistas/index.php";

?>
