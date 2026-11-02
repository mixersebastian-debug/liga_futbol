
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>DT Corp - Liga de Fútbol</title>

<style>

/* Estilos globales del tema oscuro y la cabecera del panel. */
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

.logo-encabezado{
    width:250px;
    height:120px;
    object-fit:contain;
}

header p{
    margin:0;
}

/* Barra de navegación, botones y controles de sesión. */
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

/* Ancho del contenido y apariencia de las tarjetas del panel. */
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

/* Distribución de las secciones administrativas y resúmenes. */
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

.seccion-acciones h3{
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

.seccion-acciones textarea{
    box-sizing:border-box;
    width:100%;
    max-width:100%;
    padding:10px;
    border:1px solid #283342;
    border-radius:5px;
    background:#0b1017;
    color:#f5f7fa;
    font:15px Arial,sans-serif;
}

.publicacion-aviso{
    margin:12px 0;
    padding:14px;
    background:#0b1017;
    border:1px solid #283342;
    border-left:3px solid #d5a83b;
    border-radius:5px;
}

.publicacion-aviso h4{
    margin:0 0 8px;
}

.publicacion-aviso p{
    white-space:normal;
    overflow-wrap:anywhere;
}

.publicacion-aviso form{
    margin-top:10px;
}

/* Diseño de los indicadores resumidos y sus variantes de color. */
.resumen-tarjetas{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:12px;
}

.resumen-tarjeta{
    min-width:0;
    padding:16px;
    background:#0b1017;
    border:1px solid #283342;
    border-top:3px solid #4b91df;
    border-radius:6px;
}

.resumen-tarjeta:nth-child(2){
    border-top-color:#d5a83b;
}

.resumen-tarjeta:nth-child(3){
    border-top-color:#c45c66;
    grid-column:1 / -1;
}

.resumen-tarjeta h3{
    margin:0 0 10px;
    color:#aabbd0;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.06em;
}

.resumen-valor{
    display:block;
    margin-bottom:8px;
    color:#f5f7fa;
    font-size:30px;
    font-weight:700;
}

.resumen-tarjeta p{
    margin:0;
    color:#c2cbd6;
    font-size:14px;
}

/* Ajustes de distribución para resoluciones pequeñas. */
@media (max-width:760px){
    .seccion-layout{
        grid-template-columns:1fr;
    }

    .seccion-acciones ul{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
        gap:8px 18px;
    }
}

@media (max-width:480px){
    .resumen-tarjetas{
        grid-template-columns:1fr;
    }

    .resumen-tarjeta:nth-child(3){
        grid-column:auto;
    }
}

.estado-partido-form{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    align-items:center;
    gap:8px;
    min-width:260px;
}

.estado-partido-form select,
.estado-partido-form input[type="date"]{
    width:auto;
    min-width:145px;
}

.escudo-club-form{
    display:block;
    margin:10px 0;
}

.buscador-club-autocompletar{
    position:relative;
    width:90%;
    max-width:400px;
}

.buscador-club-autocompletar input{
    width:100%;
}

.sugerencias-club{
    position:absolute;
    z-index:20;
    top:100%;
    right:0;
    left:0;
    max-height:240px;
    overflow-y:auto;
    background:#10151d;
    border:1px solid #52657a;
    border-radius:0 0 6px 6px;
    box-shadow:0 8px 18px rgba(0,0,0,.4);
}

.sugerencia-club{
    display:block;
    width:100%;
    padding:11px 12px;
    border:0;
    border-radius:0;
    background:transparent;
    color:#f5f7fa;
    text-align:left;
    font-size:15px;
}

.sugerencia-club:hover,
.sugerencia-club[aria-selected="true"]{
    background:#26384d;
    color:#fff;
}

.sugerencia-club-sin-resultados{
    padding:11px 12px;
    color:#c2cbd6;
}

.formulario-impresion-admin{
    display:grid;
    gap:10px;
    margin-top:20px;
    padding:14px;
    border:1px solid #283342;
    border-radius:6px;
    background:#10151d;
}

.formulario-impresion-admin a[aria-disabled="true"]{
    color:#9aa8b8;
    pointer-events:none;
}

/* Colores del modo claro para las tarjetas de resumen y acciones. */
.modo-claro .resumen-tarjeta{
    background:#f7f9fc;
    border-color:#8b9bad;
}

.modo-claro .resumen-tarjeta h3{
    color:#35465a;
}

.modo-claro .resumen-valor{
    color:#17212f;
}

.modo-claro .resumen-tarjeta p{
    color:#35465a;
}

.modo-claro .seccion-acciones{
    background:#f7f9fc;
    border-color:#8b9bad;
}

.modo-claro .seccion-acciones textarea,
.modo-claro .publicacion-aviso{
    background:#ffffff;
    border-color:#8b9bad;
    color:#17212f;
}

.modo-claro .sugerencias-club{
    background:#fff;
    border-color:#8b9bad;
}

.modo-claro .sugerencia-club{
    background:#fff;
    color:#17212f;
}

.modo-claro .sugerencia-club:hover,
.modo-claro .sugerencia-club[aria-selected="true"]{
    background:#dce6f0;
    color:#17212f;
}

.modo-claro .sugerencia-club-sin-resultados{
    color:#35465a;
}

/* Presentación adaptable de filtros y tabla de posiciones. */
.tabla-posiciones-contenedor{
    overflow-x:auto;
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

/* Estilo compartido de formularios, botones y pie de página. */
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

ul{
    line-height:40px;
}

footer{
    background:#10151d;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:20px;
    border-top:1px solid #d5a83b;
}

/* Alterna fondos y contraste de los componentes para el tema claro. */
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

</style>

</head>

<body>

<!-- Identidad del sistema mostrada sobre las herramientas del panel. -->
<header>

<h1><img class="logo-encabezado" src="publico/img/logo-yell.png" alt="YELL Industries"></h1>

</header>


<!-- Accesos a las secciones administrativas, al tema y al cierre de sesión. -->
<nav>

<button onclick="mostrarInicio()">Inicio</button>

<button onclick="mostrarClubes()">Clubes</button>

<button onclick="mostrarJugadores()">Jugadores</button>

<button onclick="mostrarCategorias()">Categorías</button>

<button onclick="mostrarPartidos()">Partidos</button>

<button onclick="mostrarTarjetas()">Tarjetas</button>

<button onclick="mostrarAvisos()">Avisos</button>

<div class="controles-sesion">
    <button type="button" id="botonTema" onclick="alternarModoClaro()">Modo claro</button>
    <a href="cerrar_sesion.php" onclick="return confirmarCierreSesion()">Cerrar sesión</a>
</div>

</nav>


<!-- Zona dinámica en la que JavaScript carga la sección seleccionada. -->
<main>

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

// Reglas adicionales para aplicar la apariencia clara a tablas dentro de iframes.
const estiloClaroIframes = `
    body { background:#f2f5f9 !important; color:#17212f !important; font-size:18px !important; }
    table { background:#ffffff !important; color:#17212f !important; }
    th { background:#dce6f0 !important; color:#17212f !important; border-bottom:2px solid #8a5a00 !important; }
    td { background:#ffffff !important; color:#17212f !important; border-color:#8b9bad !important; }
    tr:nth-child(even) td { background:#eaf0f6 !important; }
`;

// Inserta o actualiza el estilo del tema en el documento de un iframe.
function aplicarTemaIframe(iframe){
    const documento = iframe.contentDocument;

    if(!documento || !documento.head){
        return;
    }

    let estilo = documento.getElementById("tema-claro-iframe");

    if(!estilo){
        estilo = documento.createElement("style");
        estilo.id = "tema-claro-iframe";
        documento.head.appendChild(estilo);
    }

    estilo.textContent = document.body.classList.contains("modo-claro")
        ? estiloClaroIframes
        : "";
}

// Sincroniza el tema guardado con la página y los iframes ya presentes.
function aplicarTema(){
    const modoClaro = localStorage.getItem("ligaModoClaro") === "true";
    document.body.classList.toggle("modo-claro", modoClaro);

    const botonTema = document.getElementById("botonTema");
    botonTema.textContent = modoClaro ? "Modo oscuro" : "Modo claro";

    document.querySelectorAll("iframe").forEach(iframe => {
        aplicarTemaIframe(iframe);
        iframe.addEventListener("load", () => aplicarTemaIframe(iframe), { once: true });
    });
}

// Guarda el tema elegido y actualiza su presentación.
function alternarModoClaro(){
    const activarModoClaro = !document.body.classList.contains("modo-claro");
    localStorage.setItem("ligaModoClaro", activarModoClaro ? "true" : "false");
    aplicarTema();
}

// Detecta iframes agregados dinámicamente y les aplica el tema al cargar.
const observadorIframes = new MutationObserver(cambios => {
    cambios.forEach(cambio => {
        cambio.addedNodes.forEach(nodo => {
            if(nodo instanceof HTMLIFrameElement){
                nodo.addEventListener("load", () => aplicarTemaIframe(nodo), { once: true });
            } else if(nodo.querySelectorAll){
                nodo.querySelectorAll("iframe").forEach(iframe => {
                    iframe.addEventListener("load", () => aplicarTemaIframe(iframe), { once: true });
                });
            }
        });
    });
});
observadorIframes.observe(document.body, { childList: true, subtree: true });
aplicarTema();

// Recupera la categoría y el club seleccionados y vuelve a cargar la tabla.
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

// Devuelve el diseño reutilizable de una sección con acciones y resumen.
function construirSeccion(titulo, tituloAcciones, acciones, resumenId){
    return `
        <h2>${titulo}</h2>
        <div class="seccion-layout">
            <section class="seccion-acciones">
                <h3>${tituloAcciones}</h3>
                ${acciones}
            </section>
            <aside class="seccion-resumen">
                <div class="resumen-tarjetas" id="${resumenId}">
                    <p>Cargando resumen...</p>
                </div>
            </aside>
        </div>
    `;
}

// Solicita al servidor los indicadores que alimentan las tarjetas de resumen.
function cargarResumenes(){
    return fetch("consultar_resumenes.php")
    .then(response => {
        if(!response.ok){
            throw new Error("No se pudieron cargar los resúmenes.");
        }
        return response.json();
    });
}

// Construye de forma segura las tarjetas de resumen a partir de sus datos.
function mostrarTarjetasResumen(contenedorId, tarjetas){
    const contenedor = document.getElementById(contenedorId);
    if(!contenedor){
        return;
    }

    contenedor.replaceChildren();

    tarjetas.forEach(tarjeta => {
        const articulo = document.createElement("article");
        articulo.className = "resumen-tarjeta";

        const titulo = document.createElement("h3");
        titulo.textContent = tarjeta.titulo;

        const valor = document.createElement("span");
        valor.className = "resumen-valor";
        valor.textContent = tarjeta.valor;

        const descripcion = document.createElement("p");
        descripcion.textContent = tarjeta.descripcion;

        articulo.append(titulo, valor, descripcion);
        contenedor.appendChild(articulo);
    });
}

// Formatea los datos del próximo partido para su visualización.
function formatearProximoPartido(partido){
    if(!partido){
        return {
            valor:"Sin partido",
            descripcion:"No hay encuentros próximos agendados."
        };
    }

    const fecha = partido.fecha
        ? new Date(`${partido.fecha}T00:00:00`).toLocaleDateString("es-UY")
        : "Sin fecha";
    return {
        valor:`${partido.local} - ${partido.visitante}`,
        descripcion:`${fecha} · Categoría ${partido.categoria}`
    };
}

// Muestra el inicio del panel y su clasificación.
function mostrarInicio(){

document.getElementById("contenido").innerHTML=`

<h2>Tabla de posiciones</h2>
<p>Clasificación por categoría calculada a partir de los partidos jugados.</p>
<div id="tablaPosiciones">Cargando tabla de posiciones...</div>

`;

cargarTablaPosiciones();

}


// Renderiza las herramientas y métricas de gestión de clubes.
function mostrarClubes(){

document.getElementById("contenido").innerHTML=`

${construirSeccion("Clubes", "Gestión de clubes", `
    <ul>
        <li><button onclick="agregarClub()">Agregar Club</button></li>
        <li><button onclick="consultarClubes()">Consultar Clubes</button></li>
        <li><button onclick="modificarClub()">Modificar Club</button></li>
        <li><button onclick="eliminarClub()">Eliminar Club</button></li>
    </ul>
`, "resumenClubes")}

`;

    cargarResumenes()
    .then(resumen => {
        const proximo = formatearProximoPartido(resumen.clubes.proximo);
        mostrarTarjetasResumen("resumenClubes", [
            {titulo:"Clubes registrados", valor:resumen.clubes.total, descripcion:"Total de clubes en la liga"},
            {titulo:"Jugadores", valor:resumen.clubes.jugadores, descripcion:"Jugadores registrados"},
            {titulo:"Próximo partido", valor:proximo.valor, descripcion:proximo.descripcion}
        ]);
    })
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("resumenClubes");
        if(contenedor) contenedor.textContent = "No se pudo cargar el resumen de clubes.";
    });

}


