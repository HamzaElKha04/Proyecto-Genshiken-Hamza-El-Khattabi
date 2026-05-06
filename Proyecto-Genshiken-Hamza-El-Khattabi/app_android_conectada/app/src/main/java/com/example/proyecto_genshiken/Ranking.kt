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

Esta pantalla muestra el ranking real de la app.

Antes el ranking podía funcionar con datos locales
o de prueba. Ahora se conecta al backend PHP mediante
UserRepository.getRanking(), que llama a getRanking.php.

Flujo:
Android Ranking.kt
    -> UserRepository.getRanking()
    -> ApiService.getRanking()
    -> getRanking.php
    -> tabla puntuaciones en MySQL

El orden del ranking lo decide el backend:
- más puntos primero
- si hay empate, menos tiempo primero
- si sigue el empate, fecha más antigua primero
*/
@Composable
fun Ranking(navController: NavController) {

    /*
    --------------------------------------------------
    Estado de pantalla
    --------------------------------------------------

    ranking:
    Lista de jugadores recibida desde la API.

    cargando:
    Controla si todavía estamos esperando respuesta
    del backend.
    */
    var ranking by remember { mutableStateOf<List<Player>>(emptyList()) }
    var cargando by remember { mutableStateOf(true) }

    /*
    --------------------------------------------------
    Carga inicial del ranking
    --------------------------------------------------

    LaunchedEffect(Unit) se ejecuta una vez al abrir
    la pantalla.

    Aquí pedimos el ranking real al backend.
    */
    LaunchedEffect(Unit) {
        UserRepository.getRanking { listaRanking ->
            ranking = listaRanking
            cargando = false
        }
    }

    /*
    --------------------------------------------------
    Buscar posición del usuario actual
    --------------------------------------------------

    Se usa el nombre guardado en UserSession tras login.
    Si el usuario aparece en el ranking, se muestra abajo
    en "Tu posición".
    */
    val usuarioActual = ranking.firstOrNull { jugador ->
        jugador.nombre.equals(UserSession.userName, ignoreCase = true)
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Spacer(modifier = Modifier.height(40.dp))

        Text(
            text = "RANKING",
            fontSize = 30.sp,
            fontWeight = FontWeight.Bold
        )

        Spacer(modifier = Modifier.height(20.dp))

        /*
        --------------------------------------------------
        Estado cargando
        --------------------------------------------------
        */
        if (cargando) {
            Text(text = "Cargando ranking...")
            return@Column
        }

        /*
        --------------------------------------------------
        Ranking vacío
        --------------------------------------------------
        */
        if (ranking.isEmpty()) {
            Text(text = "No hay puntuaciones registradas.")

            Spacer(modifier = Modifier.height(20.dp))

            Button(
                onClick = {
                    navController.navigate("inicio")
                }
            ) {
                Text(text = "Volver al inicio")
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
            Text(
                text = "Puesto",
                modifier = Modifier.weight(1f),
                fontWeight = FontWeight.Bold
            )

            Text(
                text = "Nombre",
                modifier = Modifier.weight(2f),
                fontWeight = FontWeight.Bold
            )

            Text(
                text = "Puntos",
                modifier = Modifier.weight(1f),
                fontWeight = FontWeight.Bold
            )

            Text(
                text = "Tiempo",
                modifier = Modifier.weight(1f),
                fontWeight = FontWeight.Bold
            )
        }

        Spacer(modifier = Modifier.height(10.dp))

        /*
        --------------------------------------------------
        Listado del TOP ranking
        --------------------------------------------------

        Los tres primeros puestos se resaltan:
        1º oro
        2º plata
        3º bronce
        */
        LazyColumn(
            modifier = Modifier.weight(1f)
        ) {
            items(items = ranking) { jugador ->

                val colorFila = when (jugador.posicion) {
                    1 -> Color(0xFFFFD700)
                    2 -> Color(0xFFC0C0C0)
                    3 -> Color(0xFFCD7F32)
                    else -> Color.Transparent
                }

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(colorFila)
                        .padding(8.dp)
                ) {
                    Text(
                        text = jugador.posicion.toString(),
                        modifier = Modifier.weight(1f)
                    )

                    Text(
                        text = jugador.nombre,
                        modifier = Modifier.weight(2f)
                    )

                    Text(
                        text = jugador.puntuacion.toString(),
                        modifier = Modifier.weight(1f)
                    )

                    Text(
                        text = "${jugador.tiempo}s",
                        modifier = Modifier.weight(1f)
                    )
                }
            }
        }

        Spacer(modifier = Modifier.height(20.dp))

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

        Spacer(modifier = Modifier.height(10.dp))

        if (usuarioActual != null) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(Color.Cyan)
                    .padding(8.dp)
            ) {
                Text(
                    text = usuarioActual.posicion.toString(),
                    modifier = Modifier.weight(1f)
                )

                Text(
                    text = usuarioActual.nombre,
                    modifier = Modifier.weight(2f)
                )

                Text(
                    text = usuarioActual.puntuacion.toString(),
                    modifier = Modifier.weight(1f)
                )

                Text(
                    text = "${usuarioActual.tiempo}s",
                    modifier = Modifier.weight(1f)
                )
            }
        } else {
            Text(text = "Todavía no tienes puntuación registrada.")
        }

        Spacer(modifier = Modifier.height(20.dp))

        Button(
            onClick = {
                navController.navigate("inicio")
            }
        ) {
            Text(text = "Volver al inicio")
        }
    }
}