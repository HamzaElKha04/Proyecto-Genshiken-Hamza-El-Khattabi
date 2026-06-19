package com.example.proyecto_genshiken

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.*
import androidx.compose.animation.fadeIn
import androidx.compose.animation.scaleIn
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.scale
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import coil.compose.AsyncImage

@Composable
fun PantallaGacha(navController: NavController) {

    var resultados by remember {
        mutableStateOf<List<EspadaOnline>>(emptyList())
    }

    var animarCartas by remember {
        mutableStateOf(false)
    }
    LaunchedEffect(Unit) {

        UserRepository.obtenerEspadasGacha {

            GachaState.listaEspadasOnline.clear()

            GachaState.listaEspadasOnline.addAll(it)

            println("ESPADAS CARGADAS: ${it.size}")

            it.forEach { espada ->

                println("ESPADA: ${espada.nombre}")
                println("IMAGEN: ${espada.imagen_url}")
            }
        }
    }
    /*
    ----------------------------------------
    ANIMACIÓN BOTONES
    ----------------------------------------
    */

    val infiniteTransition =
        rememberInfiniteTransition(label = "")

    val scaleBoton by infiniteTransition.animateFloat(
        initialValue = 1f,
        targetValue = 1.05f,

        animationSpec = infiniteRepeatable(
            animation = tween(900),
            repeatMode = RepeatMode.Reverse
        ),

        label = ""
    )

    /*
    ----------------------------------------
    FONDO
    ----------------------------------------
    */

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    listOf(
                        Color(0xFF0F172A),
                        Color(0xFF1E1B4B),
                        Color(0xFF111827)
                    )
                )
            )
    ) {

        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(20.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            Spacer(Modifier.height(40.dp))

            /*
            ----------------------------------------
            TÍTULO
            ----------------------------------------
            */

            Text(
                text = "✨ GACHAPON GENSHIKENCS.es ✨",

                modifier = Modifier.fillMaxWidth(),

                fontSize = 30.sp,

                fontWeight = FontWeight.ExtraBold,

                color = Color.White
            )

            Spacer(Modifier.height(18.dp))

            /*
            ----------------------------------------
            MONEDAS
            ----------------------------------------
            */

            Card(
                colors = CardDefaults.cardColors(
                    containerColor =
                        Color.White.copy(alpha = 0.12f)
                ),

                shape = RoundedCornerShape(18.dp)
            ) {

                Text(
                    text =
                        "🪙 Monedas: ${GachaState.monedas.value}",

                    modifier = Modifier.padding(
                        horizontal = 20.dp,
                        vertical = 12.dp
                    ),

                    color = Color.White,

                    fontWeight = FontWeight.Bold,

                    fontSize = 18.sp
                )
            }

            Spacer(Modifier.height(35.dp))

            /*
            ----------------------------------------
            BOTÓN INVOCAR X1
            ----------------------------------------
            */

            Button(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(68.dp)
                    .scale(scaleBoton),

                shape = RoundedCornerShape(24.dp),

                colors = ButtonDefaults.buttonColors(
                    containerColor =
                        Color(0xFFFACC15),

                    contentColor =
                        Color.Black
                ),

                onClick = {

                    if (GachaState.monedas.value >= 10) {

                        animarCartas = false

                        resultados = emptyList()

                        GachaState.monedas.value -= 10

                        val espada = tirarGacha()

                        if (espada != null) {

                            resultados = listOf(espada)

                            GachaState.añadirEspada(
                                espada.id
                            )

                            UserRepository.guardarEspada(
                                UserSession.userId,
                                espada.id
                            )
                        }

                        UserRepository.guardarMonedas(
                            UserSession.userId,
                            GachaState.monedas.value
                        )

                        animarCartas = true
                    }
                }
            ) {

                Text(
                    text = "✨ INVOCAR x1 ✨",

                    fontSize = 22.sp,

                    fontWeight = FontWeight.ExtraBold
                )
            }

            Spacer(Modifier.height(16.dp))

            /*
            ----------------------------------------
            BOTÓN INVOCAR X10
            ----------------------------------------
            */

            Button(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(68.dp).
                    scale(scaleBoton),

                shape = RoundedCornerShape(24.dp),

                colors = ButtonDefaults.buttonColors(
                    containerColor =
                        Color(0xFF9333EA),

                    contentColor =
                        Color.White
                ),

                onClick = {

                    if (GachaState.monedas.value >= 100) {

                        animarCartas = false

                        resultados = emptyList()

                        GachaState.monedas.value -= 100

                        val nuevasEspadas =
                            mutableListOf<EspadaOnline>()

                        repeat(10) {

                            val espada = tirarGacha()

                            if (espada != null) {

                                nuevasEspadas.add(espada)

                                GachaState.añadirEspada(
                                    espada.id
                                )

                                UserRepository.guardarEspada(
                                    UserSession.userId,
                                    espada.id
                                )
                            }
                        }

                        resultados = nuevasEspadas

                        UserRepository.guardarMonedas(
                            UserSession.userId,
                            GachaState.monedas.value
                        )

                        animarCartas = true
                    }
                }
            ) {

                Text(
                    text = "🌟 INVOCAR x10 🌟",

                    fontSize = 22.sp,

                    fontWeight = FontWeight.ExtraBold
                )
            }

            Spacer(Modifier.height(36.dp))

            /*
            ----------------------------------------
            RESULTADOS
            ----------------------------------------
            */

            AnimatedVisibility(
                visible = resultados.isNotEmpty()
                        && animarCartas,

                enter =
                    fadeIn(
                        animationSpec =
                            tween(500)
                    ) +

                            scaleIn(
                                initialScale = 0.4f,

                                animationSpec =
                                    tween(500)
                            )
            ) {

                Column {

                    resultados.chunked(2).forEach { fila ->

                        Row(
                            horizontalArrangement =
                                Arrangement.spacedBy(12.dp),

                            modifier = Modifier
                                .fillMaxWidth()
                        ) {

                            fila.forEach { espada ->

                                val colorFondo =
                                    when (espada.rareza) {

                                        "COMUN" ->
                                            Color.Gray

                                        "RARA" ->
                                            Color(0xFF38BDF8)

                                        "EPICA" ->
                                            Color(0xFFC084FC)

                                        else ->
                                            Color(0xFFFACC15)
                                    }

                                val estrellas =
                                    when (espada.rareza) {

                                        "COMUN" -> 1
                                        "RARA" -> 2
                                        "EPICA" -> 3
                                        else -> 4
                                    }

                                Card(
                                    modifier = Modifier
                                        .weight(1f)
                                        .padding(bottom = 12.dp),

                                    shape = RoundedCornerShape(24.dp),

                                    elevation =
                                        CardDefaults.cardElevation(
                                            10.dp
                                        ),

                                    colors =
                                        CardDefaults.cardColors(
                                            containerColor =
                                                colorFondo
                                        )
                                ) {

                                    Column(
                                        modifier = Modifier
                                            .fillMaxWidth()
                                            .padding(16.dp),

                                        horizontalAlignment =
                                            Alignment.CenterHorizontally
                                    ) {

                                        Text(
                                            text =
                                                espada.rareza,

                                            fontWeight =
                                                FontWeight.Bold,

                                            color = Color.Black
                                        )

                                        Spacer(
                                            Modifier.height(10.dp)
                                        )

                                        AsyncImage(

                                            model = espada.imagen_url,

                                            contentDescription = espada.nombre,

                                            modifier = Modifier.size(120.dp)
                                        )

                                        Spacer(
                                            Modifier.height(10.dp)
                                        )

                                        Text(
                                            text =
                                                espada.nombre,

                                            fontWeight =
                                                FontWeight.ExtraBold,

                                            fontSize = 16.sp,

                                            color = Color.Black
                                        )

                                        Spacer(
                                            Modifier.height(6.dp)
                                        )

                                        Text(
                                            text =
                                                "⭐".repeat(
                                                    estrellas
                                                ),

                                            fontSize = 18.sp
                                        )
                                    }
                                }
                            }
                        }
                    }
                }
            }

            Spacer(Modifier.height(20.dp))

            /*
            ----------------------------------------
            BOTÓN COLECCIÓN
            ----------------------------------------
            */

            OutlinedButton(
                onClick = {
                    navController.navigate(
                        "coleccion"
                    )
                },

                modifier = Modifier
                    .fillMaxWidth()
                    .height(58.dp),

                shape = RoundedCornerShape(18.dp),

                colors = ButtonDefaults.outlinedButtonColors(
                    contentColor = Color.White
                )
            ) {

                Text(
                    text = " Ver colección",

                    fontSize = 18.sp,

                    fontWeight = FontWeight.Bold
                )
            }

            Spacer(Modifier.height(14.dp))

            /*
            ----------------------------------------
            BOTÓN VOLVER
            ----------------------------------------
            */

            OutlinedButton(
                onClick = {
                    navController.navigate("inicio")
                },

                modifier = Modifier
                    .fillMaxWidth()
                    .height(58.dp),

                shape = RoundedCornerShape(18.dp),

                colors = ButtonDefaults.outlinedButtonColors(
                    contentColor = Color.White
                )
            ) {

                Text(
                    text = " Volver al inicio",

                    fontSize = 18.sp,

                    fontWeight = FontWeight.Bold
                )
            }

            Spacer(Modifier.height(30.dp))
        }
    }
}