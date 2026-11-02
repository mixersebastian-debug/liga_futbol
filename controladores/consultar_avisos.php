<?php

// Recupera los comunicados ordenados del más reciente al más antiguo.
require_once __DIR__ . "/../modelos/conexion.php";

$resultado = mysqli_query($conexion, "SELECT id, titulo, descripcion, fecha FROM avisos ORDER BY fecha DESC, id DESC");
if (!$resultado) {
    http_response_code(500);
    exit("No se pudieron consultar los comunicados.");
}

// Convierte los valores de la base en texto seguro para insertarlos en HTML.
function escaparAviso($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

// Indica el estado vacío y finaliza si aún no hay publicaciones.
if (mysqli_num_rows($resultado) === 0) {
    echo "<p>Todavía no hay comunicados publicados.</p>";
    exit();
}

// Presenta cada comunicado y ofrece la acción de borrado solo al administrador.
while ($aviso = mysqli_fetch_assoc($resultado)) {
    echo '<article class="publicacion-aviso">';
    echo '<h4>' . escaparAviso($aviso["titulo"]) . '</h4>';
    echo '<p>' . nl2br(escaparAviso($aviso["descripcion"])) . '</p>';
    echo '<small>' . escaparAviso($aviso["fecha"]) . '</small>';

    if (($_SESSION["tipo"] ?? "") === "Administrador") {
        echo '<form data-eliminar-aviso method="post" action="eliminar_aviso.php">';
        echo '<input type="hidden" name="id" value="' . (int) $aviso["id"] . '">';
        echo '<button type="submit">Eliminar</button>';
        echo '</form>';
    }

    echo '</article>';
}

?>
