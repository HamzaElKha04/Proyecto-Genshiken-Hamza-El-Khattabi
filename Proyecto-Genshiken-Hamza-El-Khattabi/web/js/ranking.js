/*
--------------------------------------------------
Lógica del ranking (Frontend)
--------------------------------------------------

Este archivo:
- conecta con la API getRanking.php
- obtiene el ranking
- muestra el TOP 10 en pantalla
*/

const estado = document.getElementById("estado");
const rankingContenido = document.getElementById("ranking-contenido");
const rankingBody = document.getElementById("ranking-body");

function escaparHTML(texto) {
    const div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
}

async function cargarRanking() {
    try {
        const res = await fetch("api/getRanking.php");

        if (!res.ok) {
            throw new Error("No se pudo cargar la API del ranking");
        }

        const data = await res.json();

        if (!Array.isArray(data)) {
            throw new Error("La API no devolvió un array válido");
        }

        if (data.length === 0) {
            estado.textContent = "No hay puntuaciones registradas.";
            return;
        }

        rankingBody.innerHTML = "";

        data.forEach(jugador => {
            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td>#${jugador.posicion}</td>
                <td>${escaparHTML(jugador.usuario)}</td>
                <td>${jugador.puntos}</td>
                <td>${jugador.tiempo}s</td>
            `;

            rankingBody.appendChild(fila);
        });

        estado.style.display = "none";
        rankingContenido.style.display = "block";

    } catch (error) {
        estado.textContent = "Error al cargar el ranking.";
        console.error(error);
    }
}

cargarRanking();