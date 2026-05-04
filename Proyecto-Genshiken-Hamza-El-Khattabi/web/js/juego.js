/*
--------------------------------------------------
Lógica del juego (Frontend)
--------------------------------------------------

Este archivo gestiona la lógica principal del juego
en el navegador.

Se encarga de:
- mostrar una pantalla inicial
- recoger el nombre del jugador
- empezar la partida solo al pulsar JUGAR
- cargar preguntas dinámicamente
- mostrar preguntas y respuestas
- gestionar la interacción del usuario
- avanzar entre preguntas
- calcular la puntuación
- controlar fallos
- medir el tiempo
- guardar la puntuación final en la base de datos
- mostrar la posición final del jugador en el ranking
*/

const pantallaInicio = document.getElementById("pantalla-inicio");
const panelJuego = document.getElementById("panel-juego");
const nombreJugadorInput = document.getElementById("nombre-jugador");
const btnJugar = document.getElementById("btn-jugar");
const errorNombre = document.getElementById("error-nombre");

const estado = document.getElementById("estado");
const preguntaBox = document.getElementById("pregunta-box");
const nivelTexto = document.getElementById("nivel-texto");
const contadorTexto = document.getElementById("contador-texto");
const preguntaTexto = document.getElementById("pregunta-texto");
const preguntaImagen = document.getElementById("pregunta-imagen");
const respuestasBox = document.getElementById("respuestas-box");
const resultado = document.getElementById("resultado");
const btnSiguiente = document.getElementById("btn-siguiente");

const jugadorTexto = document.getElementById("jugador-texto");
const puntosTexto = document.getElementById("puntos-texto");
const tiempoTexto = document.getElementById("tiempo-texto");
const fallosTexto = document.getElementById("fallos-texto");

let preguntas = [];
let preguntaActual = 0;
let respuestaSeleccionada = false;

let puntuacion = 0;
let fallos = 0;

let tiempo = 0;
let intervaloTiempo = null;
let puntuacionGuardada = false;
let juegoIniciado = false;
let nombreJugador = "";

/* ========================= */
/* UTILIDADES */
/* ========================= */

function limpiarTexto(texto) {
    return texto.replace(/\s+/g, " ").trim();
}

function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

function reiniciarEstadoJuego() {
    preguntas = [];
    preguntaActual = 0;
    respuestaSeleccionada = false;
    puntuacion = 0;
    fallos = 0;
    tiempo = 0;
    puntuacionGuardada = false;

    puntosTexto.textContent = "Puntos: 0";
    tiempoTexto.textContent = "Tiempo: 0s";
    fallosTexto.textContent = "Fallos: 0 / 5";

    resultado.textContent = "";
    respuestasBox.innerHTML = "";
    preguntaBox.style.display = "none";
    estado.style.display = "block";
    estado.textContent = "Cargando preguntas...";
    btnSiguiente.style.display = "none";
}

/* ========================= */
/* TEMPORIZADOR */
/* ========================= */

function iniciarTiempo() {
    pararTiempo();

    intervaloTiempo = setInterval(() => {
        tiempo++;
        tiempoTexto.textContent = "Tiempo: " + tiempo + "s";
    }, 1000);
}

function pararTiempo() {
    if (intervaloTiempo !== null) {
        clearInterval(intervaloTiempo);
        intervaloTiempo = null;
    }
}

/* ========================= */
/* INICIO DEL JUEGO */
/* ========================= */

function iniciarPartida() {
    const nombre = limpiarTexto(nombreJugadorInput.value);

    if (nombre.length < 2) {
        errorNombre.textContent = "Debes introducir un nombre válido de al menos 2 caracteres.";
        errorNombre.style.display = "block";
        nombreJugadorInput.focus();
        return;
    }

    errorNombre.style.display = "none";
    nombreJugador = nombre;
    juegoIniciado = true;

    pantallaInicio.style.display = "none";
    panelJuego.style.display = "block";
    jugadorTexto.textContent = "Jugador: " + nombreJugador;

    reiniciarEstadoJuego();
    cargarPreguntas();
}

btnJugar.addEventListener("click", iniciarPartida);

nombreJugadorInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
        e.preventDefault();
        iniciarPartida();
    }
});

/* ========================= */
/* CARGAR PREGUNTAS */
/* ========================= */

async function cargarPreguntas() {
    try {
        const res = await fetch("api/getPreguntas.php?nivel=1");

        if (!res.ok) {
            throw new Error("No se pudo cargar la API");
        }

        const datos = await res.json();

        if (!Array.isArray(datos)) {
            throw new Error("La API no devolvió una lista válida de preguntas");
        }

        preguntas = datos;

        if (preguntas.length === 0) {
            estado.textContent = "No hay preguntas disponibles.";
            return;
        }

        estado.style.display = "none";
        preguntaBox.style.display = "block";

        iniciarTiempo();
        mostrarPregunta();

    } catch (error) {
        estado.textContent = "Error al cargar las preguntas.";
        console.error(error);
    }
}

/* ========================= */
/* MOSTRAR PREGUNTA */
/* ========================= */