// Renderiza las herramientas y métricas de gestión de jugadores.
function mostrarJugadores(){

document.getElementById("contenido").innerHTML=construirSeccion("Jugadores", "Gestión de jugadores", `
    <ul>
        <li><button onclick="agregarJugador()">Agregar Jugador</button></li>
        <li><button onclick="consultarJugadores()">Consultar Jugadores</button></li>
        <li><button onclick="modificarJugador()">Modificar Jugador</button></li>
        <li><button onclick="eliminarJugador()">Eliminar Jugador</button></li>
    </ul>
`, "resumenJugadores");

    cargarResumenes()
    .then(resumen => mostrarTarjetasResumen("resumenJugadores", [
        {titulo:"Jugadores registrados", valor:resumen.jugadores.total, descripcion:"Total de jugadores en la liga"},
        {titulo:"Categorías", valor:resumen.jugadores.categorias, descripcion:"Categorías disponibles"},
        {titulo:"Clubes", valor:resumen.jugadores.clubes, descripcion:"Clubes registrados"}
    ]))
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("resumenJugadores");
        if(contenedor) contenedor.textContent = "No se pudo cargar el resumen de jugadores.";
    });

}


// Lista las categorías disponibles y muestra sus estadísticas.
function mostrarCategorias(){
    document.getElementById("contenido").innerHTML =
        construirSeccion(
            "Categorías",
            "Jugadores por categoría",
            '<p id="accionesCategorias">Cargando categorías...</p>',
            "resumenCategorias"
        );

    fetch("consultar_categorias_partido.php")
    .then(response => {
        if(!response.ok) throw new Error("No se pudieron cargar las categorías.");
        return response.text();
    })
    .then(opciones => {
        const temporal = document.createElement("select");
        temporal.innerHTML = opciones;
        const lista = document.createElement("ul");
        Array.from(temporal.options).forEach(opcion => {
            if(!opcion.value) return;
            const elemento = document.createElement("li");
            const boton = document.createElement("button");
            boton.type = "button";
            boton.textContent = opcion.textContent;
            boton.addEventListener("click", () => mostrarJugadoresCategoria(opcion.value));
            elemento.appendChild(boton);
            lista.appendChild(elemento);
        });
        const contenedor = document.getElementById("accionesCategorias");
        if(contenedor){
            contenedor.replaceChildren(lista);
            const impresion = document.createElement("div");
            impresion.className = "formulario-impresion-admin";
            impresion.innerHTML = `
                <h3>Imprimir planilla de un club</h3>
                <label for="clubPlanillaAdmin">Club:</label>
                <select id="clubPlanillaAdmin"><option value="">Selecciona un club</option></select>
                <label for="categoriaPlanillaAdmin">Categoría:</label>
                <select id="categoriaPlanillaAdmin"><option value="">Selecciona una categoría</option></select>
                <a id="enlacePlanillaAdmin" href="#" target="_blank" rel="noopener" aria-disabled="true">Abrir planilla imprimible</a>
            `;
            contenedor.appendChild(impresion);
            const categoriaSelect = impresion.querySelector("#categoriaPlanillaAdmin");
            categoriaSelect.innerHTML += opciones;
            fetch("consultar_clubes_opciones.php")
            .then(response => {
                if(!response.ok) throw new Error("No se pudieron cargar los clubes.");
                return response.text();
            })
            .then(clubes => {
                impresion.querySelector("#clubPlanillaAdmin").innerHTML += clubes;
                const clubSelect = impresion.querySelector("#clubPlanillaAdmin");
                const enlace = impresion.querySelector("#enlacePlanillaAdmin");
                const actualizarEnlace = () => {
                    if(!clubSelect.value || !categoriaSelect.value){
                        enlace.href = "#";
                        enlace.setAttribute("aria-disabled", "true");
                        return;
                    }
                    const parametros = new URLSearchParams({
                        club_id: clubSelect.value,
                        categoria_id: categoriaSelect.value
                    });
                    enlace.href = `imprimir_lista_jugadores.php?${parametros}`;
                    enlace.removeAttribute("aria-disabled");
                };
                clubSelect.addEventListener("change", actualizarEnlace);
                categoriaSelect.addEventListener("change", actualizarEnlace);
            })
            .catch(error => {
                console.error(error);
                const errorClubes = document.createElement("p");
                errorClubes.textContent = error.message;
                impresion.appendChild(errorClubes);
            });
        }
    })
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("accionesCategorias");
        if(contenedor) contenedor.textContent = error.message;
    });

    cargarResumenes()
    .then(resumen => mostrarTarjetasResumen("resumenCategorias", [
        {titulo:"Categorías disponibles", valor:resumen.categorias.total, descripcion:"Categorías configuradas"},
        {titulo:"Con jugadores", valor:resumen.categorias.activas, descripcion:"Categorías con al menos un jugador"},
        {titulo:"Mayor plantilla", valor:resumen.categorias.mayor_cantidad, descripcion:"Jugadores en la categoría más numerosa"}
    ]))
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("resumenCategorias");
        if(contenedor) contenedor.textContent = "No se pudo cargar el resumen de categorías.";
    });

}

