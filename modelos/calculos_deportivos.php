<?php

function generarVelocidadMaximaGolSimulada(string $resultado): ?int
{
    if (!preg_match('/^\s*([0-9]{1,3})\s*-\s*([0-9]{1,3})\s*$/D', $resultado, $marcador)) {
        return null;
    }

    if ((int) $marcador[1] + (int) $marcador[2] === 0) {
        return null;
    }

    return random_int(25, 65);
}

?>
