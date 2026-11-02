<?php

function guardarLogoClub(array $archivo): ?string
{
    if (($archivo["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (
        ($archivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        || !is_uploaded_file($archivo["tmp_name"] ?? "")
    ) {
        throw new RuntimeException("No se pudo recibir el escudo. Intenta subirlo nuevamente.");
    }

    if (($archivo["size"] ?? 0) <= 0 || $archivo["size"] > 5 * 1024 * 1024) {
        throw new RuntimeException("El escudo no puede superar los 5 MB.");
    }

    $tiposPermitidos = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo = $finfo->file($archivo["tmp_name"]);
    if (!isset($tiposPermitidos[$tipo])) {
        throw new RuntimeException("El escudo debe ser una imagen JPG, PNG o WebP.");
    }

    $directorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "escudos";
    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        throw new RuntimeException("No se pudo preparar el almacenamiento de los escudos.");
    }

    $nombre = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$tipo];
    $destino = $directorio . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($archivo["tmp_name"], $destino)) {
        throw new RuntimeException("No se pudo guardar el escudo.");
    }

    return "uploads/escudos/" . $nombre;
}

function eliminarLogoClub(?string $ruta): void
{
    if (
        $ruta === null
        || !preg_match('~^uploads/escudos/[a-f0-9]{32}\.(?:jpg|png|webp)$~D', $ruta)
    ) {
        return;
    }

    $rutaCompleta = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $ruta);
    if (is_file($rutaCompleta) && !unlink($rutaCompleta)) {
        error_log("No se pudo eliminar el escudo del club: " . $rutaCompleta);
    }
}

?>
