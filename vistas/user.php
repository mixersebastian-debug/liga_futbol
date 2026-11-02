<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>DT Corp - Liga de Fútbol</title>

<style>

/* Estilos globales de la vista de clubes y su navegación. */
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#080b10;
    color:#f5f7fa;
    font-size:16px;
}

header{
    background:#10151d;
    color:white;
    text-align:center;
    padding:12px;
    border-bottom:2px solid #d5a83b;
}

header h1{
    margin:0;
    line-height:0;
}

nav{
    background:#0c1118;
    text-align:center;
    padding:10px;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;
}

nav button{
    height:42px;
    padding:10px 15px;
    margin:5px;
    border:1px solid #283342;
    border-radius:5px;
    cursor:pointer;
    font-size:15px;
    background:#171e28;
    color:#f5f7fa;
}

nav button:hover{
    background:#4b91df;
    color:#08111c;
}

.controles-sesion{
    display:flex;
    gap:8px;
    margin:5px;
    align-items:center;
}

.controles-sesion button,
.controles-sesion a{
    display:inline-flex;
    box-sizing:border-box;
    align-items:center;
    justify-content:center;
    height:42px;
    padding:10px 15px;
    border:1px solid #283342;
    border-radius:5px;
    background:#171e28;
    color:#f5f7fa;
    font:15px Arial,sans-serif;
    text-decoration:none;
    cursor:pointer;
}

.controles-sesion button:hover,
.controles-sesion a:hover{
    background:#4b91df;
    color:#08111c;
}

main{
    width:85%;
    margin:auto;
    padding:20px;
}

.card{
    background:#10151d;
    border-radius:8px;
    padding:20px;
    border:1px solid #283342;
    box-shadow:0 0 14px rgba(0,0,0,.4);
    min-height:300px;
}

/* Mantiene la misma distribución de acciones y contenido que el panel admin. */
.seccion-layout{
    display:grid;
    grid-template-columns:minmax(220px, .75fr) minmax(0, 2fr);
    gap:24px;
    align-items:start;
}

.seccion-acciones{
    padding:18px;
    background:#0b1017;
    border:1px solid #283342;
    border-radius:6px;
}

.seccion-acciones h3,
.seccion-resumen h3{
    margin:0 0 14px;
}

.seccion-acciones ul{
    margin:0;
    padding-left:22px;
}

.seccion-acciones li{
    padding:5px 0;
}

.seccion-resumen{
    min-width:0;
}

.panel-consulta{
    min-width:0;
    padding:16px;
    background:#0b1017;
    border:1px solid #283342;
    border-top:3px solid #4b91df;
    border-radius:6px;
}

.panel-consulta + .panel-consulta{
    margin-top:16px;
}

.panel-consulta > :first-child{
    margin-top:0;
}

.panel-consulta > :last-child{
    margin-bottom:0;
}

/* Tablas y publicaciones para la consulta de información de la liga. */
.tabla-posiciones-contenedor{
    overflow-x:auto;
}

.publicacion-aviso{
    margin:12px 0;
    padding:14px;
    background:#0b1017;
    border:1px solid #283342;
    border-left:3px solid #d5a83b;
    border-radius:5px;
}

.publicacion-aviso h3{
    margin-top:0;
}

.publicacion-aviso p{
    overflow-wrap:anywhere;
}

.tabla-posiciones-filtros{
    display:flex;
    flex-wrap:wrap;
    gap:16px;
    margin:16px 0 20px;
}

.tabla-posiciones-filtros label{
    display:flex;
    flex:1 1 320px;
    align-items:center;
    gap:10px;
    max-width:540px;
}

.tabla-posiciones-filtros select{
    flex:1;
    min-width:0;
    width:auto;
}

.tabla-posiciones{
    width:100%;
    min-width:760px;
    border-collapse:collapse;
    background:#10151d;
    color:#f5f7fa;
}

.tabla-posiciones th{
    padding:12px 10px;
    background:#171e28;
    border:1px solid #283342;
    border-bottom:2px solid #d5a83b;
    text-align:left;
}

.tabla-posiciones td{
    padding:12px 10px;
    border:1px solid #283342;
    text-align:left;
}

.tabla-posiciones .escudo-posiciones{
    width:48px;
    height:48px;
    object-fit:contain;
    vertical-align:middle;
}

.tabla-posiciones tbody tr:nth-child(even){
    background:#0b1017;
}

footer{
    background:#10151d;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:20px;
    border-top:1px solid #d5a83b;
}

.logo-encabezado{
    width:250px;
    height:120px;
    object-fit:contain;
}

