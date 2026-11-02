<?php

require_once __DIR__ . "/../modelos/conexion.php";
current_club_id();

$club_id = filter_var($_GET["club_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$categoria_id = filter_var($_GET["categoria_id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if ($club_id === false || $categoria_id === false || $club_id === null || $categoria_id === null) {
    http_response_code(400);
    exit("Selecciona un club y una categoría válidos.");
}

$consultaClubCategoria = mysqli_prepare(
    $conexion,
    "SELECT clubes.nombre, categorias.nombre, clubes.logo
     FROM clubes
     INNER JOIN jugadores ON jugadores.club_id = clubes.id
     INNER JOIN categorias ON categorias.id = jugadores.categoria_id
     WHERE clubes.id = ? AND categorias.id = ?
     LIMIT 1"
);
if (!$consultaClubCategoria) {
    http_response_code(500);
    exit("No se pudo consultar el club y la categoría.");
}
mysqli_stmt_bind_param($consultaClubCategoria, "ii", $club_id, $categoria_id);
if (!mysqli_stmt_execute($consultaClubCategoria)) {
    mysqli_stmt_close($consultaClubCategoria);
    http_response_code(500);
    exit("No se pudo consultar el club y la categoría.");
}
mysqli_stmt_bind_result($consultaClubCategoria, $nombreClub, $nombreCategoria, $logoClub);
$existeClubCategoria = mysqli_stmt_fetch($consultaClubCategoria);
mysqli_stmt_close($consultaClubCategoria);
if (!$existeClubCategoria) {
    http_response_code(404);
    exit("No se encontró la plantilla de esa categoría para el club.");
}

$consultaJugadores = mysqli_prepare(
    $conexion,
    "SELECT jugadores.cedula, jugadores.nombre, jugadores.apellido,
            jugadores.foto, carnets_aptitud.archivo AS archivo_aptitud,
            carnets_aptitud.fecha_vencimiento
     FROM jugadores
     LEFT JOIN (
         SELECT cedula_jugador, MAX(id) AS carnet_id
         FROM carnets_aptitud
         GROUP BY cedula_jugador
     ) AS ultimo_carnet ON ultimo_carnet.cedula_jugador = jugadores.cedula
     LEFT JOIN carnets_aptitud ON carnets_aptitud.id = ultimo_carnet.carnet_id
     WHERE jugadores.club_id = ? AND jugadores.categoria_id = ?
     ORDER BY jugadores.apellido, jugadores.nombre"
);
if (!$consultaJugadores) {
    http_response_code(500);
    exit("No se pudo preparar la lista de jugadores.");
}
mysqli_stmt_bind_param($consultaJugadores, "ii", $club_id, $categoria_id);
if (!mysqli_stmt_execute($consultaJugadores)) {
    mysqli_stmt_close($consultaJugadores);
    http_response_code(500);
    exit("No se pudo consultar la lista de jugadores.");
}
$resultadoJugadores = mysqli_stmt_get_result($consultaJugadores);
$jugadores = mysqli_fetch_all($resultadoJugadores, MYSQLI_ASSOC);
mysqli_stmt_close($consultaJugadores);

$titulares = in_array($nombreCategoria, ["2013", "2014", "2015", "2016"], true) ? 9 : 11;
$suplentes = $titulares === 9 ? 0 : 6;

function escaparImpresion($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function imprimirCasillerosJugadores(int $cantidad, array $jugadores): void
{
    for ($i = 1; $i <= $cantidad; $i++) {
        echo '<tr><td>' . $i . '</td><td><select aria-label="Jugador ' . $i . '">';
        echo '<option value="">Seleccionar jugador</option>';
        foreach ($jugadores as $jugador) {
            $nombre = $jugador["apellido"] . ", " . $jugador["nombre"];
            echo '<option value="' . escaparImpresion($nombre) . '">' . escaparImpresion($nombre) . '</option>';
        }
        echo '</select></td><td class="firma-jugador"></td></tr>';
    }
}

$logoImpresion = !empty($logoClub) ? escaparImpresion($logoClub) : "publico/img/logo-yell.png";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantel y formulario de partido - <?php echo escaparImpresion($nombreClub); ?></title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;padding:24px;background:#eef1f5;color:#17212f;font:14px Arial,sans-serif}
        .acciones{max-width:900px;margin:0 auto 16px;text-align:right}
        button{padding:10px 16px;border:0;border-radius:4px;background:#1d4e89;color:#fff;font-size:15px;cursor:pointer}
        .hoja{width:210mm;min-height:297mm;margin:0 auto 20px;padding:14mm;background:#fff;box-shadow:0 2px 12px #aab3bf}
        .encabezado{display:flex;align-items:center;gap:18px;padding-bottom:14px;border-bottom:3px solid #d5a83b}
        .logo{width:84px;height:84px;object-fit:contain}
        h1{margin:0 0 6px;font-size:22px}
        h2{margin:18px 0 10px;font-size:16px}
        .datos-partido{display:grid;grid-template-columns:repeat(2,1fr);gap:10px 22px;margin-top:16px}
        .linea{display:inline-block;width:70%;min-height:28px;border-bottom:1px solid #6b7280}
        table{width:100%;border-collapse:collapse}
        th,td{padding:7px;border:1px solid #687386;text-align:left}
        .alineacion th,.alineacion td{padding:4px 6px}
        th{background:#e8edf3}
        select{width:100%;padding:5px;border:0;background:#fff;font:inherit}
        .firma-jugador{width:34%}
        .nota{margin-top:12px;color:#526174;font-size:12px}
        .reverso{page-break-before:always}
        .campo-amplio{height:110px;border:1px solid #687386;margin-bottom:12px}
        .grilla-cambios{height:100px;border:1px solid #687386}
        .firmas{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin-top:60px}
        .firma{padding-top:8px;border-top:1px solid #17212f;text-align:center}
        @page{size:A4;margin:0}
        @media print{
            body{padding:0;background:#fff}
            .acciones{display:none}
            .hoja{width:210mm;min-height:297mm;margin:0;padding:14mm;box-shadow:none;page-break-after:always}
            .hoja:last-child{page-break-after:auto}
            select{appearance:none;-webkit-appearance:none}
            *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
        }
        @media screen and (max-width:800px){
            body{padding:8px}
            .hoja{width:100%;min-height:0;padding:18px}
            .datos-partido{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <div class="acciones"><button type="button" onclick="window.print()">Imprimir / Guardar PDF</button></div>
    <section class="hoja">
        <header class="encabezado">
            <img class="logo" src="<?php echo $logoImpresion; ?>" alt="Escudo de <?php echo escaparImpresion($nombreClub); ?>">
            <div>
                <h1><?php echo escaparImpresion($nombreClub); ?></h1>
                <div>Categoría: <?php echo escaparImpresion($nombreCategoria); ?></div>
                <div>Planilla de jugadores y alineación</div>
            </div>
        </header>

        <div class="datos-partido">
            <div>Fecha: <span class="linea"></span></div>
            <div>Rival: <span class="linea"></span></div>
            <div>Técnico: <span class="linea"></span></div>
            <div>Ayudante técnico: <span class="linea"></span></div>
            <div>Kinesiólogo: <span class="linea"></span></div>
            <div>Delegado: <span class="linea"></span></div>
        </div>

        <h2>Titulares (<?php echo $titulares; ?>)</h2>
        <table class="alineacion"><thead><tr><th>#</th><th>Jugador</th><th>Firma</th></tr></thead><tbody><?php imprimirCasillerosJugadores($titulares, $jugadores); ?></tbody></table>

        <?php if ($suplentes > 0): ?>
            <h2>Suplentes (<?php echo $suplentes; ?>)</h2>
            <table class="alineacion"><thead><tr><th>#</th><th>Jugador</th><th>Firma</th></tr></thead><tbody><?php imprimirCasillerosJugadores($suplentes, $jugadores); ?></tbody></table>
            <h2>Jugadores adicionales en el entretiempo (hasta 2)</h2>
            <table class="alineacion"><thead><tr><th>#</th><th>Jugador</th><th>Firma</th></tr></thead><tbody><?php imprimirCasillerosJugadores(2, $jugadores); ?></tbody></table>
        <?php else: ?>
            <p class="nota">Categorías 2013 a 2016: cambios libres.</p>
        <?php endif; ?>
    </section>

    <section class="hoja reverso">
        <header class="encabezado">
            <img class="logo" src="<?php echo $logoImpresion; ?>" alt="Escudo de <?php echo escaparImpresion($nombreClub); ?>">
            <div><h1><?php echo escaparImpresion($nombreClub); ?></h1><div>Categoría: <?php echo escaparImpresion($nombreCategoria); ?></div></div>
        </header>
        <h2>Observaciones del árbitro</h2>
        <div class="campo-amplio"></div>
        <h2>Cambios realizados</h2>
        <div class="grilla-cambios"></div>
        <h2>Tarjetas</h2>
        <div class="grilla-cambios"></div>
        <div class="datos-partido">
            <div>Resultado: <span class="linea"></span></div>
            <div>Partido: Local / Visitante</div>
        </div>
        <div class="firmas">
            <div class="firma">Firma del delegado</div>
            <div class="firma">Firma del árbitro</div>
        </div>
    </section>

    <section class="hoja lista-plantel">
        <header class="encabezado">
            <img class="logo" src="<?php echo $logoImpresion; ?>" alt="Escudo de <?php echo escaparImpresion($nombreClub); ?>">
            <div>
                <h1><?php echo escaparImpresion($nombreClub); ?></h1>
                <div>Categoría: <?php echo escaparImpresion($nombreCategoria); ?></div>
                <div>Lista de jugadores y aptitud física</div>
            </div>
        </header>
        <h2>Plantel del club</h2>
        <table>
            <thead><tr><th>Jugador</th><th>Foto</th><th>Aptitud física</th><th>Vigencia</th></tr></thead>
            <tbody>
            <?php if (!$jugadores): ?>
                <tr><td colspan="4">No hay jugadores registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($jugadores as $jugador): ?>
                    <?php
                    $fechaVencimiento = $jugador["fecha_vencimiento"] ?? "";
                    if (empty($jugador["archivo_aptitud"])) {
                        $vigencia = "Sin carnet";
                    } elseif ($fechaVencimiento === "") {
                        $vigencia = "Sin fecha registrada";
                    } elseif ($fechaVencimiento < date("Y-m-d")) {
                        $vigencia = "Vencida: " . $fechaVencimiento;
                    } else {
                        $vigencia = "Vigente hasta " . $fechaVencimiento;
                    }
                    ?>
                    <tr>
                        <td><?php echo escaparImpresion($jugador["apellido"] . ", " . $jugador["nombre"]); ?></td>
                        <td><?php if (!empty($jugador["foto"])): ?><img src="servir_archivo_jugador.php?tipo=foto&amp;cedula=<?php echo rawurlencode($jugador["cedula"]); ?>" alt="" width="42" height="42"><?php else: ?>Sin foto<?php endif; ?></td>
                        <td><?php if (!empty($jugador["archivo_aptitud"])): ?><a href="servir_archivo_jugador.php?tipo=aptitud&amp;cedula=<?php echo rawurlencode($jugador["cedula"]); ?>">Ver carnet</a><?php else: ?>Sin carnet<?php endif; ?></td>
                        <td><?php echo escaparImpresion($vigencia); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>
</html>
