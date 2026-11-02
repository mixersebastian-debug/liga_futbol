<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/club_logos.php";
require_admin();

function responderModificacionClub(string $mensaje): void
{
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$nombre = trim($_POST["nombre"] ?? "");
$ciudad = trim($_POST["ciudad"] ?? "");
if ($id === false || $id === null || $nombre === "" || $ciudad === "") {
    responderModificacionClub("Completa los datos del club correctamente.");
}

$consulta = mysqli_prepare($conexion, "SELECT logo FROM clubes WHERE id = ?");
if (!$consulta) {
    http_response_code(500);
    exit("No se pudo consultar el club.");
}
mysqli_stmt_bind_param($consulta, "i", $id);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar el club.");
}
mysqli_stmt_bind_result($consulta, $logoActual);
$existe = mysqli_stmt_fetch($consulta);
mysqli_stmt_close($consulta);
if (!$existe) {
    responderModificacionClub("No se encontró ningún club con ese ID.");
}

try {
    $logoNuevo = guardarLogoClub($_FILES["logo"] ?? ["error" => UPLOAD_ERR_NO_FILE]);
} catch (RuntimeException $error) {
    responderModificacionClub($error->getMessage());
}

$eliminarLogo = isset($_POST["eliminar_logo"]);
$logoFinal = $logoNuevo ?? ($eliminarLogo ? null : $logoActual);
$actualizar = mysqli_prepare(
    $conexion,
    "UPDATE clubes SET nombre = ?, ciudad = ?, logo = ? WHERE id = ?"
);
if (!$actualizar) {
    eliminarLogoClub($logoNuevo);
    http_response_code(500);
    exit("No se pudo preparar la actualización del club.");
}

mysqli_stmt_bind_param($actualizar, "sssi", $nombre, $ciudad, $logoFinal, $id);
if (!mysqli_stmt_execute($actualizar)) {
    error_log("Error al modificar el club: " . mysqli_stmt_error($actualizar));
    mysqli_stmt_close($actualizar);
    eliminarLogoClub($logoNuevo);
    http_response_code(500);
    exit("No se pudo modificar el club.");
}
mysqli_stmt_close($actualizar);
mysqli_close($conexion);

if ($logoNuevo !== null || $eliminarLogo) {
    eliminarLogoClub($logoActual);
}

responderModificacionClub("Club modificado correctamente.");

?>