ul{
    line-height:40px;
}

button{
    padding:10px 15px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:15px;
    background:#4b91df;
    color:#08111c;
}

button:hover{
    background:#70a9e8;
}

input,
select{
    padding:10px;
    width:90%;
    max-width:400px;
    border:1px solid #283342;
    border-radius:5px;
    font-size:15px;
    box-sizing:border-box;
    background:#0b1017;
    color:#f5f7fa;
}

input::placeholder{
    color:#9aa8b8;
}

/* Ajustes del modo claro para panel, botones, formularios y tablas. */
body.modo-claro{
    background:#f2f5f9;
    color:#17212f;
}

.modo-claro header,
.modo-claro nav,
.modo-claro footer{
    background:#ffffff;
    color:#17212f;
}

.modo-claro header{
    background:#10151d;
    color:#ffffff;
}

.modo-claro header,
.modo-claro footer{
    border-color:#8a5a00;
}

.modo-claro nav button,
.modo-claro .controles-sesion button,
.modo-claro .controles-sesion a{
    background:#e4ebf3;
    border-color:#52657a;
    color:#17212f;
    font-weight:600;
    font-size:15px;
    height:42px;
}

.modo-claro nav button:hover,
.modo-claro .controles-sesion button:hover,
.modo-claro .controles-sesion a:hover{
    background:#1d4e89;
    color:#ffffff;
}

.modo-claro .card{
    background:#ffffff;
    border-color:#8b9bad;
    box-shadow:0 0 14px rgba(23,33,47,.16);
}

.modo-claro .tabla-posiciones{
    background:#ffffff;
    color:#17212f;
}

.modo-claro .tabla-posiciones th{
    background:#dce6f0;
    border-color:#8b9bad;
    border-bottom-color:#8a5a00;
    color:#17212f;
}

.modo-claro .tabla-posiciones td{
    border-color:#8b9bad;
}

.modo-claro .tabla-posiciones tbody tr:nth-child(even){
    background:#eaf0f6;
}

.modo-claro .publicacion-aviso{
    background:#ffffff;
    border-color:#8b9bad;
    color:#17212f;
}

.modo-claro .seccion-acciones,
.modo-claro .panel-consulta{
    background:#f7f9fc;
    border-color:#8b9bad;
}

.modo-claro .panel-consulta{
    border-top-color:#1d4e89;
}

.modo-claro button{
    background:#1d4e89;
    color:#ffffff;
    font-size:18px;
    font-weight:600;
}

.modo-claro button:hover{
    background:#163b68;
}

.modo-claro input,
.modo-claro select{
    background:#ffffff;
    border-color:#52657a;
    color:#17212f;
    font-size:18px;
}

.modo-claro input::placeholder{
    color:#46566b;
    opacity:1;
}

.modo-claro label,
.modo-claro strong{
    color:#17212f;
}

.modo-claro p,
.modo-claro li,
.modo-claro h2{
    font-size:18px;
}

@media (max-width:760px){
    main{
        width:auto;
        padding:14px;
    }

    .seccion-layout{
        grid-template-columns:1fr;
    }
}

</style>

</head>

<body>

<!-- Cabecera de la aplicación para cuentas con perfil de club. -->
<header>

<h1><img class="logo-encabezado" src="publico/img/logo-yell.png" alt="YELL Industries"></h1>

</header>

<!-- Navegación a las vistas disponibles y control de sesión. -->
<nav>

<button onclick="mostrarInicio()">Inicio</button>

<button onclick="mostrarJugadoresClub()">Jugadores</button>

<button onclick="mostrarCategorias()">Categorías</button>

<button onclick="mostrarPartidos()">Partidos</button>

<button onclick="mostrarTarjetas()">Tarjetas</button>

<button onclick="mostrarAvisos()">Avisos</button>

<div class="controles-sesion">
    <button type="button" id="botonTema" onclick="alternarModoClaro()">Modo claro</button>
    <a href="cerrar_sesion.php" onclick="return confirmarCierreSesion()">Cerrar sesión</a>
</div>

</nav>

<main>

<!-- Contenedor que JavaScript actualiza según la sección elegida. -->
<div class="card" id="contenido">

<h2>Tabla de posiciones</h2>
<p>Clasificación por categoría calculada a partir de los partidos jugados.</p>
<div id="tablaPosiciones">Cargando tabla de posiciones...</div>

</div>

</main>

<footer>

<p>YELL INDUSTRIES © 2026</p>

</footer>

<script>

function confirmarCierreSesion(){
    return window.confirm("¿Realmente quieres cerrar sesión?");
}

