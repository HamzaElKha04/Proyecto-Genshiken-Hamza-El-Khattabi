package com.example.proyecto_genshiken

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.core.tween
import androidx.compose.animation.togetherWith
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
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.Surface
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
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import android.media.MediaPlayer

import androidx.navigation.NavHostController
import coil.compose.AsyncImage
import kotlinx.coroutines.delay
import androidx.compose.runtime.DisposableEffect
import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.content.Context
import androidx.compose.ui.platform.LocalContext

@Composable
fun Juego(navController: NavHostController) {



    var nivel by remember { mutableStateOf(1) }

    var numeroPregunta by remember {
        mutableStateOf(0)
    }
    var puntuacionTotal by remember {
        mutableStateOf(0)
    }

    var puntuacionNivel by remember {
        mutableStateOf(0)
    }

    var respuestaCorrecta by remember {
        mutableStateOf(0)
    }

    var respuestaElegida by remember {
        mutableStateOf<Int?>(null)
    }

    val estadoRespuesta = remember {
        mutableStateListOf<EstadoRespuesta>()
    }

    var tiempoTotal by remember {
        mutableStateOf(0)
    }

    var tiempoNivel by remember {
        mutableStateOf(0)
    }

    var preguntas by remember {
        mutableStateOf<List<Preguntas>>(emptyList())
    }

    var cargando by remember {
        mutableStateOf(true)
    }
    var finContenido by remember {
        mutableStateOf(false)
    }
    var mostrandoTransicion by remember {
        mutableStateOf(false)
    }

    var mensajeTransicion by remember {
        mutableStateOf("")
    }
    val context = LocalContext.current

    fun finalizarNivel() {

        val puntuacionNivelFinal =
            puntuacionNivel +
                    ((600 - tiempoNivel).coerceAtLeast(0)) * 10

        puntuacionTotal += puntuacionNivelFinal

        mostrandoTransicion = true

        if (respuestaCorrecta >= 5) {

            val siguienteNivel = nivel + 1

            UserRepository.obtenerPreguntas(
                siguienteNivel
            ) { lista ->

                if (lista.isNullOrEmpty()) {

                    mensajeTransicion =
                        " Has completado el último nivel disponible.\n\nRedirigiendo al ranking..."

                    GameSession.lastScore = puntuacionTotal
                    GameSession.lastTime = tiempoTotal

                    UserRepository.saveScore(
                        UserSession.userId,
                        puntuacionTotal,
                        tiempoTotal
                    ) {}

                    if (GameMode.esCompetitivo) {

                        val monedasGanadas = puntuacionTotal / 5000

                        GachaState.monedas.value += monedasGanadas

                        UserRepository.guardarMonedas(
                            UserSession.userId,
                            GachaState.monedas.value
                        )
                    }
                } else {

                    mensajeTransicion =
                        " Nivel $nivel completado.\n\nPreparando nivel $siguienteNivel..."
                }
            }

        } else {

            mensajeTransicion =
                " No has conseguido suficientes respuestas correctas.\n\nRedirigiendo al ranking..."

            GameSession.lastScore = puntuacionTotal
            GameSession.lastTime = tiempoTotal

            if (GameMode.esCompetitivo) {

                UserRepository.saveScore(
                    UserSession.userId,
                    puntuacionTotal,
                    tiempoTotal
                ) { }

                val monedasGanadas = puntuacionTotal / 1000

                GachaState.monedas.value += monedasGanadas

                UserRepository.guardarMonedas(
                    UserSession.userId,
                    GachaState.monedas.value
                )
            }
        }
    }
    /*
    --------------------------------------------------
    Música del juego
    --------------------------------------------------

    Al entrar en la pantalla de juego se cambia a la
    música más movida. Al salir del juego, se para.
    */
    DisposableEffect(Unit) {
        MusicManager.reproducirJuego(context)

        onDispose {
            MusicManager.pararMusica()
        }
    }


    /*
    --------------------------------------------------
    CARGAR PREGUNTAS
    --------------------------------------------------
    */
    LaunchedEffect(nivel) {

        cargando = true

        UserRepository.obtenerPreguntas(nivel) { listaPreguntas ->

            cargando = false

            // NO HAY MÁS PREGUNTAS
            if (listaPreguntas.isEmpty()) {

                finContenido = true

                return@obtenerPreguntas
            }

            preguntas = listaPreguntas

            numeroPregunta = 0

            estadoRespuesta.clear()

            repeat(listaPreguntas.size) {

                estadoRespuesta.add(
                    EstadoRespuesta.PENDING
                )
            }

            estadoRespuesta[0] =
                EstadoRespuesta.CURRENT
        }
    }

    /*
    --------------------------------------------------
    TEMPORIZADOR
    --------------------------------------------------
    */
    LaunchedEffect(Unit) {

        while (true) {

            delay(1000)

            tiempoTotal++
            tiempoNivel++
        }
    }

    /*
    --------------------------------------------------
    CAMBIO AUTOMÁTICO DE PREGUNTA
    --------------------------------------------------
    */
    LaunchedEffect(respuestaElegida) {

        if (respuestaElegida != null) {

            delay(700)

            if (numeroPregunta < preguntas.size - 1) {

                numeroPregunta++

                respuestaElegida = null

                estadoRespuesta[numeroPregunta] =
                    EstadoRespuesta.CURRENT

            } else {

                finalizarNivel()
            }
        }
    }

    /*
    --------------------------------------------------
    Cargar las preguntas, ya que lleva un pequeño tiempo de conexion
    --------------------------------------------------
    */
    if (cargando) {

        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        listOf(
                            Color(0xFF0F172A),
                            Color(0xFF1E293B)
                        )
                    )
                ),
            contentAlignment = Alignment.Center
        ) {

            Column(
                horizontalAlignment =
                    Alignment.CenterHorizontally
            ) {

                Text(
                    text = "Cargando preguntas...",
                    color = Color.White,
                    fontSize = 22.sp,
                    fontWeight = FontWeight.Bold
                )

                Spacer(modifier = Modifier.height(20.dp))

                LinearProgressIndicator(
                    modifier = Modifier.width(220.dp)
                )
            }
        }

        return
    }

    /*
--------------------------------------------------
JUEGO COMPLETADO O SIN MÁS PREGUNTAS
--------------------------------------------------
*/
    if (finContenido) {

        LaunchedEffect(Unit) {

            delay(3000)

            navController.navigate("Ranking") {

                popUpTo("Juego") {
                    inclusive = true
                }
            }
        }

        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        listOf(
                            Color(0xFF0F172A),
                            Color(0xFF1E293B),
                            Color(0xFF111827)
                        )
                    )
                ),
            contentAlignment = Alignment.Center
        ) {

            Card(
                shape = RoundedCornerShape(28.dp),

                colors = CardDefaults.cardColors(
                    containerColor =
                        Color.White.copy(alpha = 0.95f)
                )
            ) {

                Column(
                    modifier = Modifier.padding(34.dp),

                    horizontalAlignment =
                        Alignment.CenterHorizontally
                ) {

                    Text(
                        text = " JUEGO COMPLETADO ",

                        fontSize = 28.sp,

                        fontWeight = FontWeight.ExtraBold,

                        color = Color(0xFF1E3A8A),

                        textAlign = TextAlign.Center
                    )

                    Spacer(modifier = Modifier.height(22.dp))

                    Text(
                        text =
                            "No hay más preguntas disponibles por ahora.",

                        fontSize = 18.sp,

                        color = Color.DarkGray,

                        textAlign = TextAlign.Center
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    Text(
                        text =
                            "Redirigiendo al ranking...",

                        fontSize = 15.sp,

                        color = Color.Gray
                    )

                    Spacer(modifier = Modifier.height(26.dp))

                    LinearProgressIndicator(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(8.dp)
                            .clip(RoundedCornerShape(20.dp))
                    )
                }
            }
        }

        return
    }
    if (mostrandoTransicion) {

        LaunchedEffect(Unit) {

            delay(2500)



            if (respuestaCorrecta >= 5) {

                val siguienteNivel = nivel + 1

                UserRepository.obtenerPreguntas(
                    siguienteNivel
                ) { lista ->

                    if (lista.isNullOrEmpty()) {

                        navController.navigate("Ranking") {

                            popUpTo("Juego") {
                                inclusive = true
                            }
                        }

                    } else {

                        nivel = siguienteNivel
                        preguntas = lista
                        numeroPregunta = 0
                        respuestaCorrecta = 0
                        respuestaElegida = null
                        puntuacionNivel = 0

                        estadoRespuesta.clear()

                        repeat(lista.size) {

                            estadoRespuesta.add(
                                EstadoRespuesta.PENDING
                            )
                        }

                        estadoRespuesta[0] =
                            EstadoRespuesta.CURRENT

                        tiempoNivel = 0

                        mostrandoTransicion = false
                    }
                }

            } else {

                navController.navigate("Ranking") {

                    popUpTo("Juego") {
                        inclusive = true
                    }
                }
            }
        }

        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        listOf(
                            Color(0xFF0F172A),
                            Color(0xFF1E293B),
                            Color(0xFF111827)
                        )
                    )
                ),
            contentAlignment = Alignment.Center
        ) {

            Card(
                shape = RoundedCornerShape(28.dp)
            ) {

                Column(
                    modifier = Modifier.padding(30.dp),
                    horizontalAlignment =
                        Alignment.CenterHorizontally
                ) {

                    Text(
                        text = mensajeTransicion,
                        fontSize = 22.sp,
                        fontWeight = FontWeight.Bold,
                        textAlign = TextAlign.Center
                    )

                    Spacer(
                        modifier = Modifier.height(20.dp)
                    )

                    LinearProgressIndicator(
                        modifier =
                            Modifier.fillMaxWidth()
                    )
                }
            }
        }

        return
    }
    /*
    --------------------------------------------------
    CONTENIDO PRINCIPAL
    --------------------------------------------------
    */
    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(
                        Color(0xFF0F172A),
                        Color(0xFF1E293B),
                        Color(0xFF111827)
                    )
                )
            )
    ) {

        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(
                    rememberScrollState()
                )
                .padding(18.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            Spacer(modifier = Modifier.height(24.dp))

            /*
            --------------------------------------------------
            HEADER PARA MOSTRAR TIEMPO, PUNTUACION Y NIVEL
            --------------------------------------------------
            */
            Header(
                nivel = nivel,
                tiempo = tiempoTotal,
                puntuacion = puntuacionTotal + puntuacionNivel,
                preguntaActual = numeroPregunta + 1,
                totalPreguntas = preguntas.size
            )

            Spacer(modifier = Modifier.height(28.dp))

            /*
            --------------------------------------------------
            TARJETA PRINCIPAL DONDE IRÁN LOS ELEMENTOS CLICKABLES
            --------------------------------------------------
            */
            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(28.dp),
                colors = CardDefaults.cardColors(
                    containerColor =
                        Color.White.copy(alpha = 0.96f)
                ),
                elevation = CardDefaults.cardElevation(
                    defaultElevation = 10.dp
                )
            ) {
                AnimatedContent(
                    targetState = numeroPregunta,

                    transitionSpec = {

                        slideInHorizontally(
                            animationSpec = tween(250),
                            initialOffsetX = { it }

                        ) + fadeIn() togetherWith

                                slideOutHorizontally(
                                    animationSpec = tween(250),
                                    targetOffsetX = { -it }

                                ) + fadeOut()
                    },

                    label = "AnimacionPregunta"

                ) { index ->

                    val pregunta = preguntas.getOrNull(index)
                    val esPreguntaImagenes =
                        pregunta?.opcionesImagenes?.any {
                            !it.isNullOrEmpty()
                        } == true

                    if (pregunta == null) {

                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(300.dp),

                            contentAlignment = Alignment.Center
                        ) {

                            Text(
                                text = "Cargando siguiente nivel...",
                                fontSize = 20.sp,
                                fontWeight = FontWeight.Bold,
                                color = Color.Gray
                            )
                        }

                        return@AnimatedContent
                    }

                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(24.dp),

                        horizontalAlignment =
                            Alignment.CenterHorizontally
                    ) {

                        if (!pregunta.imagenPregunta.isNullOrEmpty()) {

                            Surface(
                                shape = RoundedCornerShape(22.dp),
                                tonalElevation = 6.dp
                            ) {

                                AsyncImage(
                                    model = pregunta.imagenPregunta,
                                    contentDescription = null,
                                    contentScale = ContentScale.Crop,
                                    modifier = Modifier
                                        .fillMaxWidth()
                                        .height(240.dp)
                                )
                            }

                            Spacer(
                                modifier = Modifier.height(26.dp)
                            )
                        }




                        Spacer(
                            modifier = Modifier.height(26.dp)
                        )

                        Text(
                            text = pregunta.pregunta,

                            fontSize = 23.sp,

                            fontWeight =
                                FontWeight.ExtraBold,

                            color = Color(0xFF1E293B),

                            textAlign = TextAlign.Center
                        )

                        Spacer(
                            modifier = Modifier.height(28.dp)
                        )

                    if (esPreguntaImagenes) {

                        OpcionesImagenes(
                            imagenes = pregunta.opcionesImagenes!!,
                            respuestaElegida = respuestaElegida,
                            onClick = { indexRespuesta ->

                                if (respuestaElegida == null) {

                                    respuestaElegida = indexRespuesta

                                    if (
                                        indexRespuesta ==
                                        pregunta.opcionCorrecta
                                    ) {

                                        MediaPlayer.create(
                                            context,
                                            R.raw.katanasonido
                                        ).start()

                                        puntuacionNivel += 1000

                                        respuestaCorrecta++

                                        estadoRespuesta[numeroPregunta] =
                                            EstadoRespuesta.CORRECT

                                    } else {

                                        MediaPlayer.create(
                                            context,
                                            R.raw.error
                                        ).start()

                                        puntuacionNivel -= 200

                                        estadoRespuesta[numeroPregunta] =
                                            EstadoRespuesta.WRONG
                                    }
                                }
                            }
                        )

                    } else {

                        Opciones(
                            opciones = pregunta.opciones ?: emptyList(),
                            respuestaElegida = respuestaElegida,
                            onClick = { indexRespuesta ->

                                if (respuestaElegida == null) {

                                    respuestaElegida = indexRespuesta

                                    if (
                                        indexRespuesta ==
                                        pregunta.opcionCorrecta
                                    ) {

                                        MediaPlayer.create(
                                            context,
                                            R.raw.katanasonido
                                        ).start()

                                        puntuacionNivel += 1000

                                        respuestaCorrecta++

                                        estadoRespuesta[numeroPregunta] =
                                            EstadoRespuesta.CORRECT

                                    } else {

                                        MediaPlayer.create(
                                            context,
                                            R.raw.error
                                        ).start()

                                        puntuacionNivel -= 200

                                        estadoRespuesta[numeroPregunta] =
                                            EstadoRespuesta.WRONG
                                    }
                                }
                            }
                        )
                    }

                    }
                }
            }

            Spacer(modifier = Modifier.height(28.dp))

            /*
            --------------------------------------------------
            PROGRESO DEL JUEGO
            --------------------------------------------------
            */
            QuestionProgress(
                states = estadoRespuesta,
                currentIndex = numeroPregunta
            )

            Spacer(modifier = Modifier.height(32.dp))

            /*
            --------------------------------------------------
            BOTÓN FINALIZAR
            --------------------------------------------------
            */


            Spacer(modifier = Modifier.height(30.dp))
        }
    }
}