// Abre la lista de jugadores pertenecientes a la categoría seleccionada.
function mostrarJugadoresCategoria(categoria_id){

    document.getElementById("contenido").innerHTML=`

    <h2>Jugadores de la categoría</h2>

    <iframe
        src="consultar_jugadores_categoria.php?categoria_id=${categoria_id}"
        width="100%"
        height="400">
    </iframe>

    <br><br>

    <button onclick="mostrarCategorias()">
        Volver a Categorías
    </button>

    `;

}


// Renderiza las acciones para consultar y gestionar los partidos.
function mostrarPartidos(){

    document.getElementById("contenido").innerHTML = construirSeccion("Partidos / Fixture", "Gestión de partidos", `
        <ul>
            <li><button onclick="agregarPartido()">Crear Partido</button></li>
            <li><button onclick="consultarFixture()">Consultar Fixture</button></li>
            <li><button onclick="registrarResultado()">Registrar Resultado</button></li>
        </ul>
    `, "resumenPartidos");

    cargarResumenes()
    .then(resumen => {
        const proximo = formatearProximoPartido(resumen.partidos.proximo);
        mostrarTarjetasResumen("resumenPartidos", [
            {titulo:"Partidos registrados", valor:resumen.partidos.total, descripcion:"Total de encuentros"},
            {titulo:"Sin fecha", valor:resumen.partidos.sin_fecha, descripcion:"Encuentros aún sin fecha asignada"},
            {titulo:"Programados", valor:resumen.partidos.programados, descripcion:"Encuentros con fecha confirmada"},
            {titulo:"Jugados", valor:resumen.partidos.jugados, descripcion:"Encuentros con resultado"},
            {titulo:"Pendientes", valor:resumen.partidos.pendientes, descripcion:"Encuentros pendientes de resolución"},
            {titulo:"Suspendidos", valor:resumen.partidos.suspendidos, descripcion:"Encuentros suspendidos"},
            {titulo:"Próximo partido", valor:proximo.valor, descripcion:proximo.descripcion}
        ]);
    })
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("resumenPartidos");
        if(contenedor) contenedor.textContent = "No se pudo cargar el resumen de partidos.";
    });

}

// Crea el formulario para agendar un partido y carga sus opciones.
function agregarPartido(){

    document.getElementById("contenido").innerHTML = `

    <h2>Crear Partido</h2>

    <form action="guardar_partido.php" method="POST">

        <label>Club local:</label>

        <select id="clubLocal" name="club_local" required>

            <option value="">Seleccione un club</option>

        </select>

        <br><br>

        <label>Club visitante:</label>

        <select id="clubVisitante" name="club_visitante" required>

            <option value="">Seleccione un club</option>

        </select>

        <br><br>

        <label>Categoría:</label>

        <select id="categoriaPartido" name="categoria_id" required>

            <option value="">Seleccione una categoría</option>

        </select>

        <br><br>

        <label>Estado:</label>
        <select id="estadoPartidoNuevo" name="estado" required>
            <option value="programado">Programado</option>
            <option value="pendiente">Pendiente</option>
            <option value="suspendido">Suspendido</option>
            <option value="sin_fecha">Sin fecha</option>
        </select>

        <br><br>

        <label>Fecha:</label>
        <input type="date" id="fechaPartidoNuevo" name="fecha" min="<?php echo date('Y'); ?>-01-01">

        <br><br>

        <button type="submit">
            Guardar Partido
        </button>

    </form>

    <br>

    <button onclick="mostrarPartidos()">
        Volver
    </button>

    `;

    cargarClubesPartido();
    cargarCategoriasPartido();
    configurarFechaPartidoNuevo();

}

function configurarFechaPartidoNuevo(){
    const estado = document.getElementById("estadoPartidoNuevo");
    const fecha = document.getElementById("fechaPartidoNuevo");
    if(!estado || !fecha) return;

    const actualizar = () => {
        fecha.required = estado.value !== "sin_fecha";
        fecha.disabled = estado.value === "sin_fecha";
        if(fecha.disabled) fecha.value = "";
    };
    estado.addEventListener("change", actualizar);
    actualizar();
}

// Obtiene clubes y los incorpora en los selectores de local y visitante.
function cargarClubesPartido(){

    fetch("consultar_clubes_partido.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("clubLocal").innerHTML += data;
        document.getElementById("clubVisitante").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar los clubes");

    });

}


// Obtiene las categorías y las añade al selector del partido.
function cargarCategoriasPartido(){

    fetch("consultar_categorias_partido.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("categoriaPartido").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar las categorías");

    });

}

// Muestra el calendario de encuentros en una vista incrustada.
function consultarFixture(){

    document.getElementById("contenido").innerHTML = `

    <h2>Fixture</h2>

    <div class="tabla-posiciones-filtros">
        <label for="filtroEstadoFixture">Filtrar partidos:
            <select id="filtroEstadoFixture">
                <option value="">Todos los estados</option>
                <option value="sin_fecha">Sin fecha</option>
                <option value="programado">Programados</option>
                <option value="jugado">Jugados</option>
                <option value="pendiente">Pendientes</option>
                <option value="suspendido">Suspendidos</option>
            </select>
        </label>
        <button type="button" onclick="filtrarFixtureAdmin()">Aplicar filtro</button>
    </div>

    <iframe
        id="fixtureAdmin"
        src="consultar_fixture.php"
        width="100%"
        height="500"
        style="border: none;">
    </iframe>

    <br><br>

    <button onclick="mostrarPartidos()">
        Volver
    </button>

    `;

}

