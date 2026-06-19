package com.example.proyecto_genshiken

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.scaleIn
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.alpha
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalUriHandler
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import coil.compose.AsyncImage

@Composable
fun PantallaColeccion(navController: NavController) {

    val totalEspadas =
        GachaState.listaEspadasOnline.size

    val desbloqueadas =
        GachaState.espadasDesbloqueadas.size

    val porcentaje =
        if (totalEspadas > 0)
            desbloqueadas.toFloat() / totalEspadas.toFloat()
        else
            0f

    LaunchedEffect(Unit) {

        UserRepository.obtenerEspadasGacha {

            GachaState.listaEspadasOnline.clear()

            GachaState.listaEspadasOnline.addAll(it)
        }
    }

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

        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(18.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            item {

                Spacer(
                    Modifier.height(30.dp)
                )

                /*
                ----------------------------------------
                TÍTULO
                ----------------------------------------
                */

                Text(
                    text = " COLECCIÓN ",

                    fontSize = 34.sp,

                    fontWeight =
                        FontWeight.ExtraBold,

                    color = Color.White
                )

                Spacer(
                    Modifier.height(18.dp)
                )

                /*
                ----------------------------------------
                PROGRESO
                ----------------------------------------
                */

                Card(
                    colors = CardDefaults.cardColors(
                        containerColor =
                            Color.White.copy(
                                alpha = 0.1f
                            )
                    ),

                    shape =
                        RoundedCornerShape(22.dp)
                ) {

                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(20.dp)
                    ) {

                        Text(
                            text =
                                "Desbloqueadas: $desbloqueadas / $totalEspadas ",

                            color = Color.White,

                            fontWeight =
                                FontWeight.Bold,

                            fontSize = 18.sp
                        )

                        Spacer(
                            Modifier.height(14.dp)
                        )

                        LinearProgressIndicator(
                            progress = {
                                porcentaje
                            },

                            modifier = Modifier
                                .fillMaxWidth()
                                .height(10.dp),

                            color = Color(
                                0xFFFACC15
                            ),

                            trackColor = Color(
                                0xFF334155
                            )
                        )
                    }
                }

                Spacer(
                    Modifier.height(24.dp)
                )
            }

            /*
            ----------------------------------------
            LISTA ESPADAS
            ----------------------------------------
            */

            items(GachaState.listaEspadasOnline) { espada ->

                val desbloqueada =
                    GachaState
                        .espadasDesbloqueadas
                        .contains(espada.id)

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

                AnimatedVisibility(
                    visible = true,

                    enter =
                        fadeIn(
                            animationSpec =
                                tween(500)
                        ) +

                                scaleIn(
                                    initialScale = 0.9f,

                                    animationSpec =
                                        tween(500)
                                )
                ) {

                    Card(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 10.dp),

                        shape =
                            RoundedCornerShape(28.dp),

                        elevation =
                            CardDefaults.cardElevation(
                                12.dp
                            ),

                        colors =
                            CardDefaults.cardColors(
                                containerColor =
                                    if (desbloqueada)

                                        colorFondo.copy(
                                            alpha = 0.25f
                                        )

                                    else

                                        Color.DarkGray.copy(
                                            alpha = 0.45f
                                        )
                            )
                    ) {

                        Row(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(18.dp),

                            verticalAlignment =
                                Alignment.CenterVertically
                        ) {

                            /*
                            ----------------------------------------
                            IMAGEN
                            ----------------------------------------
                            */

                            Card(
                                shape =
                                    RoundedCornerShape(
                                        20.dp
                                    ),

                                colors =
                                    CardDefaults.cardColors(
                                        containerColor =
                                            Color.White.copy(
                                                alpha = 0.08f
                                            )
                                    )
                            ) {

                                AsyncImage(

                                    model =
                                        if (desbloqueada)
                                            espada.imagen_url
                                        else
                                            null,

                                    contentDescription = espada.nombre,

                                    modifier = Modifier
                                        .size(110.dp)
                                        .padding(10.dp)
                                        .alpha(
                                            if (desbloqueada)
                                                1f
                                            else
                                                0.35f
                                        )
                                )
                            }

                            Spacer(
                                modifier =
                                    Modifier.width(18.dp)
                            )

                            /*
                            ----------------------------------------
                            INFO
                            ----------------------------------------
                            */

                            Column(
                                modifier = Modifier.weight(1f)
                            ) {

                                Text(
                                    text =

                                        if (desbloqueada)
                                            espada.nombre

                                        else
                                            "?????",

                                    fontSize = 24.sp,

                                    fontWeight =
                                        FontWeight.ExtraBold,

                                    color = Color.White
                                )

                                Spacer(
                                    Modifier.height(8.dp)
                                )

                                if (desbloqueada) {

                                    Text(
                                        text =
                                            "⭐".repeat(
                                                estrellas
                                            ),

                                        fontSize = 22.sp
                                    )

                                    Spacer(
                                        Modifier.height(10.dp)
                                    )

                                    /*
                                    ----------------------------------------
                                    DESCRIPCIÓN CON ENLACES
                                    ----------------------------------------

                                    Si la descripción contiene un enlace como:
                                    http://www.google.com
                                    https://www.shopkatanas.com

                                    Se muestra azul, subrayado y se puede pulsar
                                    para abrirlo en el navegador.
                                    */

                                    DescripcionConEnlaces(
                                        descripcion = espada.descripcion
                                    )

                                    Spacer(
                                        Modifier.height(10.dp)
                                    )

                                    Text(
                                        text =
                                            espada.rareza,

                                        color =
                                            colorFondo,

                                        fontWeight =
                                            FontWeight.Bold
                                    )

                                } else {

                                    Text(
                                        text =
                                            " Bloqueada",

                                        color =
                                            Color.LightGray,

                                        fontSize = 18.sp,

                                        fontWeight =
                                            FontWeight.Bold
                                    )

                                    Spacer(
                                        Modifier.height(8.dp)
                                    )

                                    Text(
                                        text =
                                            "Consíguela en el gachapon.",

                                        color =
                                            Color.Gray
                                    )
                                }
                            }
                        }
                    }
                }
            }

            /*
            ----------------------------------------
            BOTÓN VOLVER
            ----------------------------------------
            */

            item {

                Spacer(
                    Modifier.height(26.dp)
                )

                OutlinedButton(
                    onClick = {
                        navController.navigate(
                            "inicio"
                        )
                    },

                    modifier = Modifier
                        .fillMaxWidth()
                        .height(58.dp),

                    shape =
                        RoundedCornerShape(18.dp),

                    colors =
                        ButtonDefaults
                            .outlinedButtonColors(
                                contentColor =
                                    Color.White
                            )
                ) {

                    Text(
                        text =
                            " Volver al inicio",

                        fontSize = 18.sp,

                        fontWeight =
                            FontWeight.Bold
                    )
                }

                Spacer(
                    Modifier.height(40.dp)
                )
            }
        }
    }
}

