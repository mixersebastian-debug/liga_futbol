<?php

use Leewayweb\CiValidator\CiValidator;

function validarCedulaUruguaya(string $cedula): bool
{
    if (preg_match('/^[0-9]{8}$/D', $cedula) !== 1) {
        return false;
    }

    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
        if (class_exists(CiValidator::class)) {
            return (new CiValidator())->validate_ci($cedula);
        }
    }

    $pesos = [2, 9, 8, 7, 6, 3, 4];
    $suma = 0;
    for ($i = 0; $i < count($pesos); $i++) {
        $suma += ((int) $cedula[$i] * $pesos[$i]) % 10;
    }

    $digitoVerificador = (10 - ($suma % 10)) % 10;
    return $digitoVerificador === (int) $cedula[7];
}
