<?php

// Carga los controles de acceso para las páginas que necesitan consultar la base.
require_once __DIR__ . "/auth.php";

// La validación de credenciales abre la conexión antes de crear la sesión.
if (!defined("VALIDANDO_CREDENCIALES")) {
    require_login();
}

// Abre la conexión MySQL compartida por los endpoints de la aplicación.
$conexion = mysqli_connect("localhost", "root", "", "liga_futbol");

// Detiene la petición si la base de datos no está disponible.
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>