/*
--------------------------------------------------
Descripción con enlaces clicables
--------------------------------------------------

Convierte los enlaces escritos dentro de la descripción
en textos azules y clicables.

Ejemplos compatibles:
- http://www.google.com
- https://www.shopkatanas.com
- www.google.com
*/
@Composable
fun DescripcionConEnlaces(descripcion: String) {

    val uriHandler = LocalUriHandler.current

    val regexUrl =
        Regex("(https?://\\S+|www\\.\\S+)")

    val lineas =
        descripcion.split("\n")

    Column {

        lineas.forEachIndexed { index, linea ->

            val coincidencias =
                regexUrl.findAll(linea).toList()

            if (coincidencias.isEmpty()) {

                Text(
                    text = linea,
                    color = Color.LightGray,
                    fontSize = 15.sp
                )

            } else {

                var posicionActual = 0

                coincidencias.forEach { coincidencia ->

                    val inicio = coincidencia.range.first
                    val fin = coincidencia.range.last + 1

                    val textoAntes =
                        linea.substring(posicionActual, inicio)

                    if (textoAntes.isNotBlank()) {

                        Text(
                            text = textoAntes,
                            color = Color.LightGray,
                            fontSize = 15.sp
                        )
                    }

                    val enlaceVisible =
                        coincidencia.value.trimEnd(
                            '.',
                            ',',
                            ';',
                            ')',
                            ']'
                        )

                    val enlaceAbrir =
                        if (enlaceVisible.startsWith("www.")) {
                            "https://$enlaceVisible"
                        } else {
                            enlaceVisible
                        }

                    Text(
                        text = enlaceVisible,
                        color = Color(0xFF60A5FA),
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold,
                        textDecoration = TextDecoration.Underline,
                        modifier = Modifier.clickable {
                            uriHandler.openUri(enlaceAbrir)
                        }
                    )

                    posicionActual = fin
                }

                if (posicionActual < linea.length) {

                    val textoFinal =
                        linea.substring(posicionActual)

                    if (textoFinal.isNotBlank()) {

                        Text(
                            text = textoFinal,
                            color = Color.LightGray,
                            fontSize = 15.sp
                        )
                    }
                }
            }

            if (index < lineas.lastIndex) {
                Spacer(
                    modifier = Modifier.height(4.dp)
                )
            }
        }
    }
}