function filtrarFixtureAdmin(){
    const estado = document.getElementById("filtroEstadoFixture")?.value || "";
    const parametros = new URLSearchParams();
    if(estado) parametros.set("estado", estado);
    const iframe = document.getElementById("fixtureAdmin");
    if(iframe){
        iframe.src = `consultar_fixture.php${parametros.toString() ? `?${parametros}` : ""}`;
    }
}

// Crea el formulario para seleccionar un partido y asignarle un marcador.
function registrarResultado(){

    document.getElementById("contenido").innerHTML = `

    <h2>Registrar Resultado</h2>

    <form action="guardar_resultado.php" method="POST">

        <label>Partido:</label>

        <select id="partidoResultado" name="partido_id" required>

            <option value="">Seleccione un partido</option>

        </select>

        <br><br>

        <label>Resultado:</label>

        <input
            type="text"
            name="resultado"
            placeholder="Ejemplo: 2 - 1"
            pattern="[0-9]{1,3}\\s*-\\s*[0-9]{1,3}"
            title="Ingresa el resultado con el formato 2 - 1."
            required
        >

        <br><br>

        <label>Fecha del partido (necesaria si aún no tiene fecha):</label>
        <input type="date" id="fechaResultadoPartido" name="fecha" min="<?php echo date('Y'); ?>-01-01">

        <br><br>

        <button type="submit">
            Guardar Resultado
        </button>

    </form>

    <br>

    <button onclick="mostrarPartidos()">
        Volver
    </button>

    `;

    cargarPartidosResultado();

}

// Carga los encuentros disponibles en el selector de resultados.
function cargarPartidosResultado(){

    fetch("consultar_partidos_resultado.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("partidoResultado").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar los partidos");

    });

}   

// Presenta la gestión de sanciones y sus indicadores resumidos.
function mostrarTarjetas(){

    document.getElementById("contenido").innerHTML = construirSeccion("Tarjetas", "Gestión de sanciones", `
        <ul>
            <li><button onclick="registrarTarjetaAmarilla()">Registrar Tarjeta Amarilla</button></li>
            <li><button onclick="registrarTarjetaRoja()">Registrar Tarjeta Roja</button></li>
            <li><button onclick="consultarTarjetas()">Consultar Tarjetas</button></li>
            <li><button onclick="eliminarTarjeta()">Eliminar Tarjeta</button></li>
        </ul>
    `, "resumenTarjetas");

    cargarResumenes()
    .then(resumen => mostrarTarjetasResumen("resumenTarjetas", [
        {titulo:"Sanciones registradas", valor:resumen.tarjetas.total, descripcion:"Total de tarjetas"},
        {titulo:"Amarillas", valor:resumen.tarjetas.amarillas, descripcion:"Tarjetas amarillas registradas"},
        {titulo:"Rojas", valor:resumen.tarjetas.rojas, descripcion:"Tarjetas rojas registradas"}
    ]))
    .catch(error => {
        console.error(error);
        const contenedor = document.getElementById("resumenTarjetas");
        if(contenedor) contenedor.textContent = "No se pudo cargar el resumen de tarjetas.";
    });

}

// Muestra formularios para publicar avisos y boletines y listas existentes.
function mostrarAvisos(){
    document.getElementById("contenido").innerHTML = `
        <h2>Avisos y boletines</h2>
        <div class="seccion-layout">
            <section class="seccion-acciones">
                <h3>Publicar comunicado general</h3>
                <form id="formAviso">
                    <label for="tituloAviso">Título:</label>
                    <input type="text" id="tituloAviso" name="titulo" maxlength="100" required>
                    <br><br>
                    <label for="descripcionAviso">Descripción:</label>
                    <textarea id="descripcionAviso" name="descripcion" rows="5" maxlength="10000" required></textarea>
                    <br><br>
                    <label for="fechaAviso">Fecha:</label>
                    <input type="date" id="fechaAviso" name="fecha" required>
                    <br><br>
                    <button type="submit">Publicar comunicado</button>
                    <p id="estadoAviso" role="status"></p>
                </form>

                <h3>Subir boletín de sanciones (PDF)</h3>
                <form id="formBoletin" enctype="multipart/form-data">
                    <label for="tituloBoletin">Título:</label>
                    <input type="text" id="tituloBoletin" name="titulo" maxlength="150" required>
                    <br><br>
                    <label for="descripcionBoletin">Descripción (opcional):</label>
                    <textarea id="descripcionBoletin" name="descripcion" rows="3" maxlength="10000"></textarea>
                    <br><br>
                    <label for="archivoBoletin">Archivo PDF:</label>
                    <input type="file" id="archivoBoletin" name="archivo_pdf" accept="application/pdf,.pdf" required>
                    <br><br>
                    <button type="submit">Publicar boletín</button>
                    <p id="estadoBoletin" role="status"></p>
                </form>
            </section>
            <aside class="seccion-resumen">
                <h3>Comunicados publicados</h3>
                <div id="listaAvisos">Cargando comunicados...</div>
                <h3>Boletines publicados</h3>
                <div id="listaBoletines">Cargando boletines...</div>
            </aside>
        </div>
    `;

    document.getElementById("formAviso").addEventListener("submit", enviarAviso);
    document.getElementById("formBoletin").addEventListener("submit", enviarBoletin);
    cargarAvisosPublicados(true);
    cargarBoletinesPublicados(true);
}

// Envía el formulario de un comunicado general sin recargar la página.
function enviarAviso(evento){
    evento.preventDefault();
    enviarFormularioAvisos(
        "guardar_aviso.php",
        new FormData(evento.currentTarget),
        "estadoAviso",
        "El comunicado se publicó correctamente.",
        "formAviso",
        () => cargarAvisosPublicados(true)
    );
}

// Valida el archivo PDF en el navegador y envía el formulario del boletín.
function enviarBoletin(evento){
    evento.preventDefault();
    const archivo = document.getElementById("archivoBoletin").files[0];
    if(!archivo || archivo.size > 10 * 1024 * 1024){
        document.getElementById("estadoBoletin").textContent =
            "Seleccioná un PDF de hasta 10 MB.";
        return;
    }

    enviarFormularioAvisos(
        "guardar_boletin.php",
        new FormData(evento.currentTarget),
        "estadoBoletin",
        "El boletín se publicó correctamente.",
        "formBoletin",
        () => cargarBoletinesPublicados(true)
    );
}

// Envía formularios de publicaciones y actualiza su estado y lista.
function enviarFormularioAvisos(url, datos, estadoId, mensajeOk, formularioId, actualizarLista){
    const estado = document.getElementById(estadoId);
    estado.textContent = "Guardando...";

    fetch(url, {method:"POST", body:datos})
    .then(async response => {
        const resultado = await response.json();
        if(!response.ok){
            throw new Error(resultado.error || "No se pudo completar la operación.");
        }
        return resultado;
    })
    .then(() => {
        document.getElementById(formularioId).reset();
        estado.textContent = mensajeOk;
        actualizarLista();
    })
    .catch(error => {
        console.error(error);
        estado.textContent = error.message;
    });
}

