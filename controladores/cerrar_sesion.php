<?php

// Invalida los datos de sesión del usuario y devuelve al formulario de acceso.
session_start();
$_SESSION = [];
session_destroy();

// Evita que la página de cierre muestre contenido adicional.
header("Location: login.php");
exit();

?>
