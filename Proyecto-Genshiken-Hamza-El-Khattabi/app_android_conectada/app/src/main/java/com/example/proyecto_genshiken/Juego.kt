package com.example.proyecto_genshiken

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import coil.compose.AsyncImage
import kotlinx.coroutines.delay

/*
--------------------------------------------------
Pantalla del juego
--------------------------------------------------

Esta pantalla ya queda conectada al backend PHP.

Antes:
- usaba preguntas locales de PreguntasJuego.kt

Ahora:
- carga preguntas desde getPreguntas.php
- muestra respuestas guardadas en MySQL
- carga imágenes desde WEB_genshi/img/
- guarda la puntuación en guardarPuntuacion.php

Con esto, las preguntas creadas desde el panel admin
pueden aparecer directamente en la app Android.
*/
@Composable
fun Juego(navController: NavHostController) {

    var nivel by remember { mutableStateOf(1) }

    var preguntas by remember { mutableStateOf<List<PreguntaApi>>(emptyList()) }
    var cargando by remember { mutableStateOf(true) }
    var errorCarga by remember { mutableStateOf("") }

    var numeroPregunta by remember { mutableStateOf(0) }
    var puntuacion by remember { mutableStateOf(0) }
    var respuestasCorrectas by remember { mutableStateOf(0) }
    var respuestaElegida by remember { mutableStateOf<Int?>(null) }
    var mensajeResultado by remember { mutableStateOf("") }

    var tiempoTotal by remember { mutableStateOf(0) }
    var guardandoPuntuacion by remember { mutableStateOf(false) }

    val estadoRespuesta = remember {
        mutableStateListOf<EstadoRespuesta>()
    }

    /*
    --------------------------------------------------
    Cargar preguntas desde PHP
    --------------------------------------------------

    Cada vez que cambia el nivel, se pide a la API:
    getPreguntas.php?nivel=1
    */
    LaunchedEffect(nivel) {
        cargando = true
        errorCarga = ""

        UserRepository.getPreguntas(nivel) { ok, lista ->
            if (ok && lista.isNotEmpty()) {
                preguntas = lista
                numeroPregunta = 0
                puntuacion = 0
                respuestasCorrectas = 0
                respuestaElegida = null
                mensajeResultado = ""
                tiempoTotal = 0

                estadoRespuesta.clear()
                repeat(lista.size) { index ->
                    if (index == 0) {
                        estadoRespuesta.add(EstadoRespuesta.CURRENT)
                    } else {
                        estadoRespuesta.add(EstadoRespuesta.PENDING)
                    }
                }

                cargando = false
            } else {
                preguntas = emptyList()
                errorCarga = "No se pudieron cargar preguntas desde el backend."
                cargando = false
            }
        }
    }

    /*
    --------------------------------------------------
    Temporizador del juego
    --------------------------------------------------
    */
    LaunchedEffect(Unit) {
        while (true) {
            delay(1000)

            if (!cargando && preguntas.isNotEmpty() && !guardandoPuntuacion) {
                tiempoTotal++
            }
        }
    }

    /*
    --------------------------------------------------
    Estados de carga
    --------------------------------------------------
    */
    if (cargando) {
        PantallaEstadoJuego("Cargando preguntas desde MySQL...")
        return
    }

    if (errorCarga.isNotEmpty()) {
        PantallaEstadoJuego(errorCarga)
        return
    }

    val pregunta = preguntas.getOrNull(numeroPregunta)

    if (pregunta == null) {
        PantallaEstadoJuego("No hay pregunta disponible.")
        return
    }

    val respuestas = pregunta.respuestas.take(4)
    val indiceCorrecto = respuestas.indexOfFirst { it.correcta == 1 }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
            .verticalScroll(rememberScrollState())
            .padding(bottom = 24.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Spacer(Modifier.height(40.dp))

        HeaderJuego(
            nivel = nivel,
            tiempo = tiempoTotal,
            puntuacion = puntuacion
        )

        Spacer(Modifier.height(35.dp))

        /*
        --------------------------------------------------
        Imagen de la pregunta
        --------------------------------------------------
        */
        AsyncImage(
            model = RetrofitClient.getImageUrl(pregunta.imagen),
            contentDescription = "Imagen de la pregunta",
            contentScale = ContentScale.Fit,
            modifier = Modifier.size(220.dp)
        )

        Spacer(Modifier.height(45.dp))

        Text(
            text = pregunta.pregunta,
            fontSize = 20.sp
        )

        Spacer(Modifier.height(35.dp))

        /*
        --------------------------------------------------
        Respuestas
        --------------------------------------------------
        */
        OpcionesApi(
            respuestas = respuestas,
            respuestaElegida = respuestaElegida,
            indiceCorrecto = indiceCorrecto,
            onClick = { index ->

                if (respuestaElegida == null) {
                    respuestaElegida = index

                    if (index == indiceCorrecto) {
                        puntuacion += 1000
                        respuestasCorrectas++
                        mensajeResultado = "Correcta"
                        estadoRespuesta[numeroPregunta] = EstadoRespuesta.CORRECT
                    } else {
                        puntuacion -= 200
                        mensajeResultado = "Incorrecta"
                        estadoRespuesta[numeroPregunta] = EstadoRespuesta.WRONG
                    }
                }
            }
        )

        Spacer(Modifier.height(18.dp))

        if (mensajeResultado.isNotEmpty()) {
            Text(
                text = mensajeResultado,
                color = if (mensajeResultado == "Correcta") Color(0xFF008000) else Color.Red,
                fontWeight = FontWeight.Bold,
                fontSize = 18.sp
            )
        }

        Spacer(Modifier.height(30.dp))

        QuestionProgressApi(
            states = estadoRespuesta
        )

        Spacer(Modifier.height(35.dp))

        /*
        --------------------------------------------------
        Finalizar o pasar a la siguiente pregunta
        --------------------------------------------------
        */
        if (numeroPregunta == preguntas.lastIndex) {
            Button(
                enabled = respuestaElegida != null && !guardandoPuntuacion,
                onClick = {
                    guardandoPuntuacion = true

                    UserRepository.saveScore(
                        usuarioId = UserSession.userId,
                        puntuacion = puntuacion,
                        tiempo = tiempoTotal
                    ) { _, _ ->
                        guardandoPuntuacion = false
                        navController.navigate("Ranking")
                    }
                },
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFF6B4CB3)
                )
            ) {
                Text(
                    text = if (guardandoPuntuacion) "Guardando..." else "Finalizar partida",
                    color = Color.White
                )
            }
        } else {
            Button(
                enabled = respuestaElegida != null,
                onClick = {
                    numeroPregunta++
                    respuestaElegida = null
                    mensajeResultado = ""

                    if (numeroPregunta < estadoRespuesta.size) {
                        estadoRespuesta[numeroPregunta] = EstadoRespuesta.CURRENT
                    }
                },
                colors = ButtonDefaults.buttonColors(
                    containerColor = Color(0xFF6B4CB3)
                )
            ) {
                Text(
                    text = "Siguiente",
                    color = Color.White
                )
            }
        }
    }
}