// Solicita y presenta los comunicados, conectando sus controles de borrado.
function cargarAvisosPublicados(esAdministrador){
    const lista = document.getElementById("listaAvisos");
    if(!lista) return;

    fetch("consultar_avisos.php")
    .then(response => {
        if(!response.ok) throw new Error("No se pudieron cargar los comunicados.");
        return response.text();
    })
    .then(html => {
        lista.innerHTML = html;
        if(esAdministrador){
            lista.querySelectorAll("form[data-eliminar-aviso]").forEach(formulario => {
                formulario.addEventListener("submit", eliminarAviso);
            });
        }
    })
    .catch(error => {
        console.error(error);
        lista.textContent = error.message;
    });
}

// Solicita y presenta los boletines, conectando sus controles de borrado.
function cargarBoletinesPublicados(esAdministrador){
    const lista = document.getElementById("listaBoletines");
    if(!lista) return;

    fetch("consultar_boletines.php")
    .then(response => {
        if(!response.ok) throw new Error("No se pudieron cargar los boletines.");
        return response.text();
    })
    .then(html => {
        lista.innerHTML = html;
        if(esAdministrador){
            lista.querySelectorAll("form[data-eliminar-boletin]").forEach(formulario => {
                formulario.addEventListener("submit", eliminarBoletin);
            });
        }
    })
    .catch(error => {
        console.error(error);
        lista.textContent = error.message;
    });
}

// Intercepta el envío para confirmar y borrar el comunicado mediante el servidor.
function eliminarAviso(evento){
    evento.preventDefault();
    confirmarEliminacion(evento.currentTarget, "eliminar_aviso.php", () => cargarAvisosPublicados(true));
}

// Intercepta el envío para confirmar y borrar el boletín mediante el servidor.
function eliminarBoletin(evento){
    evento.preventDefault();
    confirmarEliminacion(evento.currentTarget, "eliminar_boletin.php", () => cargarBoletinesPublicados(true));
}

// Confirma el borrado, envía los datos y actualiza la lista si tiene éxito.
function confirmarEliminacion(formulario, url, actualizarLista){
    if(!confirm("¿Seguro que querés eliminar esta publicación?")) return;

    fetch(url, {method:"POST", body:new FormData(formulario)})
    .then(async response => {
        const resultado = await response.json();
        if(!response.ok){
            throw new Error(resultado.error || "No se pudo eliminar la publicación.");
        }
        return resultado;
    })
    .then(actualizarLista)
    .catch(error => {
        console.error(error);
        alert(error.message);
    });
}

// Presenta el formulario de alta de una tarjeta amarilla.
function registrarTarjetaAmarilla(){

    document.getElementById("contenido").innerHTML = `

    <h2>Registrar Tarjeta Amarilla</h2>

    <form action="guardar_tarjeta.php" method="POST">

        <label>Jugador:</label>

        <select id="jugadorTarjeta" name="jugador_cedula" required>

            <option value="">
                Seleccione un jugador
            </option>

        </select>

        <br><br>

        <label>Fecha:</label>

        <input type="date" name="fecha" required>

        <br><br>

        <button type="submit">
            Registrar Tarjeta Amarilla
        </button>

    </form>

    <br>

    <button onclick="mostrarTarjetas()">
        Volver
    </button>

    `;

    cargarJugadoresTarjeta();

}

// Presenta el formulario de alta de una tarjeta roja.
function registrarTarjetaRoja(){

    document.getElementById("contenido").innerHTML = `

    <h2>Registrar Tarjeta Roja</h2>

    <form action="guardar_tarjeta_roja.php" method="POST">

        <label>Jugador:</label>

        <select id="jugadorTarjetaRoja" name="jugador_cedula" required>

            <option value="">
                Seleccione un jugador
            </option>

        </select>

        <br><br>

        <label>Fecha:</label>

        <input type="date" name="fecha" required>

        <br><br>

        <button type="submit">
            Registrar Tarjeta Roja
        </button>

    </form>

    <br>

    <button onclick="mostrarTarjetas()">
        Volver
    </button>

    `;

    cargarJugadoresTarjetaRoja();

}

// Carga los jugadores disponibles en el formulario de tarjeta roja.
function cargarJugadoresTarjetaRoja(){

    fetch("consultar_jugadores_tarjeta.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("jugadorTarjetaRoja").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar los jugadores");

    });

}

// Muestra la tabla de tarjetas y solicita sus datos.
function consultarTarjetas(){

    document.getElementById("contenido").innerHTML = `

    <h2>Tarjetas Registradas</h2>

    <div id="tablaTarjetas">
        Cargando tarjetas...
    </div>

    <br>

    <button onclick="mostrarTarjetas()">
        Volver
    </button>

    `;

    cargarTarjetas();

}

// Carga el marcado de la tabla de sanciones en la sección activa.
function cargarTarjetas(){

    fetch("consultar_tarjetas.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("tablaTarjetas").innerHTML = data;

    })
    .catch(error => {

        document.getElementById("tablaTarjetas").innerHTML =
            "<p>Error al cargar las tarjetas.</p>";

    });

}

// Carga las opciones de jugadores para el formulario de tarjeta amarilla.
function cargarJugadoresTarjeta(){

    fetch("consultar_jugadores_tarjeta.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("jugadorTarjeta").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar los jugadores");

    });

}

// Presenta el formulario de eliminación de una tarjeta por identificador.
function eliminarTarjeta(){

    document.getElementById("contenido").innerHTML = `

    <h2>Eliminar Tarjeta</h2>

    <form action="eliminar_tarjeta.php" method="POST">

        <label>ID de la tarjeta:</label>

        <input
            type="number"
            name="id"
            required
        >

        <br><br>

        <button type="submit">
            Eliminar Tarjeta
        </button>

    </form>

    <br>

    <button onclick="mostrarTarjetas()">
        Volver
    </button>

    `;

}



// Construye el formulario de alta del jugador y carga clubes y categorías.
function agregarJugador(){

    document.getElementById("contenido").innerHTML = `

    <h2>Agregar Jugador</h2>

    <form action="guardar_jugador.php" method="POST" enctype="multipart/form-data" onsubmit="return validarJugador()">

        <input type="text" id="cedulaJugador" name="cedula" placeholder="Cédula (8 números)" inputmode="numeric" minlength="8" maxlength="8" pattern="[0-9]{8}" oninput="this.value=this.value.replace(/[^0-9]/g, '')" required>

        <br><br>

        <input type="text" id="nombreJugador" name="nombre" placeholder="Nombre (mínimo 3 caracteres)" minlength="3" required>

        <br><br>

        <input type="text" id="apellidoJugador" name="apellido" placeholder="Apellido (mínimo 3 caracteres)" minlength="3" required>

        <br><br>

        <label for="masaJugador">Masa (kg):</label>
        <input type="number" id="masaJugador" name="masa" placeholder="Masa en kilogramos" min="0.01" max="999.99" step="0.01" required>

        <br><br>

        <label for="alturaJugador">Altura (m):</label>
        <input type="number" id="alturaJugador" name="altura" placeholder="Altura en metros" min="0.01" max="3" step="0.01" required>

        <br><br>

        <label for="fotoJugador">Foto del jugador (opcional)</label>
        <input type="file" id="fotoJugador" name="foto" accept="image/jpeg,image/png,image/webp">

        <br><br>

        <label for="aptitudJugador">Aptitud física (opcional)</label>
        <input type="file" id="aptitudJugador" name="aptitud_fisica" accept="application/pdf,image/jpeg,image/png">

        <br><br>

        <div>
            <label for="fechaNacimientoJugador"><strong>Fecha de nacimiento (obligatoria):</strong></label>
            <br><br>
            <input type="date" id="fechaNacimientoJugador" name="fecha_nacimiento" min="2013-01-01" max="2019-12-31" required>
        </div>

        <br><br>

        <label>Club:</label>

        <select id="clubJugador" name="club_id">

            <option value="">Seleccione un club</option>

        </select>

        <br><br>

        <label>Categoría:</label>

        <select id="categoriaJugador" name="categoria_id">

            <option value="">Seleccione una categoría</option>

        </select>

        <br><br>

        <button type="submit">
            Guardar
        </button>

    </form>

    `;

    cargarClubes();
    cargarCategorias();
}

