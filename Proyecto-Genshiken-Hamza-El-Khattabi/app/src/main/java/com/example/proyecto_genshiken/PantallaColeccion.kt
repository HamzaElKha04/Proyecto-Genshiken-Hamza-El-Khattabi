package com.example.proyecto_genshiken

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.res.painterResource

import androidx.compose.ui.graphics.Color
import androidx.navigation.NavController

@Composable
fun PantallaColeccion(navController: NavController) {



    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
    ) {

        items(EspadasData.lista) { espada ->

            val desbloqueada = GachaState.espadasDesbloqueadas.contains(espada.id)

            // el color de fondo cambiara según la rareza
            val colorFondo = when (espada.rareza) {
                Rareza.COMUN -> Color.LightGray
                Rareza.RARA -> Color.Cyan
                Rareza.EPICA -> Color.Magenta
                Rareza.LEGENDARIA -> Color.Yellow
            }

            // las estrellas que determinaran su rareza
            val estrellas = when (espada.rareza) {
                Rareza.COMUN -> 1
                Rareza.RARA -> 2
                Rareza.EPICA -> 3
                Rareza.LEGENDARIA -> 4
            }

            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(vertical = 8.dp),
                colors = CardDefaults.cardColors(
                    containerColor = if (desbloqueada)
                        colorFondo.copy(alpha = 0.2f)
                    else
                        Color.DarkGray.copy(alpha = 0.3f)
                )
            ) {

                Row(modifier = Modifier.padding(16.dp)) {

                    Image(
                        painter = painterResource(
                            if (desbloqueada)
                                espada.imagen
                            else
                                R.drawable.ic_launcher_foreground
                        ),
                        contentDescription = espada.nombre,
                        modifier = Modifier.size(80.dp)
                    )

                    Spacer(modifier = Modifier.width(16.dp))

                    Column {

                        Text(
                            text = if (desbloqueada) espada.nombre else "?????",
                            style = MaterialTheme.typography.titleMedium
                        )

                        Spacer(modifier = Modifier.height(4.dp))

                        if (desbloqueada) {

                            Text("⭐".repeat(estrellas))

                            Spacer(modifier = Modifier.height(4.dp))

                            Text(
                                text = espada.descripcion,
                                style = MaterialTheme.typography.bodyMedium
                            )

                        } else {
                            Text("Bloqueada")
                        }
                    }
                }
            }
        }
    }
}