<?php

require_once __DIR__ . '/../modelos/conexion.php';
require_admin();
header('Content-Type: application/json; charset=utf-8');

$categoriasOficiales = ['2013', '2014', '2015', '2016', '2017', '2018', '2019', 'Femenino'];

function responderCategorias(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultado = mysqli_query($conexion, 'SELECT id, nombre FROM categorias ORDER BY nombre');
    if (!$resultado) {
        error_log('No se pudieron consultar categorías: ' . mysqli_error($conexion));
        responderCategorias(['error' => 'No se pudieron consultar las categorías.'], 500);
    }

    $categorias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = [
            'id' => (int) $fila['id'],
            'nombre' => $fila['nombre'],
            'oficial' => in_array($fila['nombre'], $categoriasOficiales, true)
        ];
    }
    responderCategorias(['categorias' => $categorias]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    responderCategorias(['error' => 'Método no permitido.'], 405);
}

$accion = $_POST['accion'] ?? '';
if (!is_string($accion)) {
    responderCategorias(['error' => 'La acción solicitada no es válida.'], 400);
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($accion === 'crear' || $accion === 'renombrar') {
    $nombre = $_POST['nombre'] ?? '';
    if (!is_string($nombre)) {
        responderCategorias(['error' => 'El nombre de la categoría no es válido.'], 400);
    }
    $nombre = trim($nombre);
    if (preg_match('/^.{1,50}$/usD', $nombre) !== 1) {
        responderCategorias(['error' => 'El nombre debe tener entre 1 y 50 caracteres.'], 400);
    }

    $buscar = mysqli_prepare(
        $conexion,
        'SELECT id FROM categorias WHERE LOWER(TRIM(nombre)) = LOWER(?) LIMIT 1'
    );
    if (!$buscar) {
        error_log('No se pudo validar el nombre de categoría: ' . mysqli_error($conexion));
        responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
    }
    mysqli_stmt_bind_param($buscar, 's', $nombre);
    if (!mysqli_stmt_execute($buscar)) {
        error_log('No se pudo validar el nombre de categoría: ' . mysqli_stmt_error($buscar));
        mysqli_stmt_close($buscar);
        responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
    }
    mysqli_stmt_bind_result($buscar, $categoriaExistenteId);
    $hayCoincidencia = mysqli_stmt_fetch($buscar);
    mysqli_stmt_close($buscar);

    if ($accion === 'crear' && $hayCoincidencia) {
        responderCategorias(['error' => 'Ya existe una categoría con ese nombre.'], 409);
    }
    if ($accion === 'renombrar') {
        if ($id === false || $id === null) {
            responderCategorias(['error' => 'Selecciona una categoría válida.'], 400);
        }
        if ($hayCoincidencia && (int) $categoriaExistenteId !== $id) {
            responderCategorias(['error' => 'Ya existe una categoría con ese nombre.'], 409);
        }

        $consulta = mysqli_prepare($conexion, 'SELECT nombre FROM categorias WHERE id = ? LIMIT 1');
        if (!$consulta) {
            error_log('No se pudo validar la categoría: ' . mysqli_error($conexion));
            responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
        }
        mysqli_stmt_bind_param($consulta, 'i', $id);
        if (!mysqli_stmt_execute($consulta)) {
            error_log('No se pudo validar la categoría: ' . mysqli_stmt_error($consulta));
            mysqli_stmt_close($consulta);
            responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
        }
        mysqli_stmt_bind_result($consulta, $nombreActual);
        $encontrada = mysqli_stmt_fetch($consulta);
        mysqli_stmt_close($consulta);
        if (!$encontrada) {
            responderCategorias(['error' => 'No se encontró la categoría.'], 404);
        }
        if (in_array($nombreActual, $categoriasOficiales, true) && $nombre !== $nombreActual) {
            responderCategorias(['error' => 'Las categorías requeridas por la rúbrica no se pueden renombrar.'], 409);
        }

        $guardar = mysqli_prepare($conexion, 'UPDATE categorias SET nombre = ? WHERE id = ?');
        if (!$guardar) {
            error_log('No se pudo preparar la actualización de categoría: ' . mysqli_error($conexion));
            responderCategorias(['error' => 'No se pudo actualizar la categoría.'], 500);
        }
        mysqli_stmt_bind_param($guardar, 'si', $nombre, $id);
    } else {
        $guardar = mysqli_prepare($conexion, 'INSERT INTO categorias (nombre) VALUES (?)');
        if (!$guardar) {
            error_log('No se pudo preparar la creación de categoría: ' . mysqli_error($conexion));
            responderCategorias(['error' => 'No se pudo crear la categoría.'], 500);
        }
        mysqli_stmt_bind_param($guardar, 's', $nombre);
    }

    if (!mysqli_stmt_execute($guardar)) {
        error_log('No se pudo guardar la categoría: ' . mysqli_stmt_error($guardar));
        mysqli_stmt_close($guardar);
        responderCategorias(['error' => 'No se pudo guardar la categoría.'], 500);
    }
    mysqli_stmt_close($guardar);
    responderCategorias(['mensaje' => $accion === 'crear' ? 'Categoría creada.' : 'Categoría actualizada.']);
}

if ($accion === 'eliminar') {
    if ($id === false || $id === null) {
        responderCategorias(['error' => 'Selecciona una categoría válida.'], 400);
    }

    $consulta = mysqli_prepare(
        $conexion,
        'SELECT categorias.nombre,
            (SELECT COUNT(*) FROM jugadores WHERE jugadores.categoria_id = categorias.id) AS jugadores,
            (SELECT COUNT(*) FROM partidos WHERE partidos.categoria_id = categorias.id) AS partidos
         FROM categorias
         WHERE categorias.id = ?
         LIMIT 1'
    );
    if (!$consulta) {
        error_log('No se pudo validar la eliminación de categoría: ' . mysqli_error($conexion));
        responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
    }
    mysqli_stmt_bind_param($consulta, 'i', $id);
    if (!mysqli_stmt_execute($consulta)) {
        error_log('No se pudo validar la eliminación de categoría: ' . mysqli_stmt_error($consulta));
        mysqli_stmt_close($consulta);
        responderCategorias(['error' => 'No se pudo validar la categoría.'], 500);
    }
    mysqli_stmt_bind_result($consulta, $nombre, $cantidadJugadores, $cantidadPartidos);
    $encontrada = mysqli_stmt_fetch($consulta);
    mysqli_stmt_close($consulta);
    if (!$encontrada) {
        responderCategorias(['error' => 'No se encontró la categoría.'], 404);
    }
    if (in_array($nombre, $categoriasOficiales, true)) {
        responderCategorias(['error' => 'No se pueden eliminar las categorías requeridas por la rúbrica.'], 409);
    }
    if ((int) $cantidadJugadores > 0 || (int) $cantidadPartidos > 0) {
        responderCategorias(['error' => 'No se puede eliminar una categoría que tenga jugadores o partidos asociados.'], 409);
    }

    $eliminar = mysqli_prepare($conexion, 'DELETE FROM categorias WHERE id = ?');
    if (!$eliminar) {
        error_log('No se pudo preparar la eliminación de categoría: ' . mysqli_error($conexion));
        responderCategorias(['error' => 'No se pudo eliminar la categoría.'], 500);
    }
    mysqli_stmt_bind_param($eliminar, 'i', $id);
    if (!mysqli_stmt_execute($eliminar)) {
        error_log('No se pudo eliminar la categoría: ' . mysqli_stmt_error($eliminar));
        mysqli_stmt_close($eliminar);
        responderCategorias(['error' => 'No se pudo eliminar la categoría.'], 500);
    }
    mysqli_stmt_close($eliminar);
    responderCategorias(['mensaje' => 'Categoría eliminada.']);
}

responderCategorias(['error' => 'La acción solicitada no es válida.'], 400);