// Obtiene opciones de clubes para el formulario de jugador.
function cargarClubes(){

    fetch("consultar_clubes_opciones.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("clubJugador").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar los clubes");

    });

}


// Obtiene opciones de categorías para el formulario de jugador.
function cargarCategorias(){

    fetch("consultar_categorias.php")
    .then(response => response.text())
    .then(data => {

        document.getElementById("categoriaJugador").innerHTML += data;

    })
    .catch(error => {

        alert("Error al cargar las categorías");

    });

}

// Comprueba en el navegador los datos requeridos y los límites de archivos.
function validarJugador(){

    let cedula = document.getElementById("cedulaJugador").value.trim();
    let nombre = document.getElementById("nombreJugador").value.trim();
    let apellido = document.getElementById("apellidoJugador").value.trim();
    let masa = document.getElementById("masaJugador").value;
    let altura = document.getElementById("alturaJugador").value;
    let foto = document.getElementById("fotoJugador").files[0];
    let aptitud = document.getElementById("aptitudJugador").files[0];
    let fechaNacimiento = document.getElementById("fechaNacimientoJugador").value;
    let club = document.getElementById("clubJugador").value;
    let categoria = document.getElementById("categoriaJugador").value;

    if(!/^[0-9]{8}$/.test(cedula)){
        alert("La cédula debe tener exactamente 8 números.");
        return false;
    }

    if(nombre.length < 3 || apellido.length < 3){
        alert("El nombre y el apellido deben tener al menos 3 caracteres.");
        return false;
    }

    if(fechaNacimiento < "2013-01-01" || fechaNacimiento > "2019-12-31"){
        alert("La fecha de nacimiento debe estar entre el 01/01/2013 y el 31/12/2019.");
        return false;
    }

    if(
        masa === "" ||
        altura === "" ||
        fechaNacimiento === "" ||
        club === "" ||
        categoria === ""
    ){

        alert("Faltan datos por completar. Complete todos los campos.");

        return false;
    }

    if(Number(masa) <= 0 || Number(masa) > 999.99 || Number(altura) < 0.01 || Number(altura) > 3){
        alert("Verifique que la masa sea mayor que 0 kg y la altura esté entre 0,01 y 3 metros.");
        return false;
    }

    if((foto && foto.size > 5 * 1024 * 1024) || (aptitud && aptitud.size > 10 * 1024 * 1024)){
        alert("La foto no puede superar 5 MB y la aptitud física no puede superar 10 MB.");
        return false;
    }

    return true;
}


// Muestra el listado completo de jugadores en un iframe.
function consultarJugadores(){

document.getElementById("contenido").innerHTML=`

<h2>Jugadores registrados</h2>

<iframe 
src="consultar_jugadores.php"
width="100%"
height="400">
</iframe>

`;

}


// Construye el formulario para modificar la ficha de un jugador.
function modificarJugador(){

document.getElementById("contenido").innerHTML=`

<h2>Modificar Jugador</h2>

<form action="modificar_jugador.php" method="POST" onsubmit="return confirmarCambioJugador(this, 'modificar')">

<label for="buscarJugadorModificar">Buscar jugador:</label>
<div class="buscador-club-autocompletar">
    <input type="search" id="buscarJugadorModificar" placeholder="Escribe nombre, apellido o cédula" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sugerenciasJugadorModificar">
    <div id="sugerenciasJugadorModificar" class="sugerencias-club" role="listbox" hidden></div>
</div>
<input type="hidden" id="cedulaJugadorModificar" name="cedula">

<br><br>

<input type="text" id="nombreJugadorModificar" name="nombre" placeholder="Nuevo nombre" required disabled>

<br><br>

<input type="text" id="apellidoJugadorModificar" name="apellido" placeholder="Nuevo apellido" required disabled>

<br><br>

<label for="fechaNacimientoModificar"><strong>Fecha de nacimiento (obligatoria):</strong></label>

<br><br>

<input type="date" id="fechaNacimientoModificar" name="fecha_nacimiento" required disabled>

<br><br>

<label for="clubModificarJugador">Club:</label>

<select id="clubModificarJugador" name="club_id" required disabled>
    <option value="">Seleccione un club</option>
</select>

<br><br>

<label for="categoriaModificarJugador">Categoría:</label>

<select id="categoriaModificarJugador" name="categoria_id" required disabled>
    <option value="">Seleccione una categoría</option>
</select>

<br><br>

<button type="submit">
Modificar
</button>

</form>

`;

configurarBuscadorJugador("buscarJugadorModificar", "sugerenciasJugadorModificar", jugador => {
    const cedula = document.getElementById("cedulaJugadorModificar");
    const nombre = document.getElementById("nombreJugadorModificar");
    const apellido = document.getElementById("apellidoJugadorModificar");
    const fechaNacimiento = document.getElementById("fechaNacimientoModificar");
    const club = document.getElementById("clubModificarJugador");
    const categoria = document.getElementById("categoriaModificarJugador");
    cedula.value = jugador ? jugador.cedula : "";
    nombre.disabled = !jugador;
    apellido.disabled = !jugador;
    fechaNacimiento.disabled = !jugador;
    club.disabled = !jugador;
    categoria.disabled = !jugador;
    nombre.value = jugador ? jugador.nombre : "";
    apellido.value = jugador ? jugador.apellido : "";
    fechaNacimiento.value = jugador ? jugador.fecha_nacimiento : "";

    if(jugador){
        if(!Array.from(club.options).some(opcion => opcion.value === String(jugador.club_id))){
            club.add(new Option(jugador.club, jugador.club_id));
        }
        if(!Array.from(categoria.options).some(opcion => opcion.value === String(jugador.categoria_id))){
            categoria.add(new Option(jugador.categoria, jugador.categoria_id));
        }
        club.value = String(jugador.club_id);
        categoria.value = String(jugador.categoria_id);
    } else {
        club.value = "";
        categoria.value = "";
    }
});

cargarClubesModificarJugador();
cargarCategoriasModificarJugador();

}

// Completa el selector de clubes de la ficha que se va a modificar.
function cargarClubesModificarJugador(){

    fetch("consultar_clubes_opciones.php")
    .then(response => response.text())
    .then(data => {
        const selector = document.getElementById("clubModificarJugador");
        const valorSeleccionado = selector.value;
        selector.insertAdjacentHTML("beforeend", data);
        selector.value = valorSeleccionado;
    })
    .catch(error => {
        alert("Error al cargar los clubes");
    });

}

// Completa el selector de categorías de la ficha que se va a modificar.
function cargarCategoriasModificarJugador(){

    fetch("consultar_categorias.php")
    .then(response => response.text())
    .then(data => {
        const selector = document.getElementById("categoriaModificarJugador");
        const valorSeleccionado = selector.value;
        selector.insertAdjacentHTML("beforeend", data);
        selector.value = valorSeleccionado;
    })
    .catch(error => {
        alert("Error al cargar las categorías");
    });

}


// Muestra el formulario para solicitar la baja de un jugador por cédula.
function eliminarJugador(){

document.getElementById("contenido").innerHTML=`

<h2>Eliminar Jugador</h2>

<form action="eliminar_jugador.php" method="POST" onsubmit="return confirmarCambioJugador(this, 'eliminar')">

<label for="buscarJugadorEliminar">Buscar jugador:</label>
<div class="buscador-club-autocompletar">
    <input type="search" id="buscarJugadorEliminar" placeholder="Escribe nombre, apellido o cédula" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sugerenciasJugadorEliminar">
    <div id="sugerenciasJugadorEliminar" class="sugerencias-club" role="listbox" hidden></div>
</div>
<input type="hidden" id="cedulaJugadorEliminar" name="cedula">

<br><br>

<button type="submit">
Eliminar
</button>

</form>

`;

configurarBuscadorJugador("buscarJugadorEliminar", "sugerenciasJugadorEliminar", jugador => {
    document.getElementById("cedulaJugadorEliminar").value = jugador ? jugador.cedula : "";
});

}