/*
--------------------------------------------------
HEADER SUPERIOR
--------------------------------------------------
*/
@Composable
fun Header(
    nivel: Int,
    tiempo: Int,
    puntuacion: Int,
    preguntaActual: Int,
    totalPreguntas: Int
) {

    Card(
        modifier = Modifier.fillMaxWidth(),

        shape = RoundedCornerShape(24.dp),

        colors = CardDefaults.cardColors(
            containerColor =
                Color.White.copy(alpha = 0.95f)
        )
    ) {

        Column(
            modifier = Modifier.padding(18.dp)
        ) {

            Row(
                modifier = Modifier.fillMaxWidth(),

                horizontalArrangement =
                    Arrangement.SpaceBetween
            ) {

                Estadistica(
                    titulo = "Nivel",
                    valor = "$nivel"
                )

                Estadistica(
                    titulo = "Tiempo",
                    valor = "${tiempo}s"
                )

                Estadistica(
                    titulo = "Puntos",
                    valor = "$puntuacion"
                )
            }

            Spacer(modifier = Modifier.height(18.dp))

            Text(
                text = "Pregunta $preguntaActual de $totalPreguntas",

                fontWeight = FontWeight.Bold,

                color = Color(0xFF1E293B)
            )

            Spacer(modifier = Modifier.height(8.dp))

            LinearProgressIndicator(
                progress = {
                    preguntaActual.toFloat() /
                            totalPreguntas.toFloat()
                }
            )
        }
    }
}

