<?php

// Calcula la clasificación a partir de los resultados y filtros solicitados.
require_once __DIR__ . '/../modelos/conexion.php';
$club_id_usuario = current_club_id();

// Acepta únicamente identificadores numéricos y usa cero para indicar "todos".
function obtenerFiltroId($valor) {
    if (!is_string($valor) || !ctype_digit($valor)) {
        return 0;
    }

    return (int) $valor;
}

// Crea una fila de estadísticas para el par categoría-club si aún no existe.
function asegurarEquipo(&$equipos, $categoria_id, $categoria, $club_id, $club, $logo = null) {
    $clave = $categoria_id . ':' . $club_id;

    if (!isset($equipos[$clave])) {
        $equipos[$clave] = [
            'categoria_id' => (int) $categoria_id,
            'categoria' => $categoria,
            'club_id' => (int) $club_id,
            'club' => $club,
            'logo' => $logo,
            'pj' => 0,
            'pg' => 0,
            'pe' => 0,
            'pp' => 0,
            'gf' => 0,
            'gc' => 0,
            'pts' => 0
        ];
    }

    return $clave;
}

// Lee los filtros opcionales de categoría y club enviados por la interfaz.
$categoria_seleccionada = obtenerFiltroId($_GET['categoria_id'] ?? '');
$club_seleccionado = $club_id_usuario ?? obtenerFiltroId($_GET['club_id'] ?? '');

// Recupera los valores válidos de los filtros que se mostrarán al usuario.
$resultado_categorias = mysqli_query($conexion, "SELECT id, nombre FROM categorias ORDER BY nombre");
if (!$resultado_categorias) {
    http_response_code(500);
    exit("Error al consultar las categorías.");
}

$resultado_clubes = mysqli_query($conexion, "SELECT id, nombre FROM clubes ORDER BY nombre");
if (!$resultado_clubes) {
    http_response_code(500);
    exit("Error al consultar los clubes.");
}

// Inicializa los equipos que tienen jugadores registrados en la liga.
$equipos = [];

// Incluye también equipos que participan en partidos aunque no tengan plantilla.
$sql_equipos = "
    SELECT DISTINCT
        categorias.id AS categoria_id,
        categorias.nombre AS categoria,
        clubes.id AS club_id,
        clubes.nombre AS club,
        clubes.logo
    FROM jugadores
    INNER JOIN categorias ON jugadores.categoria_id = categorias.id
    INNER JOIN clubes ON jugadores.club_id = clubes.id
";

$resultado_equipos = mysqli_query($conexion, $sql_equipos);

if (!$resultado_equipos) {
    http_response_code(500);
    exit("Error al consultar los equipos: " . htmlspecialchars(mysqli_error($conexion), ENT_QUOTES, 'UTF-8'));
}

while ($fila = mysqli_fetch_assoc($resultado_equipos)) {
    asegurarEquipo(
        $equipos,
        $fila['categoria_id'],
        $fila['categoria'],
        $fila['club_id'],
        $fila['club'],
        $fila['logo']
    );
}

// Obtiene los partidos y los nombres que hacen falta para actualizar estadísticas.
$sql_partidos = "
    SELECT
        partidos.categoria_id,
        categorias.nombre AS categoria,
        partidos.club_local,
        clubes_local.nombre AS nombre_local,
        clubes_local.logo AS logo_local,
        partidos.club_visitante,
        clubes_visitante.nombre AS nombre_visitante,
        clubes_visitante.logo AS logo_visitante,
        partidos.resultado
    FROM partidos
    INNER JOIN categorias ON partidos.categoria_id = categorias.id
    INNER JOIN clubes AS clubes_local ON partidos.club_local = clubes_local.id
    INNER JOIN clubes AS clubes_visitante ON partidos.club_visitante = clubes_visitante.id
";

$resultado_partidos = mysqli_query($conexion, $sql_partidos);

if (!$resultado_partidos) {
    http_response_code(500);
    exit("Error al consultar los partidos: " . htmlspecialchars(mysqli_error($conexion), ENT_QUOTES, 'UTF-8'));
}

// Convierte cada marcador válido en partidos, goles, victorias, empates y puntos.
while ($partido = mysqli_fetch_assoc($resultado_partidos)) {
    $clave_local = asegurarEquipo(
        $equipos,
        $partido['categoria_id'],
        $partido['categoria'],
        $partido['club_local'],
        $partido['nombre_local'],
        $partido['logo_local']
    );
    $clave_visitante = asegurarEquipo(
        $equipos,
        $partido['categoria_id'],
        $partido['categoria'],
        $partido['club_visitante'],
        $partido['nombre_visitante'],
        $partido['logo_visitante']
    );

    if (!preg_match('/^\s*(\d+)\s*[-–—]\s*(\d+)\s*$/u', $partido['resultado'] ?? '', $marcador)) {
        continue;
    }

    $goles_local = (int) $marcador[1];
    $goles_visitante = (int) $marcador[2];

    $equipos[$clave_local]['pj']++;
    $equipos[$clave_local]['gf'] += $goles_local;
    $equipos[$clave_local]['gc'] += $goles_visitante;

    $equipos[$clave_visitante]['pj']++;
    $equipos[$clave_visitante]['gf'] += $goles_visitante;
    $equipos[$clave_visitante]['gc'] += $goles_local;

    if ($goles_local > $goles_visitante) {
        $equipos[$clave_local]['pg']++;
        $equipos[$clave_local]['pts'] += 3;
        $equipos[$clave_visitante]['pp']++;
    } elseif ($goles_local < $goles_visitante) {
        $equipos[$clave_visitante]['pg']++;
        $equipos[$clave_visitante]['pts'] += 3;
        $equipos[$clave_local]['pp']++;
    } else {
        $equipos[$clave_local]['pe']++;
        $equipos[$clave_visitante]['pe']++;
        $equipos[$clave_local]['pts']++;
        $equipos[$clave_visitante]['pts']++;
    }
}

