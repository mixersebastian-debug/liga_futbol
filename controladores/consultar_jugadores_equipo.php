<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_admin();
header("Content-Type: application/json; charset=utf-8");

$clubId = filter_var($_GET["club_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$categoriaId = filter_var($_GET["categoria_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($clubId === false || $categoriaId === false || $clubId === null || $categoriaId === null) {
    http_response_code(400);
    echo json_encode(["error" => "El club o la categoría no son válidos."]);
    exit;
}

$consulta = mysqli_prepare(
    $conexion,
    "SELECT cedula, nombre, apellido
     FROM jugadores
     WHERE club_id = ? AND categoria_id = ?
     ORDER BY apellido, nombre"
);
if (!$consulta) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudieron consultar los jugadores."]);
    exit;
}

mysqli_stmt_bind_param($consulta, "ii", $clubId, $categoriaId);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    echo json_encode(["error" => "No se pudieron consultar los jugadores."]);
    exit;
}

$resultado = mysqli_stmt_get_result($consulta);
$jugadores = [];
while ($jugador = mysqli_fetch_assoc($resultado)) {
    $jugadores[] = [
        "cedula" => $jugador["cedula"],
        "nombre" => $jugador["nombre"] . " " . $jugador["apellido"]
    ];
}
mysqli_stmt_close($consulta);

echo json_encode(["jugadores" => $jugadores], JSON_UNESCAPED_UNICODE);

?>
