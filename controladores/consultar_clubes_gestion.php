<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_admin();
header("Content-Type: application/json; charset=utf-8");

$resultado = mysqli_query($conexion, "SELECT id, nombre, ciudad FROM clubes ORDER BY nombre");
if (!$resultado) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudieron consultar los clubes."], JSON_UNESCAPED_UNICODE);
    exit;
}

$clubes = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $clubes[] = [
        "id" => (int) $fila["id"],
        "nombre" => $fila["nombre"],
        "ciudad" => $fila["ciudad"]
    ];
}

echo json_encode($clubes, JSON_UNESCAPED_UNICODE);

?>
