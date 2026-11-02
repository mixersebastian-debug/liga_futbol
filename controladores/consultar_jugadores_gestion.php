<?php

require_once __DIR__ . "/../modelos/conexion.php";
require_admin();
header("Content-Type: application/json; charset=utf-8");

$sql = "
    SELECT jugadores.cedula, jugadores.nombre, jugadores.apellido,
           jugadores.fecha_nacimiento, jugadores.club_id, clubes.nombre AS nombre_club,
           jugadores.categoria_id, categorias.nombre AS nombre_categoria
    FROM jugadores
    INNER JOIN clubes ON clubes.id = jugadores.club_id
    INNER JOIN categorias ON categorias.id = jugadores.categoria_id
    ORDER BY jugadores.apellido, jugadores.nombre
";
$resultado = mysqli_query($conexion, $sql);
if (!$resultado) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudieron consultar los jugadores."], JSON_UNESCAPED_UNICODE);
    exit;
}

$jugadores = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $jugadores[] = [
        "cedula" => $fila["cedula"],
        "nombre" => $fila["nombre"],
        "apellido" => $fila["apellido"],
        "fecha_nacimiento" => $fila["fecha_nacimiento"],
        "club_id" => (int) $fila["club_id"],
        "club" => $fila["nombre_club"],
        "categoria_id" => (int) $fila["categoria_id"],
        "categoria" => $fila["nombre_categoria"]
    ];
}

echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);

?>
