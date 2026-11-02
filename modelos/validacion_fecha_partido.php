<?php

function fechaPartidoPermitida(string $fecha): bool
{
    $fechaValida = DateTime::createFromFormat("!Y-m-d", $fecha);
    if (!$fechaValida || $fechaValida->format("Y-m-d") !== $fecha) {
        return false;
    }

    return $fecha >= date("Y") . "-01-01";
}

?>