// Presenta el formulario para crear el club y las credenciales de su usuario.
function agregarClub(){

document.getElementById("contenido").innerHTML=`

<h2>Agregar Club</h2>

<form action="guardar_club.php" method="POST" enctype="multipart/form-data" onsubmit="return validarClub()">

<br><br>

<input 
type="text" 
id="nombreClub"
name="nombre" 
placeholder="Nombre del club">

<br><br>

<input 
type="text" 
id="ciudadClub"
name="ciudad" 
placeholder="Ciudad">

<br><br>

<input 
type="text" 
id="usuarioClub"
name="usuario" 
placeholder="Nombre de usuario">

<br><br>

<input 
type="password" 
id="contraseñaClub"
name="contraseña" 
placeholder="Contraseña">

<br><br>

<label for="logoClub">Escudo del club (opcional, JPG/PNG/WebP hasta 5 MB)</label>
<input class="escudo-club-form" type="file" id="logoClub" name="logo" accept="image/jpeg,image/png,image/webp">

<br><br>

<button type="submit">
Guardar
</button>

</form>

`;

}


// Comprueba que se hayan completado los campos del formulario de club.
function validarClub(){

let nombre = document.getElementById("nombreClub").value.trim();

let ciudad = document.getElementById("ciudadClub").value.trim();

let usuario = document.getElementById("usuarioClub").value.trim();

let contraseña = document.getElementById("contraseñaClub").value.trim();
let logo = document.getElementById("logoClub").files[0];


if(nombre === "" || ciudad === "" || usuario === "" || contraseña === ""){

alert("Faltan datos por completar. Complete todos los campos.");

return false;

}

if(logo && (!["image/jpeg", "image/png", "image/webp"].includes(logo.type) || logo.size > 5 * 1024 * 1024)){
    alert("El escudo debe ser JPG, PNG o WebP y no superar 5 MB.");
    return false;
}

return true;

}


// Muestra los clubes registrados mediante una vista incrustada.
function consultarClubes(){

document.getElementById("contenido").innerHTML=`

<h2>Clubes registrados</h2>

<iframe 
src="consultar_clubes.php"
width="100%"
height="400">
</iframe>

`;

}


// Presenta el formulario para cambiar nombre y ciudad de un club.
function modificarClub(){

document.getElementById("contenido").innerHTML=`

<h2>Modificar Club</h2>

<form action="modificar_club.php" method="POST" enctype="multipart/form-data" onsubmit="return validarSeleccionClubModificar()">

<label for="buscarClubModificar">Club:</label>
<div class="buscador-club-autocompletar">
    <input type="search" id="buscarClubModificar" placeholder="Escribe el nombre del club" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sugerenciasClubModificar">
    <div id="sugerenciasClubModificar" class="sugerencias-club" role="listbox" hidden></div>
</div>

<br><br>

<input type="hidden" id="idClubModificar" name="id" required>

<label for="nombreClubModificar">Nombre:</label>
<input type="text" id="nombreClubModificar" name="nombre" placeholder="Nombre del club" required disabled>

<br><br>

<label for="ciudadClubModificar">Ciudad:</label>
<input type="text" id="ciudadClubModificar" name="ciudad" placeholder="Ciudad" required disabled>

<br><br>

<label for="logoClubModificar">Nuevo escudo (opcional, JPG/PNG/WebP hasta 5 MB)</label>
<input class="escudo-club-form" type="file" id="logoClubModificar" name="logo" accept="image/jpeg,image/png,image/webp">
<label><input type="checkbox" name="eliminar_logo" value="1" style="width:auto"> Quitar el escudo actual</label>

<br><br>

<button type="submit">
Modificar
</button>

</form>

`;

configurarBuscadorClub("buscarClubModificar", "sugerenciasClubModificar", club => {
    const id = document.getElementById("idClubModificar");
    const nombre = document.getElementById("nombreClubModificar");
    const ciudad = document.getElementById("ciudadClubModificar");
    id.value = club ? club.id : "";
    nombre.disabled = !club;
    ciudad.disabled = !club;
    nombre.value = club ? club.nombre : "";
    ciudad.value = club ? club.ciudad : "";
});

}


// Presenta el formulario para dar de baja un club por su identificador.
function eliminarClub(){

document.getElementById("contenido").innerHTML=`

<h2>Eliminar Club</h2>

<form action="eliminar_club.php" method="POST" onsubmit="return confirmarEliminacionClub()">

<label for="buscarClubEliminar">Club:</label>
<div class="buscador-club-autocompletar">
    <input type="search" id="buscarClubEliminar" placeholder="Escribe el nombre del club" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="sugerenciasClubEliminar">
    <div id="sugerenciasClubEliminar" class="sugerencias-club" role="listbox" hidden></div>
</div>
<input type="hidden" id="idClubEliminar" name="id" required>

<br><br>

<button type="submit">
Eliminar
</button>

</form>

`;

configurarBuscadorClub("buscarClubEliminar", "sugerenciasClubEliminar", club => {
    document.getElementById("idClubEliminar").value = club ? club.id : "";
});

}

function configurarBuscadorClub(inputId, sugerenciasId, alSeleccionar = () => {}){
    const buscador = document.getElementById(inputId);
    const sugerencias = document.getElementById(sugerenciasId);
    if(!buscador || !sugerencias) return;

    fetch("consultar_clubes_gestion.php")
    .then(async response => {
        const clubes = await response.json();
        if(!response.ok) throw new Error(clubes.error || "No se pudieron cargar los clubes.");
        if(!Array.isArray(clubes)) throw new Error("La respuesta de clubes no es válida.");
        let indiceActivo = -1;

        const cerrarSugerencias = () => {
            sugerencias.hidden = true;
            buscador.setAttribute("aria-expanded", "false");
            buscador.removeAttribute("aria-activedescendant");
        };

        const seleccionarClub = club => {
            buscador.value = club.nombre;
            alSeleccionar(club);
            cerrarSugerencias();
        };

        const actualizarIndiceActivo = indice => {
            const opciones = sugerencias.querySelectorAll(".sugerencia-club");
            if(!opciones.length) return;
            indiceActivo = (indice + opciones.length) % opciones.length;
            opciones.forEach((opcion, indiceOpcion) => {
                const activo = indiceOpcion === indiceActivo;
                opcion.setAttribute("aria-selected", activo ? "true" : "false");
                if(activo) buscador.setAttribute("aria-activedescendant", opcion.id);
            });
        };

        const actualizarSugerencias = limpiarSeleccion => {
            const busqueda = buscador.value.trim().toLocaleLowerCase("es");
            if(limpiarSeleccion) alSeleccionar(null);
            indiceActivo = -1;
            sugerencias.replaceChildren();

            if(!busqueda){
                cerrarSugerencias();
                return;
            }

            const resultados = clubes.filter(club =>
                `${club.nombre} ${club.ciudad}`.toLocaleLowerCase("es").includes(busqueda)
            );
            if(!resultados.length){
                const mensaje = document.createElement("div");
                mensaje.className = "sugerencia-club-sin-resultados";
                mensaje.textContent = "No se encontraron clubes";
                sugerencias.appendChild(mensaje);
            } else {
                resultados.forEach((club, indice) => {
                    const opcion = document.createElement("button");
                    opcion.type = "button";
                    opcion.id = `${sugerenciasId}-opcion-${indice}`;
                    opcion.className = "sugerencia-club";
                    opcion.setAttribute("role", "option");
                    opcion.setAttribute("aria-selected", "false");
                    opcion.textContent = `${club.nombre} — ${club.ciudad}`;
                    opcion.addEventListener("mousedown", evento => evento.preventDefault());
                    opcion.addEventListener("click", () => seleccionarClub(club));
                    sugerencias.appendChild(opcion);
                });
            }

            sugerencias.hidden = false;
            buscador.setAttribute("aria-expanded", "true");
        };

        buscador.addEventListener("input", () => actualizarSugerencias(true));
        buscador.addEventListener("keydown", evento => {
            const opciones = sugerencias.querySelectorAll(".sugerencia-club");
            if(evento.key === "ArrowDown" && opciones.length){
                evento.preventDefault();
                actualizarIndiceActivo(indiceActivo + 1);
            } else if(evento.key === "ArrowUp" && opciones.length){
                evento.preventDefault();
                actualizarIndiceActivo(indiceActivo < 0 ? opciones.length - 1 : indiceActivo - 1);
            } else if(evento.key === "Enter" && indiceActivo >= 0 && opciones[indiceActivo]){
                evento.preventDefault();
                opciones[indiceActivo].click();
            } else if(evento.key === "Escape"){
                cerrarSugerencias();
            }
        });
        document.addEventListener("click", evento => {
            if(!buscador.parentElement.contains(evento.target)){
                cerrarSugerencias();
            }
        });
    })
    .catch(error => {
        console.error(error);
        sugerencias.replaceChildren();
        const mensaje = document.createElement("div");
        mensaje.className = "sugerencia-club-sin-resultados";
        mensaje.textContent = error.message;
        sugerencias.appendChild(mensaje);
        sugerencias.hidden = false;
    });
}

