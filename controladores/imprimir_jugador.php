<?php

// Recupera la ficha completa de un jugador para mostrarla o imprimirla.
require_once __DIR__ . "/../modelos/conexion.php";
current_club_id();

// Valida la cédula recibida antes de consultar la base de datos.
$cedula = trim($_GET['cedula'] ?? '');

if (!preg_match('/^[0-9]{8}$/D', $cedula)) {
    http_response_code(400);
    exit("La cédula no es válida.");
}

// Obtiene datos personales, club, categoría y el carnet de aptitud más reciente.
$consulta = mysqli_prepare(
    $conexion,
    "SELECT jugadores.cedula, jugadores.nombre, jugadores.apellido,
            jugadores.fecha_nacimiento, jugadores.masa, jugadores.altura,
            jugadores.foto, clubes.nombre AS nombre_club,
            categorias.nombre AS nombre_categoria,
            carnets_aptitud.archivo AS archivo_aptitud,
            carnets_aptitud.fecha_emision, carnets_aptitud.fecha_vencimiento,
            jugadores.club_id
     FROM jugadores
     LEFT JOIN clubes ON jugadores.club_id = clubes.id
     LEFT JOIN categorias ON jugadores.categoria_id = categorias.id
     LEFT JOIN (
         SELECT cedula_jugador, MAX(id) AS carnet_id
         FROM carnets_aptitud
         GROUP BY cedula_jugador
     ) AS ultimo_carnet ON ultimo_carnet.cedula_jugador = jugadores.cedula
     LEFT JOIN carnets_aptitud ON carnets_aptitud.id = ultimo_carnet.carnet_id
     WHERE jugadores.cedula = ?
     LIMIT 1"
);

if (!$consulta) {
    http_response_code(500);
    exit("No se pudo preparar la ficha del jugador.");
}

mysqli_stmt_bind_param($consulta, "s", $cedula);
if (!mysqli_stmt_execute($consulta)) {
    mysqli_stmt_close($consulta);
    http_response_code(500);
    exit("No se pudo consultar la ficha del jugador.");
}

$resultado = mysqli_stmt_get_result($consulta);
$jugador = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($consulta);

if (!$jugador) {
    http_response_code(404);
    exit("No se encontró el jugador.");
}
// Escapa valores para mostrarlos como texto dentro de la página HTML.
function escaparFicha($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de jugador - <?php echo escaparFicha($jugador['nombre'] . ' ' . $jugador['apellido']); ?></title>
    <!-- Estilos de pantalla, diseño adaptable y reglas específicas de impresión. -->
    <style>
        body{
            margin:0;
            padding:32px;
            background:#f2f5f9;
            color:#17212f;
            font-family:Arial,sans-serif;
        }

        .ficha{
            max-width:760px;
            margin:0 auto;
            padding:28px;
            background:#fff;
            border:1px solid #8b9bad;
            border-radius:10px;
        }

        .encabezado{
            display:flex;
            align-items:center;
            gap:24px;
            padding-bottom:20px;
            border-bottom:2px solid #d5a83b;
        }

        .foto{
            width:140px;
            height:140px;
            object-fit:cover;
            border-radius:50%;
            border:2px solid #d5a83b;
        }

        h1{margin:0 0 8px}
        .cedula{color:#52657a}

        dl{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:18px;
            margin:24px 0;
        }

        dl div{
            padding:14px;
            background:#f2f5f9;
            border-radius:6px;
        }

        dt{
            margin-bottom:6px;
            color:#52657a;
            font-size:13px;
            font-weight:bold;
            text-transform:uppercase;
        }

        dd{margin:0;font-size:17px}

        .acciones{
            display:flex;
            gap:10px;
            margin-top:24px;
        }

        .acciones button,
        .acciones a{
            display:inline-flex;
            width:46px;
            height:46px;
            align-items:center;
            justify-content:center;
            padding:10px;
            border:0;
            border-radius:5px;
            background:#1d4e89;
            color:white;
            font:inherit;
            font-weight:bold;
            text-decoration:none;
            cursor:pointer;
        }

        .acciones button:hover,
        .acciones a:hover{
            background:#163b68;
        }

        .acciones svg{
            width:24px;
            height:24px;
            fill:currentColor;
        }

        @media print{
            body{padding:0;background:#fff}
            .ficha{max-width:none;border:0;border-radius:0;padding:0}
            .acciones{display:none}
            *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
        }

        @media(max-width:560px){
            body{padding:12px}
            .encabezado{align-items:flex-start;flex-direction:column}
            dl{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <!-- Ficha imprimible con identidad, datos deportivos y documentos asociados. -->
    <main class="ficha">
        <header class="encabezado">
            <?php if (!empty($jugador['foto'])): ?>
                <img
                    class="foto"
                    src="servir_archivo_jugador.php?tipo=foto&amp;cedula=<?php echo rawurlencode($jugador['cedula']); ?>"
                    alt="Foto de <?php echo escaparFicha($jugador['nombre']); ?>"
                >
            <?php endif; ?>
            <div>
                <h1><?php echo escaparFicha($jugador['nombre'] . ' ' . $jugador['apellido']); ?></h1>
                <div class="cedula">Cédula: <?php echo escaparFicha($jugador['cedula']); ?></div>
            </div>
        </header>

        <dl>
            <div><dt>Fecha de nacimiento</dt><dd><?php echo escaparFicha($jugador['fecha_nacimiento'] ?: 'Sin registrar'); ?></dd></div>
            <div><dt>Club</dt><dd><?php echo escaparFicha($jugador['nombre_club'] ?: 'Sin club'); ?></dd></div>
            <div><dt>Categoría</dt><dd><?php echo escaparFicha($jugador['nombre_categoria'] ?: 'Sin categoría'); ?></dd></div>
            <div><dt>Masa</dt><dd><?php echo escaparFicha($jugador['masa'] !== null ? $jugador['masa'] . ' kg' : 'Sin registrar'); ?></dd></div>
            <div><dt>Altura</dt><dd><?php echo escaparFicha($jugador['altura'] !== null ? $jugador['altura'] . ' m' : 'Sin registrar'); ?></dd></div>
            <div>
                <dt>Aptitud física</dt>
                <dd>
                    <?php if (!empty($jugador['archivo_aptitud'])): ?>
                        <?php if (empty($jugador['fecha_vencimiento'])): ?>
                            Vigencia no registrada
                        <?php elseif ($jugador['fecha_vencimiento'] < date('Y-m-d')): ?>
                            Vencida: <?php echo escaparFicha($jugador['fecha_vencimiento']); ?>
                        <?php else: ?>
                            Vigente hasta <?php echo escaparFicha($jugador['fecha_vencimiento']); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        Sin documento
                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <!-- Acciones disponibles fuera de la vista impresa. -->
        <div class="acciones">
            <button type="button" onclick="window.print()" aria-label="Imprimir o guardar como PDF" title="Imprimir o guardar como PDF"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 8H5a3 3 0 0 0-3 3v5h4v4h12v-4h4v-5a3 3 0 0 0-3-3Zm-3 10H8v-5h8Zm3-7a1 1 0 1 1 0-2 1 1 0 0 1 0 2ZM18 3H6v4h12Z"/></svg></button>
            <?php if (!empty($jugador['archivo_aptitud'])): ?>
                <a href="servir_archivo_jugador.php?tipo=aptitud&amp;cedula=<?php echo rawurlencode($jugador['cedula']); ?>" target="_blank" rel="noopener">Ver aptitud física</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
