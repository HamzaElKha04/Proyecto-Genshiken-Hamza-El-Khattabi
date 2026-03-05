package com.example.proyecto_genshiken


import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import com.example.proyecto_genshiken.Player

@Composable
fun Ranking(navController: NavController) {

    // Esto sería el usuario en cuestión, habra que cambiarlo a futuro con los datos de la partida
    val usuarioActual = Player("Di_patata", 4800)

    // Lista de ejemplo, habra que adaptarla a la base de datos d:
    val jugadores = listOf(
        Player("El Choso que gira",13500),
        Player("Kuru-Kuru",12700),
        Player("St.Louis Smash",11000),
        Player("La Shogun Raiden",10500),
        Player("Elisabeth Liones",10300),
        Player("GalaChispas@Oficial",10100),
        Player("Sasukeeeee",9900),
        Player("Narutooooo",9700),
        Player("GentleCriminal_YT",9500),
        Player("El Chambas",9400),
        Player("JIJI JIJA",9300),
        usuarioActual
    )

    // con esto ordenaremos el ranking por el numero de puntuacion
    val ranking = jugadores.sortedByDescending { it.puntuacion }

    // con esto encontraremos donde esta el jugador en cuestion.
    val posicionUsuario = ranking.indexOf(usuarioActual) + 1

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(20.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
            Spacer(modifier = Modifier.height(30.dp))
        Text(
            text = "RANKING",
            fontSize = 32.sp
        )

        Spacer(modifier = Modifier.height(20.dp))

        RankingTabla()

        //con esto, solamente escogeremos los 10 jugadores del ranking junto a sus puntuaciones
        val top10 = ranking.take(10)

        //creamos una columna a parte para el jugador, ya que querrá saber donde esta en el ranking
        LazyColumn {

            itemsIndexed(top10) { index, player ->
                RankingPosicion(index + 1, player)
            }

            item {

                Spacer(modifier = Modifier.height(20.dp))

                Text(
                    text = "Tu posición",
                    fontSize = 18.sp,
                    textAlign = TextAlign.Center,
                    color = Color.Green,
                    fontWeight = FontWeight.Bold
                )

                RankingPosicion(posicionUsuario, usuarioActual)
            }
        }

        Spacer(modifier = Modifier.height(60.dp))

        Button(
            onClick = {
                navController.navigate("inicio")
            }
        ) {
            Text("Volver al inicio")
        }
    }
}

@Composable
fun RankingTabla() {

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .border(1.dp, Color.Black)
            .background(Color.LightGray)
            .padding(8.dp)
    ) {

        Text(
            text = "Puesto",
            modifier = Modifier.weight(1f)
        )

        Text(
            text = "Nombre",
            modifier = Modifier.weight(1f)
        )

        Text(
            text = "Puntuación",
            modifier = Modifier.weight(1f)
        )
    }
}

@Composable
fun RankingPosicion(puesto: Int, player: Player) {

    val color = when (puesto) {
        1 -> Color.Yellow
        2 -> Color.LightGray
        3 -> Color(0xFFCD7F32) // Este es el color mas cercano al bronce que he encontrado jajaja
        else -> Color.Transparent
    }

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .border(1.dp, Color.Black)
            .background(color)
            .padding(8.dp)
    ) {

        Text(
            text = "${puesto}º",
            modifier = Modifier.weight(1f)
        )

        Text(
            text = player.nombre,
            modifier = Modifier.weight(2f)
        )

        Text(
            text = player.puntuacion.toString(),
            modifier = Modifier.weight(1f)
        )
    }
}