// Aplica al panel el tema almacenado en el navegador y actualiza el botón.
function aplicarTema(){
    const modoClaro = localStorage.getItem("ligaModoClaro") === "true";
    document.body.classList.toggle("modo-claro", modoClaro);
    document.getElementById("botonTema").textContent =
        modoClaro ? "Modo oscuro" : "Modo claro";
}

// Alterna la preferencia visual y guarda la selección localmente.
function alternarModoClaro(){
    const activarModoClaro = !document.body.classList.contains("modo-claro");
    localStorage.setItem("ligaModoClaro", activarModoClaro ? "true" : "false");
    aplicarTema();
}

aplicarTema();

// Recupera la tabla de posiciones usando los filtros actualmente elegidos.
function cargarTablaPosiciones(){
    const contenedor = document.getElementById("tablaPosiciones");
    const parametros = new URLSearchParams();
    const filtroCategoria = contenedor.querySelector("#filtroCategoriaPosiciones");
    const filtroClub = contenedor.querySelector("#filtroClubPosiciones");
    const categoria = filtroCategoria ? filtroCategoria.value : "";
    const club = filtroClub ? filtroClub.value : "";

    if(categoria){
        parametros.set("categoria_id", categoria);
    }
    if(club){
        parametros.set("club_id", club);
    }
    const consulta = parametros.toString();

    fetch(`consultar_posiciones.php${consulta ? `?${consulta}` : ""}`)
    .then(response => {
        if(!response.ok){
            throw new Error("No se pudo consultar la tabla de posiciones.");
        }
        return response.text();
    })
    .then(html => {
        contenedor.innerHTML = html;
    })
    .catch(error => {
        console.error(error);
        contenedor.textContent = "No se pudo cargar la tabla de posiciones.";
    });
}

cargarTablaPosiciones();

// Restaura la clasificación como vista principal del panel de clubes.
function mostrarInicio(){

document.getElementById("contenido").innerHTML=`

<h2>Tabla de posiciones</h2>
<p>Clasificación por categoría calculada a partir de los partidos jugados.</p>
<div id="tablaPosiciones">Cargando tabla de posiciones...</div>

`;

cargarTablaPosiciones();

}

function mostrarJugadoresClub(){
    document.getElementById("contenido").innerHTML = `
        <h2>Jugadores de la liga</h2>
        <div class="seccion-layout">
            <section class="seccion-acciones">
                <h3>Seleccionar plantilla</h3>
                <p>Consulta los jugadores de tu club o la información de solo lectura de otros clubes.</p>
                <label for="clubConsulta">Club:</label>
                <select id="clubConsulta" onchange="cargarCategoriasClub()">
                    <option value="">Cargando clubes...</option>
                </select>
                <br><br>
                <label for="categoriaConsulta">Categoría:</label>
                <select id="categoriaConsulta" disabled onchange="consultarPlantillaClub()">
                    <option value="">Selecciona un club primero</option>
                </select>
                <br><br>
                <button type="button" onclick="imprimirPlantillaClub()">Imprimir lista y formulario</button>
            </section>
            <aside class="seccion-resumen">
                <section class="panel-consulta">
                    <h3>Plantilla y aptitud física</h3>
                    <div id="plantillaClub">Selecciona un club y una categoría.</div>
                </section>
            </aside>
        </div>
    `;

    fetch("consultar_clubes_opciones.php")
    .then(response => {
        if(!response.ok) throw new Error("No se pudieron cargar los clubes.");
        return response.text();
    })
    .then(opciones => {
        const selector = document.getElementById("clubConsulta");
        if(!selector) return;
        selector.innerHTML = `<option value="">Selecciona un club</option>${opciones}`;
        selector.value = String(<?php echo (int) $club_id_sesion; ?>);
        cargarCategoriasClub();
    })
    .catch(error => {
        console.error(error);
        const selector = document.getElementById("clubConsulta");
        if(selector) selector.innerHTML = `<option value="">No se pudieron cargar los clubes</option>`;
    });
}