function mostrarPregunta() {
    const p = preguntas[preguntaActual];

    respuestaSeleccionada = false;
    resultado.textContent = "";
    btnSiguiente.style.display = "none";
    btnSiguiente.disabled = false;
    respuestasBox.innerHTML = "";

    nivelTexto.textContent = "Nivel 1";
    contadorTexto.textContent = `Pregunta ${preguntaActual + 1} de ${preguntas.length}`;
    preguntaTexto.textContent = p.pregunta;

    if (p.imagen && p.imagen.trim() !== "") {
        preguntaImagen.src = "img/" + p.imagen;
        preguntaImagen.style.display = "block";
    } else {
        preguntaImagen.style.display = "none";
        preguntaImagen.src = "";
    }

    p.respuestas.forEach((r, index) => {
        const btn = document.createElement("button");
        btn.classList.add("respuesta-btn");
        btn.type = "button";

        if (r.texto && r.texto.trim() !== "") {
            btn.textContent = r.texto;
        } else {
            btn.textContent = "Respuesta";
        }

        btn.addEventListener("click", function () {
            if (!respuestaSeleccionada) {
                comprobarRespuesta(index);
            }
        });

        respuestasBox.appendChild(btn);
    });
}

/* ========================= */
/* COMPROBAR RESPUESTA */
/* ========================= */

function comprobarRespuesta(indexSeleccionado) {
    respuestaSeleccionada = true;

    const botonesRespuestas = document.querySelectorAll(".respuesta-btn");
    const respuestas = preguntas[preguntaActual].respuestas;

    botonesRespuestas.forEach((boton, i) => {
        boton.disabled = true;

        if (parseInt(respuestas[i].correcta) === 1) {
            boton.classList.add("respuesta-correcta");
        }
    });

    if (parseInt(respuestas[indexSeleccionado].correcta) === 1) {
        resultado.textContent = "Correcta";
        resultado.style.color = "green";
        puntuacion += 100;
    } else {
        botonesRespuestas[indexSeleccionado].classList.add("respuesta-incorrecta");
        resultado.textContent = "Incorrecta";
        resultado.style.color = "red";
        fallos++;
    }

    puntosTexto.textContent = "Puntos: " + puntuacion;
    fallosTexto.textContent = "Fallos: " + fallos + " / 5";

    if (fallos >= 5) {
        finalizarJuego(false);
        return;
    }

    btnSiguiente.style.display = "inline-block";
}

/* ========================= */
/* SIGUIENTE PREGUNTA */
/* ========================= */

btnSiguiente.addEventListener("click", function () {
    preguntaActual++;

    if (preguntaActual < preguntas.length) {
        mostrarPregunta();
    } else {
        finalizarJuego(true);
    }
});

/* ========================= */
/* GUARDAR PUNTUACIÓN */
/* ========================= */

async function guardarPuntuacion() {
    if (puntuacionGuardada) {
        return {
            ok: true,
            mensaje: "La puntuación ya se había guardado.",
            posicion: 0,
            top3: false
        };
    }

    try {
        const respuesta = await fetch("api/guardarPuntuacion.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre: nombreJugador,
                puntos: puntuacion,
                tiempo: tiempo
            })
        });

        const data = await respuesta.json();

        if (data.ok) {
            puntuacionGuardada = true;
        }

        return data;
    } catch (error) {
        console.error("Error al guardar la puntuación:", error);
        return {
            ok: false,
            mensaje: "Error al guardar la puntuación.",
            posicion: 0,
            top3: false
        };
    }
}

/* ========================= */
/* FINALIZAR JUEGO */
/* ========================= */

async function finalizarJuego(completado) {
    pararTiempo();

    let mensaje = "";
    let bloqueTop = "";
    let bloquePosicion = "";
    let bloqueGuardado = "";

    if (completado) {
        mensaje = "Juego terminado";
    } else {
        mensaje = "Has perdido (demasiados fallos)";
    }

    const respuestaGuardado = await guardarPuntuacion();

    if (respuestaGuardado.ok && respuestaGuardado.posicion > 0) {
        bloquePosicion = `<p>Has quedado en la posición: <strong>#${respuestaGuardado.posicion}</strong></p>`;
    }

    if (respuestaGuardado.ok && respuestaGuardado.top3) {
        bloqueTop = `<p class="mensaje-top">¡Enhorabuena! Has entrado en el TOP 3</p>`;
    }

    if (!respuestaGuardado.ok) {
        bloqueGuardado = `<p class="mensaje-error-final">No se pudo guardar la puntuación.</p>`;
    }

    preguntaBox.innerHTML = `
        <div class="estado estado-final">
            <h2>${mensaje}</h2>
            <p>Jugador: ${escaparHTML(nombreJugador)}</p>
            <p>Puntos: ${puntuacion}</p>
            <p>Tiempo: ${tiempo}s</p>
            ${bloquePosicion}
            ${bloqueTop}
            ${bloqueGuardado}

            <div class="acciones-juego">
                <button class="btn-siguiente" onclick="location.reload()">Volver a jugar</button>
            </div>

            <div class="acciones-juego">
                <button class="btn-siguiente" onclick="window.location.href='ranking.html'">Ver Ranking</button>
            </div>
        </div>
    `;
}