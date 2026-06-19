package com.example.proyecto_genshiken

import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ElevatedCard
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import kotlinx.coroutines.delay

/*
--------------------------------------------------
Pantalla inicial de la app
--------------------------------------------------

Pantalla de bienvenida principal.

Incluye:
- Logo de Genshiken
- Animación suave
- Accesos a modo casual, competitivo, ranking,
  gacha y colección
- Curiosidad aleatoria
- Menú de configuración
*/
@OptIn(ExperimentalMaterial3Api::class)
@Composable

fun PantallaInicio(navController: NavController) {
    val context = LocalContext.current

    /*
    --------------------------------------------------
    Música del menú
    --------------------------------------------------

    Al abrir la app por primera vez, el emulador carga
    pantalla, imágenes, animaciones y música a la vez.

    Por eso se retrasa un poco la música del menú.
    Así primero carga la interfaz y luego empieza el audio.
    */
    LaunchedEffect(Unit) {

        delay(1500)

        MusicManager.reproducirMenu(context)
    }


    val curiosidad = remember { Curiosidades.lista.random() }
    var expandir by remember { mutableStateOf(false) }

    val animacion = rememberInfiniteTransition()

    val movimientoLogo by animacion.animateFloat(
        initialValue = 0f,
        targetValue = -8f,
        animationSpec = infiniteRepeatable(
            animation = tween(
                durationMillis = 1600,
                easing = FastOutSlowInEasing
            ),
            repeatMode = RepeatMode.Reverse
        )
    )

    val escalaLogo by animacion.animateFloat(
        initialValue = 1f,
        targetValue = 1.04f,
        animationSpec = infiniteRepeatable(
            animation = tween(
                durationMillis = 1600,
                easing = FastOutSlowInEasing
            ),
            repeatMode = RepeatMode.Reverse
        )
    )

    val movimientoTexto by animacion.animateFloat(
        initialValue = 0f,
        targetValue = 5f,
        animationSpec = infiniteRepeatable(
            animation = tween(
                durationMillis = 1900,
                easing = FastOutSlowInEasing
            ),
            repeatMode = RepeatMode.Reverse
        )
    )

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "GenshikenCS.es",
                        fontWeight = FontWeight.Bold
                    )
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color(0xFF101827),
                    titleContentColor = Color.White,
                    actionIconContentColor = Color.White
                ),
                actions = {
                    IconButton(onClick = { expandir = true }) {
                        Icon(
                            imageVector = Icons.Default.Settings,
                            contentDescription = "Configuración"
                        )
                    }

                    DropdownMenu(
                        expanded = expandir,
                        onDismissRequest = { expandir = false }
                    ) {
                        DropdownMenuItem(
                            text = { Text("Modo oscuro") },
                            onClick = {
                                expandir = false
                                ThemeState.isDarkMode.value =
                                    !ThemeState.isDarkMode.value
                            }
                        )

                        DropdownMenuItem(
                            text = { Text("Cambiar nombre") },
                            onClick = {
                                expandir = false
                                navController.navigate("cambiarNombre")
                            }
                        )
                        if (UserSession.userId != 0) {

                        DropdownMenuItem(
                            text = { Text("Cerrar sesión") },
                            onClick = {

                                expandir = false

                                UserSession.userId = 0
                                UserSession.userName = ""

                                navController.navigate("inicioSesionCompeti") {
                                    popUpTo(0)
                                }
                            }
                        )
                    }
                    }
                }
            )
        }
    ) { paddingValues ->

        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .background(
                    Brush.verticalGradient(
                        colors = listOf(
                            Color(0xFF101827),
                            Color(0xFF1E293B),
                            Color(0xFF0F172A)
                        )
                    )
                )
        ) {

            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .verticalScroll(rememberScrollState())
                    .padding(20.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {

                Spacer(modifier = Modifier.height(18.dp))

                /*
                --------------------------------------------------
                Logo principal
                --------------------------------------------------
                */
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(170.dp)
                        .offset(y = movimientoLogo.dp)
                        .graphicsLayer(
                            scaleX = escalaLogo,
                            scaleY = escalaLogo
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    Image(
                        painter = painterResource(id = R.drawable.logotipo),
                        contentDescription = "Logo Genshiken",
                        contentScale = ContentScale.Fit,
                        modifier = Modifier.height(160.dp)
                    )
                }

                Spacer(modifier = Modifier.height(28.dp))

                Text(
                    text = "Trivia GenshikenCS.es",
                    fontSize = 30.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color = Color.White,
                    textAlign = TextAlign.Center
                )

                Spacer(modifier = Modifier.height(8.dp))

                Text(
                    text = "Demuestra tus conocimientos y compite por el mejor puesto",
                    fontSize = 16.sp,
                    color = Color(0xFFE5E7EB),
                    textAlign = TextAlign.Center,
                    modifier = Modifier.padding(horizontal = 10.dp)
                )

                Spacer(modifier = Modifier.height(18.dp))

                Text(
                    text = "Selecciona un modo para comenzar",
                    fontSize = 15.sp,
                    color = Color(0xFFFACC15),
                    fontWeight = FontWeight.SemiBold,
                    modifier = Modifier.offset(y = movimientoTexto.dp)
                )

                Spacer(modifier = Modifier.height(30.dp))

                OpcionPrincipal(
                    titulo = "Modo Casual",
                    subtitulo = "Juega una partida rápida sin presión.",
                    imagen = R.drawable.espadacasual,
                    onClick = {

                        GameMode.esCompetitivo = false

                        navController.navigate("Juego")
                    }
                )

                Spacer(modifier = Modifier.height(16.dp))

                OpcionPrincipal(
                    titulo = "Modo Competitivo",
                    subtitulo = "Compite, suma puntos y sube en el ranking.",
                    imagen = R.drawable.espadacasual,
                    onClick = {
                        GameMode.esCompetitivo = true
                        if (UserSession.userId == 0) {
                            navController.navigate("inicioSesionCompeti")
                        } else {
                            navController.navigate("Juego")
                        }
                    }
                )

                Spacer(modifier = Modifier.height(22.dp))

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    BotonSecundario(
                        texto = "Ranking",
                        modifier = Modifier.weight(1f),
                        onClick = {
                            navController.navigate("Ranking")
                        }
                    )

                    BotonSecundario(
                        texto = "Gacha",
                        modifier = Modifier.weight(1f),
                        onClick = {
                            navController.navigate("gacha")
                        }
                    )
                }

                Spacer(modifier = Modifier.height(12.dp))

                OutlinedButton(
                    onClick = {
                        navController.navigate("coleccion")
                    },
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(16.dp),
                    border = BorderStroke(1.dp, Color(0xFFFACC15)),
                    colors = ButtonDefaults.outlinedButtonColors(
                        contentColor = Color(0xFFFACC15)
                    )
                ) {
                    Text(
                        text = "Ver colección de espadas",
                        fontSize = 15.sp,
                        fontWeight = FontWeight.Bold
                    )
                }

                Spacer(modifier = Modifier.height(26.dp))

                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(
                        containerColor = Color.White.copy(alpha = 0.95f)
                    ),
                    elevation = CardDefaults.cardElevation(
                        defaultElevation = 8.dp
                    )
                ) {
                    Column(
                        modifier = Modifier.padding(18.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Text(
                            text = "¿Sabías que?",
                            fontWeight = FontWeight.ExtraBold,
                            fontSize = 23.sp,
                            color = Color(0xFF1E3A8A)
                        )

                        Spacer(modifier = Modifier.height(10.dp))

                        Text(
                            text = curiosidad,
                            fontSize = 16.sp,
                            textAlign = TextAlign.Center,
                            color = Color(0xFF111827)
                        )
                    }

                }


                Spacer(modifier = Modifier.height(30.dp))
                Text(
                    text = "Mark&Raph Development®",
                    fontWeight = FontWeight.ExtraBold,
                    fontSize = 15.sp,
                    color = Color.White
                )
            }

        }

    }

}


