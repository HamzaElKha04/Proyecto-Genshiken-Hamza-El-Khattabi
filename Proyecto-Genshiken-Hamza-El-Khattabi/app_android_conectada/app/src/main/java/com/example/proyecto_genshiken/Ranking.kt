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

        var ranking by remember { mutableStateOf<List<Player>>(emptyList()) }

        LaunchedEffect(Unit) {
            UserRepository.getRanking {
                ranking = it
            }
        }

        val sortedRanking = ranking.sortedByDescending { it.puntuacion }

        val top10 = sortedRanking.take(10)

        val userIndex = sortedRanking.indexOfFirst {
            it.nombre == UserSession.userName
        }

        val userPosition = userIndex + 1
        val userData = sortedRanking.getOrNull(userIndex)

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp),
            horizontalAlignment = Alignment.CenterHorizontally

        ) {
            Spacer(Modifier.height(40.dp))
            Text("RANKING", fontSize = 30.sp)


            Spacer(Modifier.height(20.dp))

            // LA CABECERA
            Row(Modifier.fillMaxWidth()) {
                Text("Puesto", Modifier.weight(1f))
                Text("Nombre", Modifier.weight(2f))
                Text("Puntuación", Modifier.weight(1f))
            }

            Spacer(Modifier.height(10.dp))

            // aqui se puede ver el TOP 10
            LazyColumn(
                modifier = Modifier.weight(1f)
            ) {

                itemsIndexed(top10) { index, player ->

                    val color = when (index) {
                        0 -> Color(0xFFFFD700) // este color es para el oro (la de letras que he tenido que probar para que salga el color que mas se pareciera...)
                        1 -> Color(0xFFC0C0C0) // Plata
                        2 -> Color(0xFFCD7F32) // Bronce
                        else -> Color.Transparent
                    }

                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .background(color)
                            .padding(8.dp)
                    ) {
                        Text("${index + 1}", Modifier.weight(1f))
                        Text(player.nombre, Modifier.weight(2f))
                        Text("${player.puntuacion}", Modifier.weight(1f))
                    }
                }
            }

            Spacer(Modifier.height(20.dp))

            // Aqui va el usuario
            Text("Tu posición", fontSize = 20.sp)

            Spacer(Modifier.height(10.dp))

            userData?.let {

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(Color.Cyan)
                        .padding(8.dp)
                ) {
                    Text("$userPosition", Modifier.weight(1f))
                    Text(it.nombre, Modifier.weight(2f))
                    Text("${it.puntuacion}", Modifier.weight(1f))
                }
            }

            Spacer(Modifier.height(20.dp))

            Button(onClick = {
                navController.navigate("inicio")
            }) {
                Text("Volver al inicio")
            }
        }
    }
