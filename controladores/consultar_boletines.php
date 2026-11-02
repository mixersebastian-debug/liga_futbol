<?php

// Obtiene los boletines junto con el nombre de la cuenta que los publicó.
require_once __DIR__ . "/../modelos/conexion.php";

$resultado = mysqli_query(
    $conexion,
    "SELECT boletines.id, boletines.titulo, boletines.descripcion,
            boletines.fecha_publicacion, usuarios.usuario AS autor
     FROM boletines
     INNER JOIN usuarios ON usuarios.id = boletines.usuario_id
     ORDER BY boletines.fecha_publicacion DESC, boletines.id DESC"
);

if (!$resultado) {
    http_response_code(500);
    exit("No se pudieron consultar los boletines.");
}

// Escapa texto antes de incorporarlo al marcado HTML.
function escaparBoletin($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

// Muestra un mensaje informativo cuando no hay boletines.
if (mysqli_num_rows($resultado) === 0) {
    echo "<p>Todavía no hay boletines publicados.</p>";
    exit();
}

// Renderiza cada publicación, el enlace al PDF y la opción administrativa.
while ($boletin = mysqli_fetch_assoc($resultado)) {
    $id = (int) $boletin["id"];
    echo '<article class="publicacion-aviso">';
    echo '<h4>' . escaparBoletin($boletin["titulo"]) . '</h4>';
    if (!empty($boletin["descripcion"])) {
        echo '<p>' . nl2br(escaparBoletin($boletin["descripcion"])) . '</p>';
    }
    echo '<small>Publicado: ' . escaparBoletin($boletin["fecha_publicacion"]) . ' · ' . escaparBoletin($boletin["autor"]) . '</small>';
    echo '<p><a href="servir_boletin.php?id=' . $id . '" target="_blank" rel="noopener">Ver boletín PDF</a></p>';

    if (($_SESSION["tipo"] ?? "") === "Administrador") {
        echo '<form data-eliminar-boletin method="post" action="eliminar_boletin.php">';
        echo '<input type="hidden" name="id" value="' . $id . '">';
        echo '<button type="submit">Eliminar</button>';
        echo '</form>';
    }

    echo '</article>';
}

?>