/*
--------------------------------------------------
Cabecera del juego
--------------------------------------------------
*/
@Composable
fun HeaderJuego(
    nivel: Int,
    tiempo: Int,
    puntuacion: Int
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text("Nivel $nivel")
        Text("Tiempo $tiempo")

        Column {
            Text("Puntuación")
            Text("$puntuacion")
        }
    }
}

/*
--------------------------------------------------
Opciones de respuesta recibidas desde MySQL
--------------------------------------------------
*/
@Composable
fun OpcionesApi(
    respuestas: List<RespuestaApi>,
    respuestaElegida: Int?,
    indiceCorrecto: Int,
    onClick: (Int) -> Unit
) {
    val coloresBase = listOf(
        Color.Red,
        Color.Yellow,
        Color.Cyan,
        Color.Green
    )

    Column(
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        respuestas.chunked(2).forEachIndexed { filaIndex, fila ->

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                fila.forEachIndexed { columnaIndex, respuesta ->

                    val index = filaIndex * 2 + columnaIndex

                    val colorBoton = when {
                        respuestaElegida == null -> coloresBase[index % coloresBase.size]
                        index == indiceCorrecto -> Color.Green
                        index == respuestaElegida && index != indiceCorrecto -> Color.Red
                        else -> Color.LightGray
                    }

                    BotonRespuestaApi(
                        text = respuesta.texto?.takeIf { it.isNotBlank() } ?: "Respuesta",
                        color = colorBoton,
                        enabled = respuestaElegida == null,
                        modifier = Modifier.weight(1f)
                    ) {
                        onClick(index)
                    }
                }

                if (fila.size == 1) {
                    Spacer(modifier = Modifier.weight(1f))
                }
            }
        }
    }
}

/*
--------------------------------------------------
Botón de respuesta
--------------------------------------------------
*/
@Composable
fun BotonRespuestaApi(
    text: String,
    color: Color,
    enabled: Boolean,
    modifier: Modifier = Modifier,
    onClick: () -> Unit
) {
    Button(
        onClick = onClick,
        enabled = enabled,
        modifier = modifier.height(60.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = color,
            disabledContainerColor = color,
            contentColor = Color.Black,
            disabledContentColor = Color.Black
        )
    ) {
        Text(
            text = text,
            fontSize = 16.sp,
            fontWeight = FontWeight.Bold,
            color = Color.Black
        )
    }
}

/*
--------------------------------------------------
Progreso de preguntas
--------------------------------------------------
*/
@Composable
fun QuestionProgressApi(
    states: List<EstadoRespuesta>
) {
    Row {
        states.forEachIndexed { index, state ->

            val color = when (state) {
                EstadoRespuesta.CORRECT -> Color.Green
                EstadoRespuesta.WRONG -> Color.Red
                EstadoRespuesta.CURRENT -> Color(0xFFFFA500)
                EstadoRespuesta.PENDING -> Color.LightGray
            }

            Box(
                modifier = Modifier
                    .size(30.dp)
                    .background(color)
                    .border(1.dp, Color.Black),
                contentAlignment = Alignment.Center
            ) {
                Text("${index + 1}")
            }

            Spacer(Modifier.width(4.dp))
        }
    }
}

/*
--------------------------------------------------
Pantalla simple de estado
--------------------------------------------------
*/
@Composable
fun PantallaEstadoJuego(
    mensaje: String
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = mensaje,
            fontSize = 18.sp,
            fontWeight = FontWeight.Bold
        )
    }
}