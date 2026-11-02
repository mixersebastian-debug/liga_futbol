<?php

// Centraliza las comprobaciones de sesión y permisos usadas por las páginas protegidas.
function require_login(): void
{
    // Inicia la sesión solo si todavía no está activa.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Envía a la pantalla de acceso a quien no tenga una sesión autenticada.
    if (empty($_SESSION["usuario"])) {
        header("Location: login.php");
        exit();
    }
}

function require_admin(): void
{
    // Primero exige que exista una sesión válida.
    require_login();

    // Restringe esta operación al perfil de administrador.
    if (($_SESSION["tipo"] ?? "") !== "Administrador") {
        http_response_code(403);
        exit("Acceso reservado para el administrador.");
    }
}

function current_club_id(): ?int
{
    require_login();

    if (($_SESSION["tipo"] ?? "") === "Administrador") {
        return null;
    }

    if (($_SESSION["tipo"] ?? "") !== "club") {
        http_response_code(403);
        exit("La cuenta no tiene un club asociado.");
    }

    $clubId = filter_var(
        $_SESSION["club_id"] ?? null,
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    if ($clubId === false) {
        global $conexion;
        if (!isset($conexion) || !$conexion) {
            http_response_code(500);
            exit("No se pudo verificar el club de la cuenta.");
        }

        $consulta = mysqli_prepare(
            $conexion,
            "SELECT clubes.id, clubes.nombre
             FROM clubes
             INNER JOIN usuarios ON usuarios.id = clubes.usuario_id
             WHERE usuarios.usuario = ?
             LIMIT 1"
        );
        if (!$consulta) {
            http_response_code(500);
            exit("No se pudo verificar el club de la cuenta.");
        }

        mysqli_stmt_bind_param($consulta, "s", $_SESSION["usuario"]);
        if (!mysqli_stmt_execute($consulta)) {
            mysqli_stmt_close($consulta);
            http_response_code(500);
            exit("No se pudo verificar el club de la cuenta.");
        }

        mysqli_stmt_bind_result($consulta, $clubId, $clubNombre);
        $encontrado = mysqli_stmt_fetch($consulta);
        mysqli_stmt_close($consulta);
        if (!$encontrado) {
            http_response_code(403);
            exit("La cuenta no tiene un club asociado.");
        }

        $_SESSION["club_id"] = (int) $clubId;
        $_SESSION["club_nombre"] = $clubNombre;
        $clubId = (int) $clubId;
    }

    return $clubId;
}

?>
