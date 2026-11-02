<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_admin();

function responderEliminacionClub(string $mensaje, int $codigo = 200): void
{
    http_response_code($codigo);
    echo "<script>alert(" . json_encode($mensaje) . ");window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

$id = filter_var(
    $_POST["id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);
if ($id === false || $id === null) {
    responderEliminacionClub("Selecciona un club válido.", 400);
}

$eliminar = mysqli_prepare($conexion, "DELETE FROM clubes WHERE id = ?");
if (!$eliminar) {
    http_response_code(500);
    exit("No se pudo preparar la eliminación del club.");
}

mysqli_stmt_bind_param($eliminar, "i", $id);
if (!mysqli_stmt_execute($eliminar)) {
    error_log("Error al eliminar club: " . mysqli_stmt_error($eliminar));
    mysqli_stmt_close($eliminar);
    responderEliminacionClub("No se pudo eliminar el club. Comprueba que no tenga datos relacionados.", 500);
}

$eliminado = mysqli_stmt_affected_rows($eliminar) > 0;
mysqli_stmt_close($eliminar);
mysqli_close($conexion);

if (!$eliminado) {
    responderEliminacionClub("No se encontró el club seleccionado.", 404);
}

responderEliminacionClub("Club eliminado correctamente.");

?>
