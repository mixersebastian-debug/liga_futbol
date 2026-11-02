<?php

// Envía una respuesta JSON con código HTTP y termina el procesamiento de la petición.
function responderJson(array $datos, int $codigo = 200): void
{
    // Establece el estado y el tipo de contenido de la respuesta.
    http_response_code($codigo);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

?>
