package com.example.proyecto_genshiken

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import java.util.Calendar

@Composable
fun Ranking(navController: NavController) {

    var ranking by remember {
        mutableStateOf<List<Player>>(emptyList())
    }

    val meses = listOf(
        "Enero","Febrero","Marzo","Abril",
        "Mayo","Junio","Julio","Agosto",
        "Septiembre","Octubre","Noviembre","Diciembre"
    )

    val calendario = Calendar.getInstance()

    var mesSeleccionado by remember {
        mutableIntStateOf(
            calendario.get(Calendar.MONTH) + 1
        )
    }

    var anioSeleccionado by remember {
        mutableIntStateOf(
            calendario.get(Calendar.YEAR)
        )
    }

    var expandedMes by remember {
        mutableStateOf(false)
    }

    var expandedAnio by remember {
        mutableStateOf(false)
    }

    val anios = (2026..2040).toList()

    LaunchedEffect(
        mesSeleccionado,
        anioSeleccionado
    ) {

        UserRepository.getRanking(
            mesSeleccionado,
            anioSeleccionado
        ) {

            ranking = it
        }
    }

    val top10 = ranking.take(10)

    val userData = ranking.find {
        it.usuarioId == UserSession.userId &&
                it.puntuacion == GameSession.lastScore &&
                it.tiempo == GameSession.lastTime
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
            )
    ) {

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(18.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            Spacer(Modifier.height(20.dp))

            Text(
                text = "RANKING ",
                fontSize = 34.sp,
                fontWeight = FontWeight.ExtraBold,
                color = Color.White
            )

            Spacer(Modifier.height(24.dp))



            Row {

                Box {

                    Button(
                        onClick = {
                            expandedMes = true
                        },

                        shape = RoundedCornerShape(16.dp),

                        colors = ButtonDefaults.buttonColors(
                            containerColor =
                                Color(0xFFFACC15),

                            contentColor =
                                Color.Black
                        )
                    ) {

                        Text(
                            meses[mesSeleccionado - 1],
                            fontWeight = FontWeight.Bold
                        )
                    }

                    DropdownMenu(
                        expanded = expandedMes,

                        onDismissRequest = {
                            expandedMes = false
                        }
                    ) {

                        meses.forEachIndexed { index, mes ->

                            DropdownMenuItem(
                                text = {
                                    Text(mes)
                                },

                                onClick = {

                                    mesSeleccionado =
                                        index + 1

                                    expandedMes = false
                                }
                            )
                        }
                    }
                }

                Spacer(Modifier.width(12.dp))

                Box {

                    Button(
                        onClick = {
                            expandedAnio = true
                        },

                        shape = RoundedCornerShape(16.dp),

                        colors = ButtonDefaults.buttonColors(
                            containerColor =
                                Color(0xFF38BDF8),

                            contentColor =
                                Color.Black
                        )
                    ) {

                        Text(
                            "$anioSeleccionado",
                            fontWeight = FontWeight.Bold
                        )
                    }

                    DropdownMenu(
                        expanded = expandedAnio,

                        onDismissRequest = {
                            expandedAnio = false
                        }
                    ) {

                        anios.forEach { anio ->

                            DropdownMenuItem(
                                text = {
                                    Text("$anio")
                                },

                                onClick = {

                                    anioSeleccionado =
                                        anio

                                    expandedAnio = false
                                }
                            )
                        }
                    }
                }
            }

            Spacer(Modifier.height(26.dp))



            LazyColumn(
                modifier = Modifier.weight(1f),

                verticalArrangement =
                    Arrangement.spacedBy(12.dp)
            ) {

                itemsIndexed(top10) { index, player ->

                    val background = when(index){

                        0 -> Color(0xFFFFD700)
                        1 -> Color(0xFFC0C0C0)
                        2 -> Color(0xFFCD7F32)

                        else -> Color.White.copy(alpha = 0.12f)
                    }

                    val textColor =
                        if(index <= 2)
                            Color.Black
                        else
                            Color.White

                    Card(
                        modifier = Modifier.fillMaxWidth(),

                        shape = RoundedCornerShape(22.dp),

                        colors = CardDefaults.cardColors(
                            containerColor = background
                        )
                    ) {

                        Row(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(18.dp),

                            verticalAlignment =
                                Alignment.CenterVertically
                        ) {

                            Text(
                                text = "#${player.posicion}",

                                modifier =
                                    Modifier.weight(1f),

                                fontSize = 20.sp,

                                fontWeight =
                                    FontWeight.ExtraBold,

                                color = textColor
                            )

                            Text(
                                text = player.nombre,

                                modifier =
                                    Modifier.weight(2f),

                                fontSize = 18.sp,

                                fontWeight =
                                    FontWeight.Bold,

                                color = textColor
                            )

                            Text(
                                text =
                                    "${player.puntuacion}",

                                modifier =
                                    Modifier.weight(1.5f),

                                fontWeight =
                                    FontWeight.Bold,

                                color = textColor
                            )

                            Text(
                                text =
                                    "${player.tiempo}s",

                                modifier =
                                    Modifier.weight(1f),

                                color = textColor
                            )
                        }
                    }
                }
            }

            Spacer(Modifier.height(22.dp))

            /*
            -----------------------------------------
            La Posicion del jugador
            -----------------------------------------
            */

            Text(
                text = " Tu posición ",

                fontSize = 24.sp,

                fontWeight = FontWeight.Bold,

                color = Color.White
            )

            Spacer(Modifier.height(14.dp))

            userData?.let {

                Card(
                    modifier = Modifier.fillMaxWidth(),

                    shape = RoundedCornerShape(24.dp),

                    colors = CardDefaults.cardColors(
                        containerColor =
                            Color(0xFF06B6D4)
                    )
                ) {

                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(18.dp),

                        verticalAlignment =
                            Alignment.CenterVertically
                    ) {

                        Text(
                            "#${it.posicion}",
                            Modifier.weight(1f),

                            fontWeight =
                                FontWeight.ExtraBold,

                            color = Color.Black
                        )

                        Text(
                            it.nombre,
                            Modifier.weight(2f),

                            fontWeight =
                                FontWeight.Bold,

                            color = Color.Black
                        )

                        Text(
                            "${it.puntuacion}",
                            Modifier.weight(1.5f),

                            fontWeight =
                                FontWeight.Bold,

                            color = Color.Black
                        )

                        Text(
                            "${it.tiempo}s",
                            Modifier.weight(1f),

                            color = Color.Black
                        )
                    }
                }
            }

            Spacer(Modifier.height(24.dp))

            /*
            -----------------------------------------
            El boton para regresar al Inicio
            -----------------------------------------
            */

            Button(
                onClick = {
                    navController.navigate("inicio")
                },

                modifier = Modifier
                    .fillMaxWidth()
                    .height(58.dp),

                shape = RoundedCornerShape(18.dp),

                colors = ButtonDefaults.buttonColors(
                    containerColor =
                        Color(0xFFFACC15),

                    contentColor =
                        Color.Black
                )
            ) {

                Text(
                    text = "Volver al inicio",

                    fontSize = 18.sp,

                    fontWeight = FontWeight.Bold
                )
            }

            Spacer(Modifier.height(12.dp))
        }
    }
}