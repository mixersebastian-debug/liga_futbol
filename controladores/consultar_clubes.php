<?php

// Consulta los clubes y sus ciudades para formar la tabla de resultados.
require_once __DIR__ . '/../modelos/conexion.php';

$sql = "SELECT id, nombre, ciudad, logo FROM clubes ORDER BY nombre";

$resultado = mysqli_query($conexion, $sql);

// Si la consulta falla, muestra el motivo y no intenta renderizar filas.
if (!$resultado) {

    echo "<p>Error al consultar los clubes: " . mysqli_error($conexion) . "</p>";
    exit;

}

// Define los estilos propios de la tabla enviada al iframe.
echo "
<style>

body {
    background-color: #10151d;
    color: #f5f7fa;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    background-color: #10151d;
    color: #f5f7fa;
}

th {
    background-color: #171e28;
    color: #f5f7fa;
    padding: 10px;
    border-bottom: 2px solid #d5a83b;
}

td {
    padding: 10px;
    text-align: center;
    border: 1px solid #283342;
}

tr:nth-child(even) {
    background-color: #0b1017;
}

</style>
";

// Crea la tabla e imprime sus encabezados.
echo "<table>";

echo "
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Ciudad</th>
    <th>Escudo</th>
</tr>
";

// Añade una fila por club obtenido de la base de datos.
while ($fila = mysqli_fetch_assoc($resultado)) {

    echo "
    <tr>
        <td>".(int) $fila['id']."</td>
        <td>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".htmlspecialchars($fila['ciudad'], ENT_QUOTES, 'UTF-8')."</td>
        <td>".(!empty($fila['logo'])
            ? "<img src='servir_logo_club.php?club_id=".(int) $fila['id']."' alt='Escudo de ".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."' width='64' height='64' style='object-fit:contain'>"
            : "Sin escudo")."</td>
    </tr>
    ";

}

echo "</table>";

?>