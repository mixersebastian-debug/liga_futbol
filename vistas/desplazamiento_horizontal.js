function activarDesplazamientoHorizontal(documento){
    if(!documento || !documento.body || documento.documentElement.dataset.barrasHorizontales === "activas") return;
    documento.documentElement.dataset.barrasHorizontales = "activas";

    const instalar = () => {
        documento.querySelectorAll("table").forEach(tabla => {
            let contenedor = tabla.closest("[data-liga-scroll-wrapper]");
            let desplazable = contenedor
                ? contenedor.querySelector("[data-liga-scroll-content]")
                : null;

            if(!desplazable){
                let ancestro = tabla.parentElement;
                while(ancestro && ancestro !== documento.body){
                    const overflow = documento.defaultView.getComputedStyle(ancestro).overflowX;
                    if(overflow === "auto" || overflow === "scroll") break;
                    ancestro = ancestro.parentElement;
                }

                if(ancestro && ancestro !== documento.body){
                    contenedor = ancestro;
                }else{
                    const padre = tabla.parentElement;
                    if(!padre || tabla.scrollWidth <= padre.clientWidth + 1) return;
                    contenedor = documento.createElement("div");
                    tabla.before(contenedor);
                    contenedor.appendChild(tabla);
                }

                contenedor.dataset.ligaScrollWrapper = "true";
                contenedor.style.overflowX = "visible";
                contenedor.style.overflowY = "visible";
                contenedor.style.width = "100%";
                contenedor.style.maxWidth = "100%";
                contenedor.style.boxSizing = "border-box";

                const barra = documento.createElement("div");
                barra.dataset.ligaScrollTop = "true";
                barra.setAttribute("role", "region");
                barra.setAttribute("aria-label", "Desplazamiento horizontal de la tabla");
                barra.tabIndex = 0;
                barra.style.width = "100%";
                barra.style.height = "18px";
                barra.style.marginBottom = "4px";
                barra.style.overflowX = "auto";
                barra.style.overflowY = "hidden";
                barra.style.boxSizing = "border-box";
                barra.style.background = documento.body.classList.contains("modo-claro") ? "#dce6f0" : "#171e28";
                barra.style.borderRadius = "3px";

                const espacio = documento.createElement("div");
                espacio.style.height = "1px";
                barra.appendChild(espacio);

                desplazable = documento.createElement("div");
                desplazable.dataset.ligaScrollContent = "true";
                desplazable.style.width = "100%";
                desplazable.style.overflowX = "auto";
                desplazable.style.overflowY = "hidden";
                desplazable.style.boxSizing = "border-box";

                if(tabla.parentElement !== desplazable){
                    tabla.before(desplazable);
                    desplazable.appendChild(tabla);
                }
                contenedor.prepend(barra);
                if(!contenedor.contains(desplazable)){
                    contenedor.appendChild(desplazable);
                }

                let sincronizando = false;
                const sincronizar = (origen, destino) => {
                    if(sincronizando) return;
                    sincronizando = true;
                    destino.scrollLeft = origen.scrollLeft;
                    sincronizando = false;
                };
                barra.addEventListener("scroll", () => sincronizar(barra, desplazable), {passive: true});
                desplazable.addEventListener("scroll", () => sincronizar(desplazable, barra), {passive: true});

                const actualizarBarra = () => {
                    const anchoContenido = Math.max(desplazable.clientWidth, desplazable.scrollWidth, tabla.scrollWidth);
                    espacio.style.width = `${anchoContenido}px`;
                    barra.hidden = anchoContenido <= desplazable.clientWidth + 1;
                };
                actualizarBarra();

                if(typeof documento.defaultView.ResizeObserver === "function"){
                    const observadorTamano = new documento.defaultView.ResizeObserver(actualizarBarra);
                    observadorTamano.observe(desplazable);
                    observadorTamano.observe(tabla);
                }
            }
        });
    };

    instalar();
    const observador = new documento.defaultView.MutationObserver(instalar);
    observador.observe(documento.body, {childList: true, subtree: true});
    documento.defaultView.addEventListener("resize", instalar);
}