/*
--------------------------------------------------
Tarjeta principal de modo de juego
--------------------------------------------------

Se corrige la imagen usando ContentScale .Fit
para que la espada no se corte ni salga como franja roja.
*/
@Composable
fun OpcionPrincipal(
    titulo: String,
    subtitulo: String,
    imagen: Int,
    onClick: () -> Unit
) {
    ElevatedCard(
        modifier = Modifier
            .fillMaxWidth()
            .height(132.dp)
            .clickable { onClick() },
        shape = RoundedCornerShape(22.dp),
        colors = CardDefaults.elevatedCardColors(
            containerColor = Color.White.copy(alpha = 0.96f)
        ),
        elevation = CardDefaults.elevatedCardElevation(
            defaultElevation = 10.dp
        )
    ) {
        Row(
            modifier = Modifier
                .fillMaxSize()
                .padding(14.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {

            Box(
                modifier = Modifier
                    .width(100.dp)
                    .height(95.dp)
                    .clip(RoundedCornerShape(18.dp))
                    .background(Color(0xFFF8FAFC))
                    .padding(8.dp),
                contentAlignment = Alignment.Center
            ) {
                Image(
                    painter = painterResource(id = imagen),
                    contentDescription = titulo,
                    contentScale = ContentScale.Fit,
                    modifier = Modifier.fillMaxSize()
                )
            }

            Spacer(modifier = Modifier.size(16.dp))

            Column(
                modifier = Modifier.weight(1f),
                verticalArrangement = Arrangement.Center
            ) {
                Text(
                    text = titulo,
                    fontSize = 22.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color = Color(0xFF1E3A8A)
                )

                Spacer(modifier = Modifier.height(6.dp))

                Text(
                    text = subtitulo,
                    fontSize = 14.sp,
                    color = Color(0xFF374151)
                )
            }
        }
    }
}

/*
--------------------------------------------------
Botones secundarios
--------------------------------------------------
*/
@Composable
fun BotonSecundario(
    texto: String,
    modifier: Modifier = Modifier,
    onClick: () -> Unit
) {
    Button(
        onClick = onClick,
        modifier = modifier.height(52.dp),
        shape = RoundedCornerShape(16.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = Color(0xFFFACC15),
            contentColor = Color(0xFF111827)
        )
    ) {
        Text(
            text = texto,
            fontSize = 15.sp,
            fontWeight = FontWeight.Bold
        )
    }
}