// Calcula la diferencia de goles de cada equipo tras procesar los encuentros.
foreach ($equipos as &$equipo) {
    $equipo['dg'] = $equipo['gf'] - $equipo['gc'];
}
unset($equipo);

$categorias_del_club = [];
if ($club_id_usuario !== null) {
    foreach ($equipos as $equipo) {
        if ($equipo['club_id'] === $club_id_usuario) {
            $categorias_del_club[$equipo['categoria_id']] = true;
        }
    }
}

// Ordena por categoría y luego por puntos, diferencia, goles y nombre del club.
usort($equipos, function ($a, $b) {
    $comparacion = strcasecmp($a['categoria'], $b['categoria']);
    if ($comparacion !== 0) {
        return $comparacion;
    }

    foreach (['pts', 'dg', 'gf'] as $criterio) {
        if ($a[$criterio] !== $b[$criterio]) {
            return $b[$criterio] <=> $a[$criterio];
        }
    }

    return strcasecmp($a['club'], $b['club']);
});

// Escapa valores dinámicos antes de incorporarlos al HTML.
function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Construye filtros para limitar la tabla a una categoría o club.
echo '<div class="tabla-posiciones-filtros">';
echo '<label for="filtroCategoriaPosiciones">Categoría:';
echo '<select id="filtroCategoriaPosiciones" onchange="cargarTablaPosiciones()">';
echo '<option value="">Todas las categorías</option>';
while ($categoria = mysqli_fetch_assoc($resultado_categorias)) {
    $id = (int) $categoria['id'];
    if ($club_id_usuario !== null && !isset($categorias_del_club[$id])) {
        continue;
    }
    $seleccionada = $categoria_seleccionada === $id ? ' selected' : '';
    echo '<option value="' . $id . '"' . $seleccionada . '>' . escapar($categoria['nombre']) . '</option>';
}
echo '</select></label>';

if ($club_id_usuario === null) {
    echo '<label for="filtroClubPosiciones">Club:';
    echo '<select id="filtroClubPosiciones" onchange="cargarTablaPosiciones()">';
    echo '<option value="">Todos los clubes</option>';
    while ($club = mysqli_fetch_assoc($resultado_clubes)) {
        $id = (int) $club['id'];
        $seleccionado = $club_seleccionado === $id ? ' selected' : '';
        echo '<option value="' . $id . '"' . $seleccionado . '>' . escapar($club['nombre']) . '</option>';
    }
    echo '</select></label>';
} else {
    echo '<p>Club: <strong>' . escapar($_SESSION['club_nombre'] ?? '') . '</strong></p>';
}
echo '</div>';

// Presenta el encabezado y el cuerpo de la clasificación.
echo '<div class="tabla-posiciones-contenedor">';
echo '<table class="tabla-posiciones">';
echo '<thead><tr>';
echo '<th>Pos.</th><th>Categoría</th><th>Escudo</th><th>Club</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>DG</th><th>Pts</th>';
echo '</tr></thead><tbody>';

// Muestra equipos ordenados, aplica filtros y numera posiciones dentro de categoría.
if (count($equipos) === 0) {
    echo '<tr><td colspan="12">Todavía no hay equipos registrados en categorías.</td></tr>';
} else {
    $posiciones = [];
    $equipos_mostrados = 0;

    foreach ($equipos as $equipo) {
        $categoria_id = $equipo['categoria_id'];
        $posiciones[$categoria_id] = ($posiciones[$categoria_id] ?? 0) + 1;

        if (
            ($categoria_seleccionada !== 0 && $categoria_id !== $categoria_seleccionada)
            || ($club_seleccionado !== 0 && $equipo['club_id'] !== $club_seleccionado)
        ) {
            continue;
        }

        $equipos_mostrados++;
        echo '<tr>';
        echo '<td>' . $posiciones[$categoria_id] . '</td>';
        echo '<td>' . escapar($equipo['categoria']) . '</td>';
        echo '<td>' . (!empty($equipo['logo'])
            ? '<img class="escudo-posiciones" src="servir_logo_club.php?club_id=' . $equipo['club_id'] . '" alt="Escudo de ' . escapar($equipo['club']) . '">'
            : 'Sin escudo') . '</td>';
        echo '<td>' . escapar($equipo['club']) . '</td>';
        echo '<td>' . $equipo['pj'] . '</td>';
        echo '<td>' . $equipo['pg'] . '</td>';
        echo '<td>' . $equipo['pe'] . '</td>';
        echo '<td>' . $equipo['pp'] . '</td>';
        echo '<td>' . $equipo['gf'] . '</td>';
        echo '<td>' . $equipo['gc'] . '</td>';
        echo '<td>' . $equipo['dg'] . '</td>';
        echo '<td><strong>' . $equipo['pts'] . '</strong></td>';
        echo '</tr>';
    }

    if ($equipos_mostrados === 0) {
        echo '<tr><td colspan="12">No hay equipos que coincidan con los filtros seleccionados.</td></tr>';
    }
}

echo '</tbody></table></div>';

?>
