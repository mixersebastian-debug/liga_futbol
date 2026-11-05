<?php

function crearHashContrasena(string $contrasena): string
{
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    if ($hash === false) {
        throw new RuntimeException('No se pudo proteger la contraseña.');
    }

    return $hash;
}

function verificarContrasena(string $contrasena, string $hashAlmacenado): bool
{
    $informacion = password_get_info($hashAlmacenado);
    if ($informacion['algo'] === null) {
        return hash_equals($hashAlmacenado, $contrasena);
    }

    return password_verify($contrasena, $hashAlmacenado);
}

function necesitaActualizarHashContrasena(string $hashAlmacenado): bool
{
    $informacion = password_get_info($hashAlmacenado);

    return $informacion['algo'] === null
        || password_needs_rehash($hashAlmacenado, PASSWORD_DEFAULT);
}