/*
--------------------------------------------------
ESTADÍSTICAS
--------------------------------------------------
*/
@Composable
fun Estadistica(
    titulo: String,
    valor: String
) {

    Column(
        horizontalAlignment =
            Alignment.CenterHorizontally
    ) {

        Text(
            text = titulo,
            fontSize = 13.sp,
            color = Color.Gray
        )

        Spacer(modifier = Modifier.height(4.dp))

        Text(
            text = valor,
            fontSize = 20.sp,
            fontWeight = FontWeight.ExtraBold,
            color = Color(0xFF1E3A8A)
        )
    }
}

/*
--------------------------------------------------
OPCIONES
--------------------------------------------------
*/
@Composable
fun Opciones(
    opciones: List<String>,
    respuestaElegida: Int?,
    onClick: (Int) -> Unit
) {

    Column(
        verticalArrangement =
            Arrangement.spacedBy(14.dp)
    ) {

        Row(
            horizontalArrangement =
                Arrangement.spacedBy(14.dp)
        ) {

            BotonRespuesta(
                text = opciones[0],
                color = Color(0xFFEF4444),
                modifier = Modifier.weight(1f),
                enabled = respuestaElegida == null
            ) {
                onClick(0)
            }

            BotonRespuesta(
                text = opciones[1],
                color = Color(0xFFFACC15),
                modifier = Modifier.weight(1f),
                enabled = respuestaElegida == null
            ) {
                onClick(1)
            }
        }

        Row(
            horizontalArrangement =
                Arrangement.spacedBy(14.dp)
        ) {

            BotonRespuesta(
                text = opciones[2],
                color = Color(0xFF38BDF8),
                modifier = Modifier.weight(1f),
                enabled = respuestaElegida == null
            ) {
                onClick(2)
            }

            BotonRespuesta(
                text = opciones[3],
                color = Color(0xFF4ADE80),
                modifier = Modifier.weight(1f),
                enabled = respuestaElegida == null
            ) {
                onClick(3)
            }
        }
    }
}
@Composable
fun OpcionesImagenes(
    imagenes: List<String?>,
    respuestaElegida: Int?,
    onClick: (Int) -> Unit
) {

    Column(
        verticalArrangement =
            Arrangement.spacedBy(12.dp)
    ) {

        Row(
            horizontalArrangement =
                Arrangement.spacedBy(12.dp)
        ) {

            ImagenRespuesta(
                imagenes[0],
                respuestaElegida == null
            ) {
                onClick(0)
            }

            ImagenRespuesta(
                imagenes[1],
                respuestaElegida == null
            ) {
                onClick(1)
            }
        }

        Row(
            horizontalArrangement =
                Arrangement.spacedBy(12.dp)
        ) {

            ImagenRespuesta(
                imagenes[2],
                respuestaElegida == null
            ) {
                onClick(2)
            }

            ImagenRespuesta(
                imagenes[3],
                respuestaElegida == null
            ) {
                onClick(3)
            }
        }
    }
}
@Composable
fun ImagenRespuesta(
    url: String?,
    enabled: Boolean,
    onClick: () -> Unit
) {

    Card(
        modifier = Modifier
            .size(160.dp)
            .padding(4.dp),

        shape = RoundedCornerShape(16.dp),

        onClick = {
            if (enabled) onClick()
        }
    ) {

        AsyncImage(
            model = url,
            contentDescription = null,
            contentScale = ContentScale.Crop,

            modifier = Modifier.fillMaxSize()
        )
    }
}

