package com.example.proyecto_genshiken

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController

/*
--------------------------------------------------
Pantalla de ranking
--------------------------------------------------

Esta pantalla ya no usa datos inventados.
Carga el TOP 10 real desde getRanking.php.

El backend ordena por:
- puntos DESC
- tiempo ASC
- fecha ASC

Por eso aquí respetamos el orden que llega desde PHP.
*/
@Composable
fun Ranking(navController: NavController) {

    var ranking by remember { mutableStateOf<List<Player>>(emptyList()) }
    var cargando by remember { mutableStateOf(true) }

    /*
    --------------------------------------------------
    Cargar ranking real desde PHP
    --------------------------------------------------
    */
    LaunchedEffect(Unit) {
        UserRepository.getRanking {
            ranking = it
            cargando = false
        }
    }

    val usuarioActual = ranking.firstOrNull {
        it.nombre.equals(UserSession.userName, ignoreCase = true)
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Spacer(Modifier.height(40.dp))

        Text(
            text = "RANKING",
            fontSize = 30.sp,
            fontWeight = FontWeight.Bold
        )

        Spacer(Modifier.height(20.dp))

        if (cargando) {
            Text("Cargando ranking...")
            return@Column
        }

        if (ranking.isEmpty()) {
            Text("No hay puntuaciones registradas.")
            Spacer(Modifier.height(20.dp))

            Button(
                onClick = {
                    navController.navigate("inicio")
                }
            ) {
                Text("Volver al inicio")
            }

            return@Column
        }

        /*
        --------------------------------------------------
        Cabecera de tabla
        --------------------------------------------------
        */
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween
        ) {
            Text("Puesto", Modifier.weight(1f), fontWeight = FontWeight.Bold)
            Text("Nombre", Modifier.weight(2f), fontWeight = FontWeight.Bold)
            Text("Puntos", Modifier.weight(1f), fontWeight = FontWeight.Bold)
            Text("Tiempo", Modifier.weight(1f), fontWeight = FontWeight.Bold)
        }

        Spacer(Modifier.height(10.dp))

        /*
        --------------------------------------------------
        TOP 10
        --------------------------------------------------
        */
        LazyColumn(
            modifier = Modifier.weight(1f)
        ) {
            items(ranking) { player ->

                val color = when (player.posicion) {
                    1 -> Color(0xFFFFD700)
                    2 -> Color(0xFFC0C0C0)
                    3 -> Color(0xFFCD7F32)
                    else -> Color.Transparent
                }

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(color)
                        .padding(8.dp)
                ) {
                    Text("${player.posicion}", Modifier.weight(1f))
                    Text(player.nombre, Modifier.weight(2f))
                    Text("${player.puntuacion}", Modifier.weight(1f))
                    Text("${player.tiempo}s", Modifier.weight(1f))
                }
            }
        }

        Spacer(Modifier.height(20.dp))

        /*
        --------------------------------------------------
        Posición del usuario actual
        --------------------------------------------------
        */
        Text(
            text = "Tu posición",
            fontSize = 20.sp,
            fontWeight = FontWeight.Bold
        )

        Spacer(Modifier.height(10.dp))

        if (usuarioActual != null) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(Color.Cyan)
                    .padding(8.dp)
            ) {
                Text("${usuarioActual.posicion}", Modifier.weight(1f))
                Text(usuarioActual.nombre, Modifier.weight(2f))
                Text("${usuarioActual.puntuacion}", Modifier.weight(1f))
                Text("${usuarioActual.tiempo}s", Modifier.weight(1f))
            }
        } else {
            Text("Todavía no tienes puntuación registrada.")
        }

        Spacer(Modifier.height(20.dp))

        Button(
            onClick = {
                navController.navigate("inicio")
            }
        ) {
            Text("Volver al inicio")
        }
    }
}