function cargarCategoriasClub(){
    const clubSelector = document.getElementById("clubConsulta");
    const categoriaSelector = document.getElementById("categoriaConsulta");
    const clubId = clubSelector ? clubSelector.value : "";
    if(!clubId || !categoriaSelector) return;

    categoriaSelector.disabled = true;
    categoriaSelector.innerHTML = `<option value="">Cargando categorías...</option>`;
    fetch(`consultar_categorias.php?club_id=${encodeURIComponent(clubId)}`)
    .then(response => {
        if(!response.ok) throw new Error("No se pudieron cargar las categorías.");
        return response.text();
    })
    .then(opciones => {
        if(!document.getElementById("categoriaConsulta")) return;
        const opcionesParseadas = document.createElement("select");
        opcionesParseadas.innerHTML = opciones;
        categoriaSelector.innerHTML = `<option value="">Selecciona una categoría</option>${opciones}`;
        categoriaSelector.disabled = !Array.from(opcionesParseadas.options).some(opcion => opcion.value !== "");
        if(!categoriaSelector.disabled) {
            const primeraOpcion = categoriaSelector.options[1];
            if(primeraOpcion) {
                categoriaSelector.value = primeraOpcion.value;
                consultarPlantillaClub();
            }
        } else {
            document.getElementById("plantillaClub").textContent = "Este club aún no tiene jugadores en categorías.";
        }
    })
    .catch(error => {
        console.error(error);
        categoriaSelector.innerHTML = `<option value="">No se pudieron cargar las categorías</option>`;
        document.getElementById("plantillaClub").textContent = error.message;
    });
}

function consultarPlantillaClub(){
    const clubId = document.getElementById("clubConsulta")?.value;
    const categoriaId = document.getElementById("categoriaConsulta")?.value;
    const contenedor = document.getElementById("plantillaClub");
    if(!clubId || !categoriaId || !contenedor) return;

    const parametros = new URLSearchParams({club_id: clubId, categoria_id: categoriaId});
    cargarPublicaciones(
        `consultar_jugadores_categoria.php?${parametros.toString()}`,
        "plantillaClub",
        "No se pudo cargar la plantilla."
    );
}

function imprimirPlantillaClub(){
    const clubId = document.getElementById("clubConsulta")?.value;
    const categoriaId = document.getElementById("categoriaConsulta")?.value;
    if(!clubId || !categoriaId) {
        alert("Selecciona un club y una categoría para imprimir.");
        return;
    }
    const parametros = new URLSearchParams({club_id: clubId, categoria_id: categoriaId});
    window.open(`imprimir_lista_jugadores.php?${parametros.toString()}`, "_blank", "noopener");
}

function subirAptitudClub(evento, formulario){
    evento.preventDefault();
    const estado = formulario.querySelector(".estado-subida-aptitud");
    estado.textContent = "Subiendo carnet...";
    const datos = new FormData(formulario);
    fetch("guardar_aptitud_jugador.php", {method:"POST", body:datos})
    .then(async response => {
        const datosRespuesta = await response.json();
        if(!response.ok) throw new Error(datosRespuesta.error || "No se pudo cargar el carnet.");
        return datosRespuesta;
    })
    .then(respuesta => {
        estado.textContent = respuesta.mensaje;
        setTimeout(consultarPlantillaClub, 1000);
    })
    .catch(error => {
        console.error(error);
        estado.textContent = error.message;
    });
    return false;
}


// Muestra las categorías disponibles en la navegación del panel.
function mostrarCategorias(){

document.getElementById("contenido").innerHTML=`

<h2>Categorías</h2>
<div class="seccion-layout">
    <section class="seccion-acciones">
        <h3>Jugadores por categoría</h3>
        <p>Consulta los jugadores de las categorías de tu club.</p>
        <label for="categoriaClub">Categoría:</label>
        <select id="categoriaClub" onchange="cargarJugadoresCategoria()">
            <option value="">Cargando categorías...</option>
        </select>
    </section>
    <aside class="seccion-resumen">
        <section class="panel-consulta">
            <h3>Plantilla</h3>
            <div id="jugadoresCategoria">Selecciona una categoría para consultar sus jugadores.</div>
        </section>
    </aside>
</div>

`;

fetch("consultar_categorias.php")
.then(response => {
    if(!response.ok) throw new Error("No se pudieron cargar las categorías.");
    return response.text();
})
.then(opciones => {
    const selector = document.getElementById("categoriaClub");
    if(!selector) return;
    selector.innerHTML = `<option value="">Selecciona una categoría</option>${opciones}`;
    if(opciones.includes("Este club aún no tiene categorías")) {
        selector.disabled = true;
    }
})
.catch(error => {
    console.error(error);
    const selector = document.getElementById("categoriaClub");
    if(selector) selector.innerHTML = `<option value="">No se pudieron cargar las categorías</option>`;
});

}

