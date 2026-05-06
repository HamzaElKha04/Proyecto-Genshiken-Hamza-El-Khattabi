package com.example.proyecto_genshiken
/*
--------------------------------------------------
Modelos de datos de la app
--------------------------------------------------

Este archivo contiene los modelos que usa Android
para entender los JSON que llegan desde PHP.

Los nombres de las propiedades deben coincidir con
lo que devuelven tus APIs.
*/

/*
--------------------------------------------------
Modelo usado por la pantalla Ranking
--------------------------------------------------
*/
data class Player(
    val nombre: String,
    val puntuacion: Int,
    val posicion: Int = 0,
    val tiempo: Int = 0,
    val fecha: String = ""
)

/*
--------------------------------------------------
Pregunta recibida desde getPreguntas.php
--------------------------------------------------

Ejemplo JSON:
{
  "id": 1,
  "nivel_id": 1,
  "pregunta": "¿A quién pertenece esta espada?",
  "imagen": "nivel1/espada.png",
  "respuestas": [...]
}
*/
data class PreguntaApi(
    val id: Int,
    val nivel_id: Int,
    val pregunta: String,
    val imagen: String?,
    val respuestas: List<RespuestaApi>
)

/*
--------------------------------------------------
Respuesta de cada pregunta
--------------------------------------------------
*/
data class RespuestaApi(
    val texto: String?,
    val imagen: String?,
    val correcta: Int
)

/*
--------------------------------------------------
Jugador recibido desde getRanking.php
--------------------------------------------------
*/
data class RankingApiResponse(
    val posicion: Int,
    val usuario: String,
    val puntos: Int,
    val tiempo: Int,
    val fecha: String?
)

/*
--------------------------------------------------
Datos enviados a guardarPuntuacion.php
--------------------------------------------------
*/
data class GuardarPuntuacionRequest(
    val nombre: String,
    val puntos: Int,
    val tiempo: Int
)

/*
--------------------------------------------------
Respuesta recibida desde guardarPuntuacion.php
--------------------------------------------------
*/
data class GuardarPuntuacionResponse(
    val ok: Boolean,
    val mensaje: String,
    val posicion: Int,
    val top3: Boolean
)

/*
--------------------------------------------------
Datos enviados a registrarDescarga.php
--------------------------------------------------
*/
data class RegistrarDescargaRequest(
    val usuario_id: Int?,
    val nombre_usuario: String,
    val dispositivo: String,
    val version_app: String
)

/*
--------------------------------------------------
Respuesta simple de APIs
--------------------------------------------------
*/
data class ApiSimpleResponse(
    val ok: Boolean,
    val mensaje: String
)