function confirmarEliminacionClub(){
    if(!document.getElementById("idClubEliminar").value){
        window.alert("Escribe y selecciona un club de la lista de sugerencias.");
        return false;
    }
    const nombreClub = document.getElementById("buscarClubEliminar").value || "este club";
    return window.confirm(`¿Seguro que quieres eliminar ${nombreClub}?`);
}

function configurarBuscadorJugador(inputId, sugerenciasId, alSeleccionar = () => {}){
    const buscador = document.getElementById(inputId);
    const sugerencias = document.getElementById(sugerenciasId);
    if(!buscador || !sugerencias) return;

    fetch("consultar_jugadores_gestion.php")
    .then(async response => {
        const jugadores = await response.json();
        if(!response.ok) throw new Error(jugadores.error || "No se pudieron cargar los jugadores.");
        if(!Array.isArray(jugadores)) throw new Error("La respuesta de jugadores no es válida.");
        let indiceActivo = -1;

        const cerrarSugerencias = () => {
            sugerencias.hidden = true;
            buscador.setAttribute("aria-expanded", "false");
            buscador.removeAttribute("aria-activedescendant");
        };

        const seleccionarJugador = jugador => {
            buscador.value = `${jugador.apellido}, ${jugador.nombre}`;
            alSeleccionar(jugador);
            cerrarSugerencias();
        };

        const actualizarIndiceActivo = indice => {
            const opciones = sugerencias.querySelectorAll(".sugerencia-club");
            if(!opciones.length) return;
            indiceActivo = (indice + opciones.length) % opciones.length;
            opciones.forEach((opcion, indiceOpcion) => {
                const activo = indiceOpcion === indiceActivo;
                opcion.setAttribute("aria-selected", activo ? "true" : "false");
                if(activo) buscador.setAttribute("aria-activedescendant", opcion.id);
            });
        };

        const actualizarSugerencias = () => {
            const busqueda = buscador.value.trim().toLocaleLowerCase("es");
            alSeleccionar(null);
            indiceActivo = -1;
            sugerencias.replaceChildren();

            if(!busqueda){
                cerrarSugerencias();
                return;
            }

            const resultados = jugadores.filter(jugador =>
                `${jugador.nombre} ${jugador.apellido} ${jugador.apellido} ${jugador.nombre} ${jugador.cedula} ${jugador.club} ${jugador.categoria}`
                    .toLocaleLowerCase("es")
                    .includes(busqueda)
            );
            if(!resultados.length){
                const mensaje = document.createElement("div");
                mensaje.className = "sugerencia-club-sin-resultados";
                mensaje.textContent = "No se encontraron jugadores";
                sugerencias.appendChild(mensaje);
            } else {
                resultados.forEach((jugador, indice) => {
                    const opcion = document.createElement("button");
                    opcion.type = "button";
                    opcion.id = `${sugerenciasId}-opcion-${indice}`;
                    opcion.className = "sugerencia-club";
                    opcion.setAttribute("role", "option");
                    opcion.setAttribute("aria-selected", "false");
                    opcion.textContent = `${jugador.apellido}, ${jugador.nombre} — ${jugador.cedula} · ${jugador.club} · ${jugador.categoria}`;
                    opcion.addEventListener("mousedown", evento => evento.preventDefault());
                    opcion.addEventListener("click", () => seleccionarJugador(jugador));
                    sugerencias.appendChild(opcion);
                });
            }

            sugerencias.hidden = false;
            buscador.setAttribute("aria-expanded", "true");
        };

        buscador.addEventListener("input", actualizarSugerencias);
        buscador.addEventListener("keydown", evento => {
            const opciones = sugerencias.querySelectorAll(".sugerencia-club");
            if(evento.key === "ArrowDown" && opciones.length){
                evento.preventDefault();
                actualizarIndiceActivo(indiceActivo + 1);
            } else if(evento.key === "ArrowUp" && opciones.length){
                evento.preventDefault();
                actualizarIndiceActivo(indiceActivo < 0 ? opciones.length - 1 : indiceActivo - 1);
            } else if(evento.key === "Enter" && indiceActivo >= 0 && opciones[indiceActivo]){
                evento.preventDefault();
                opciones[indiceActivo].click();
            } else if(evento.key === "Escape"){
                cerrarSugerencias();
            }
        });
        document.addEventListener("click", evento => {
            if(!buscador.parentElement.contains(evento.target)){
                cerrarSugerencias();
            }
        });
    })
    .catch(error => {
        console.error(error);
        sugerencias.replaceChildren();
        const mensaje = document.createElement("div");
        mensaje.className = "sugerencia-club-sin-resultados";
        mensaje.textContent = error.message;
        sugerencias.appendChild(mensaje);
        sugerencias.hidden = false;
    });
}

function validarSeleccionClubModificar(){
    if(!document.getElementById("idClubModificar").value){
        window.alert("Escribe y selecciona un club de la lista de sugerencias.");
        return false;
    }
    const nombreClub = document.getElementById("buscarClubModificar").value;
    return window.confirm(`¿Confirmas que quieres modificar los datos de ${nombreClub}?`);
}

function confirmarCambioJugador(formulario, accion){
    const cedula = formulario.elements.cedula.value.trim();
    if(!cedula){
        window.alert("Escribe y selecciona un jugador de la lista de sugerencias.");
        return false;
    }

    const nombreJugador = accion === "modificar"
        ? `${document.getElementById("apellidoJugadorModificar").value}, ${document.getElementById("nombreJugadorModificar").value}`
        : document.getElementById("buscarJugadorEliminar").value;
    const mensaje = accion === "eliminar"
        ? `¿Seguro que quieres eliminar a ${nombreJugador} (cédula ${cedula})? Esta acción no se puede deshacer.`
        : `¿Confirmas que quieres modificar los datos de ${nombreJugador} (cédula ${cedula})?`;
    return window.confirm(mensaje);
}

</script>

</body>

</html>