function cargarJugadoresCategoria(){
    const selector = document.getElementById("categoriaClub");
    const categoriaId = selector ? selector.value : "";
    if(!categoriaId) {
        document.getElementById("jugadoresCategoria").textContent = "";
        return;
    }
    cargarPublicaciones(
        `consultar_jugadores_categoria.php?categoria_id=${encodeURIComponent(categoriaId)}`,
        "jugadoresCategoria",
        "No se pudieron cargar los jugadores de esta categoría."
    );
}


// Presenta las opciones de consulta relacionadas con el fixture.
function mostrarPartidos(){

document.getElementById("contenido").innerHTML=`

<h2>Fixture</h2>
<div class="seccion-layout">
    <section class="seccion-acciones">
        <h3>Consultar partidos</h3>
        <p>Partidos en los que participa tu club.</p>
        <ul>
            <li><button type="button" onclick="cargarFixture('')">Todos</button></li>
            <li><button type="button" onclick="cargarFixture('pendientes')">Pendientes</button></li>
            <li><button type="button" onclick="cargarFixture('jugados')">Jugados</button></li>
        </ul>
    </section>
    <aside class="seccion-resumen">
        <section class="panel-consulta">
            <h3>Fixture del club</h3>
            <div id="listaFixture">Selecciona un estado para consultar el fixture.</div>
        </section>
    </aside>
</div>

`;

}

function cargarFixture(estado){
    const parametros = new URLSearchParams();
    if(estado) parametros.set("estado", estado);
    const query = parametros.toString();
    cargarPublicaciones(
        `consultar_fixture.php${query ? `?${query}` : ""}`,
        "listaFixture",
        "No se pudo cargar el fixture."
    );
}

// Presenta las opciones disponibles para consultar sanciones.
function mostrarTarjetas(){

document.getElementById("contenido").innerHTML=`

<h2>Sistema de Tarjetas</h2>
<div class="seccion-layout">
    <section class="seccion-acciones">
        <h3>Consultas disponibles</h3>
        <p>Sanciones asociadas a los jugadores de tu club.</p>
        <ul>
            <li><button type="button" onclick="cargarTarjetas(false)">Consultar sanciones</button></li>
            <li><button type="button" onclick="cargarTarjetas(true)">Ver acumulación de tarjetas</button></li>
        </ul>
    </section>
    <aside class="seccion-resumen">
        <section class="panel-consulta">
            <h3>Tarjetas del club</h3>
            <div id="listaTarjetas">Selecciona una consulta.</div>
        </section>
    </aside>
</div>

`;

}

function cargarTarjetas(acumulacion){
    cargarPublicaciones(
        acumulacion ? "consultar_acumulacion_tarjetas.php" : "consultar_tarjetas.php",
        "listaTarjetas",
        "No se pudieron cargar las tarjetas."
    );
}

// Muestra comunicados y boletines publicados para consulta.
function mostrarFichas_Medicas(){
    document.getElementById("contenido").innerHTML = `
        <h2>Avisos y boletines</h2>
        <div class="seccion-layout">
            <section class="seccion-acciones">
                <h3>Publicaciones</h3>
                <p>Consulta los comunicados y boletines disponibles para los clubes.</p>
            </section>
            <aside class="seccion-resumen">
                <section class="panel-consulta">
                    <h3>Comunicados</h3>
                    <div id="listaAvisos">Cargando comunicados...</div>
                </section>
                <section class="panel-consulta">
                    <h3>Boletines de sanciones</h3>
                    <div id="listaBoletines">Cargando boletines...</div>
                </section>
            </aside>
        </div>
    `;
    cargarAvisosPublicados();
    cargarBoletinesPublicados();
}

// Usa la misma vista de publicaciones para el acceso de avisos.
function mostrarAvisos(){
    mostrarFichas_Medicas();
}

// Carga la lista de comunicados en el contenedor indicado.
function cargarAvisosPublicados(){
    cargarPublicaciones("consultar_avisos.php", "listaAvisos", "No se pudieron cargar los comunicados.");
}

// Carga la lista de boletines en el contenedor indicado.
function cargarBoletinesPublicados(){
    cargarPublicaciones("consultar_boletines.php", "listaBoletines", "No se pudieron cargar los boletines.");
}

// Recupera una publicación, la muestra o presenta el error correspondiente.
function cargarPublicaciones(url, contenedorId, mensajeError){
    const contenedor = document.getElementById(contenedorId);
    if(!contenedor) return;
    fetch(url)
    .then(response => {
        if(!response.ok) throw new Error(mensajeError);
        return response.text();
    })
    .then(html => {
        contenedor.innerHTML = html;
    })
    .catch(error => {
        console.error(error);
        contenedor.textContent = error.message;
    });
}

</script>

</body>
</html> 