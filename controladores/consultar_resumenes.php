<?php

// Expone en JSON los indicadores generales que utilizan los paneles de la liga.
require_once __DIR__ . '/../modelos/conexion.php';

// Declara el formato de respuesta para que el cliente pueda procesar JSON.
header("Content-Type: application/json; charset=utf-8");

// Ejecuta una consulta que debe devolver una fila y centraliza el error JSON.
function obtenerUnaFila($conexion, $sql) {
    $resultado = mysqli_query($conexion, $sql);

    if (!$resultado) {
        http_response_code(500);
        echo json_encode(["error" => "No se pudieron consultar los resúmenes."]);
        exit;
    }

    return mysqli_fetch_assoc($resultado);
}

// Cuenta clubes, jugadores, categorías y equipos activos en categorías.
$clubes = obtenerUnaFila($conexion, "SELECT COUNT(*) AS total FROM clubes");
$jugadores = obtenerUnaFila($conexion, "SELECT COUNT(*) AS total FROM jugadores");
$categorias = obtenerUnaFila(
    $conexion,
    "SELECT
        COUNT(*) AS total,
        COUNT(DISTINCT CASE WHEN conteos.jugadores > 0 THEN conteos.id END) AS activas,
        COALESCE(MAX(conteos.jugadores), 0) AS mayor_cantidad
    FROM (
        SELECT categorias.id, COUNT(jugadores.cedula) AS jugadores
        FROM categorias
        LEFT JOIN jugadores ON jugadores.categoria_id = categorias.id
        GROUP BY categorias.id
    ) AS conteos"
);
// Resume el estado de los partidos y las tarjetas por tipo.
$partidos = obtenerUnaFila(
    $conexion,
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN estado = 'sin_fecha' THEN 1 ELSE 0 END), 0) AS sin_fecha,
        COALESCE(SUM(CASE WHEN estado = 'programado' THEN 1 ELSE 0 END), 0) AS programados,
        COALESCE(SUM(CASE WHEN estado = 'jugado' THEN 1 ELSE 0 END), 0) AS jugados,
        COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END), 0) AS pendientes,
        COALESCE(SUM(CASE WHEN estado = 'suspendido' THEN 1 ELSE 0 END), 0) AS suspendidos
    FROM partidos"
);
$tarjetas = obtenerUnaFila(
    $conexion,
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN tipo = 'Amarilla' THEN 1 ELSE 0 END), 0) AS amarillas,
        COALESCE(SUM(CASE WHEN tipo = 'Roja' THEN 1 ELSE 0 END), 0) AS rojas
    FROM tarjetas"
);

// Busca el próximo partido desde la fecha actual.
$sql_proximo = "
    SELECT
        partidos.fecha,
        categorias.nombre AS categoria,
        clubes_local.nombre AS local,
        clubes_visitante.nombre AS visitante
    FROM partidos
    INNER JOIN categorias ON categorias.id = partidos.categoria_id
    INNER JOIN clubes AS clubes_local ON clubes_local.id = partidos.club_local
    INNER JOIN clubes AS clubes_visitante ON clubes_visitante.id = partidos.club_visitante
    WHERE partidos.fecha >= CURDATE()
      AND partidos.estado IN ('programado', 'pendiente')
    ORDER BY partidos.fecha ASC
    LIMIT 1
";

// Devuelve error JSON si no se pudo consultar el próximo encuentro.
$resultado_proximo = mysqli_query($conexion, $sql_proximo);

if (!$resultado_proximo) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo consultar el próximo partido."]);
    exit;
}

$proximo = mysqli_fetch_assoc($resultado_proximo);

// Agrupa las métricas por sección con tipos numéricos y datos del próximo partido.
echo json_encode([
    "clubes" => [
        "total" => (int) $clubes["total"],
        "jugadores" => (int) $jugadores["total"],
        "proximo" => $proximo
    ],
    "jugadores" => [
        "total" => (int) $jugadores["total"],
        "categorias" => (int) $categorias["total"],
        "clubes" => (int) $clubes["total"]
    ],
    "categorias" => [
        "total" => (int) $categorias["total"],
        "activas" => (int) $categorias["activas"],
        "mayor_cantidad" => (int) $categorias["mayor_cantidad"]
    ],
    "partidos" => [
        "total" => (int) $partidos["total"],
        "sin_fecha" => (int) $partidos["sin_fecha"],
        "programados" => (int) $partidos["programados"],
        "pendientes" => (int) $partidos["pendientes"],
        "jugados" => (int) $partidos["jugados"],
        "suspendidos" => (int) $partidos["suspendidos"],
        "proximo" => $proximo
    ],
    "tarjetas" => [
        "total" => (int) $tarjetas["total"],
        "amarillas" => (int) $tarjetas["amarillas"],
        "rojas" => (int) $tarjetas["rojas"]
    ]
], JSON_UNESCAPED_UNICODE);

?>
