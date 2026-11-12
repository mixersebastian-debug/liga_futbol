<?php

function normalizarGoleadores($entradas, int $golesEsperados): ?array
{
    if (!is_array($entradas)) {
        return $golesEsperados === 0 ? [] : null;
    }

    $goleadores = [];
    foreach ($entradas as $entrada) {
        if (!is_array($entrada)) {
            return null;
        }

        $cedula = trim((string) ($entrada["cedula"] ?? ""));
        $goles = filter_var(
            $entrada["goles"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0, "max_range" => 999]]
        );
        if ($goles === false || $goles === null) {
            return null;
        }
        if ($goles === 0) {
            continue;
        }
        if ($cedula === "") {
            return null;
        }

        $goleadores[$cedula] = ($goleadores[$cedula] ?? 0) + $goles;
    }

    if (array_sum($goleadores) !== $golesEsperados) {
        return null;
    }

    return $goleadores;
}

function normalizarAsistencias($entradas, int $golesEsperados): ?array
{
    if (!is_array($entradas)) {
        return [];
    }

    $asistencias = [];
    foreach ($entradas as $entrada) {
        if (!is_array($entrada)) {
            return null;
        }

        $cedula = trim((string) ($entrada["cedula"] ?? ""));
        $cantidad = filter_var(
            $entrada["cantidad"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0, "max_range" => 999]]
        );
        if ($cantidad === false || $cantidad === null) {
            return null;
        }
        if ($cantidad === 0) {
            continue;
        }
        if ($cedula === "") {
            return null;
        }

        $asistencias[$cedula] = ($asistencias[$cedula] ?? 0) + $cantidad;
    }

    if (array_sum($asistencias) > $golesEsperados) {
        return null;
    }

    return $asistencias;
}

function validarGoleadoresDelEquipo(
    mysqli $conexion,
    array $goleadores,
    int $clubId,
    int $categoriaId
): ?bool {
    if (!$goleadores) {
        return true;
    }

    $consulta = mysqli_prepare(
        $conexion,
        "SELECT cedula FROM jugadores WHERE cedula = ? AND club_id = ? AND categoria_id = ?"
    );
    if (!$consulta) {
        error_log("No se pudo preparar la validación de goleadores: " . mysqli_error($conexion));
        return null;
    }

    foreach (array_keys($goleadores) as $cedula) {
        mysqli_stmt_bind_param($consulta, "sii", $cedula, $clubId, $categoriaId);
        if (!mysqli_stmt_execute($consulta)) {
            error_log("No se pudo validar el goleador: " . mysqli_stmt_error($consulta));
            mysqli_stmt_close($consulta);
            return null;
        }
        mysqli_stmt_store_result($consulta);
        $encontrado = mysqli_stmt_num_rows($consulta) === 1;
        mysqli_stmt_free_result($consulta);
        if (!$encontrado) {
            mysqli_stmt_close($consulta);
            return false;
        }
    }

    mysqli_stmt_close($consulta);
    return true;
}

function guardarGoleadoresPartido(
    mysqli $conexion,
    int $partidoId,
    array $aportesLocal,
    array $aportesVisitante,
    string $tabla
): bool {
    if (!in_array($tabla, ["goles_partido", "asistencias_partido"], true)) {
        return false;
    }

    $eliminar = mysqli_prepare($conexion, "DELETE FROM " . $tabla . " WHERE partido_id = ?");
    if (!$eliminar) {
        error_log("No se pudo preparar la limpieza de aportes: " . mysqli_error($conexion));
        return false;
    }
    mysqli_stmt_bind_param($eliminar, "i", $partidoId);
    $eliminado = mysqli_stmt_execute($eliminar);
    mysqli_stmt_close($eliminar);
    if (!$eliminado) {
        error_log("No se pudieron limpiar aportes del partido " . $partidoId);
        return false;
    }

    $insertar = mysqli_prepare(
        $conexion,
        "INSERT INTO " . $tabla . " (partido_id, jugador_cedula, cantidad) VALUES (?, ?, ?)"
    );
    if (!$insertar) {
        error_log("No se pudo preparar el registro de aportes: " . mysqli_error($conexion));
        return false;
    }

    foreach ([$aportesLocal, $aportesVisitante] as $aportes) {
        foreach ($aportes as $cedula => $cantidad) {
            mysqli_stmt_bind_param($insertar, "isi", $partidoId, $cedula, $cantidad);
            if (!mysqli_stmt_execute($insertar)) {
                error_log("No se pudo registrar un aporte: " . mysqli_stmt_error($insertar));
                mysqli_stmt_close($insertar);
                return false;
            }
        }
    }

    mysqli_stmt_close($insertar);
    return true;
}

?>