/*
--------------------------------------------------
BOTONES RESPUESTA
--------------------------------------------------
*/
@Composable
fun BotonRespuesta(
    text: String,
    color: Color,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    onClick: () -> Unit
) {

    Button(
        onClick = onClick,

        enabled = enabled,

        modifier = modifier.height(74.dp),

        shape = RoundedCornerShape(18.dp),

        colors = ButtonDefaults.buttonColors(
            containerColor = color,
            disabledContainerColor =
                color.copy(alpha = 0.5f),

            contentColor = Color.Black
        )
    ) {

        Text(
            text = text,

            fontSize = 15.sp,

            textAlign = TextAlign.Center,

            fontWeight = FontWeight.Bold
        )
    }
}

/*
--------------------------------------------------
PROGRESO DE PREGUNTAS
--------------------------------------------------
*/
@Composable
fun QuestionProgress(
    states: List<EstadoRespuesta>,
    currentIndex: Int
) {

    Row(
        horizontalArrangement =
            Arrangement.Center
    ) {

        states.forEachIndexed { index, state ->

            val color = when (state) {

                EstadoRespuesta.CORRECT ->
                    Color(0xFF22C55E)

                EstadoRespuesta.WRONG ->
                    Color(0xFFEF4444)

                EstadoRespuesta.CURRENT ->
                    Color(0xFFFACC15)

                else ->
                    Color(0xFFCBD5E1)
            }

            Box(
                modifier = Modifier
                    .padding(horizontal = 4.dp)
                    .size(
                        if (index == currentIndex)
                            34.dp
                        else
                            28.dp
                    )
                    .clip(CircleShape)
                    .background(color)
                    .border(
                        width = 2.dp,
                        color = Color.White,
                        shape = CircleShape
                    ),

                contentAlignment =
                    Alignment.Center
            ) {

                Text(
                    text = "${index + 1}",

                    color = Color.Black,

                    fontWeight = FontWeight.Bold,

                    fontSize = 13.sp
                )
            }
        }

    }

}