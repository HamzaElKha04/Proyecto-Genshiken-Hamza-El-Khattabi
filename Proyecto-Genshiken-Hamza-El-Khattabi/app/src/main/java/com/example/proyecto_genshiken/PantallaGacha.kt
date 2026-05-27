package com.example.proyecto_genshiken

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Card
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.IconButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.Icon
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import androidx.compose.material3.*
import androidx.compose.material3.Icon
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Settings
import androidx.compose.ui.graphics.Color


@Composable


fun PantallaGacha(navController: NavController) {

    var resultado by remember { mutableStateOf<Espada?>(null) }


    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(Modifier.height(42.dp))

        Text(" Gachapon Genshiken", style = MaterialTheme.typography.headlineMedium)

        Spacer(Modifier.height(16.dp))

        Text("Monedas: ${GachaState.monedas.value}")

        Spacer(Modifier.height(40.dp))

        Button(
            modifier = Modifier
                .fillMaxWidth()
                .height(60.dp),
            onClick = {

                if (GachaState.monedas.value >= 10) {

                    GachaState.monedas.value -= 10

                    val espada = tirarGacha()
                    resultado = espada

                    GachaState.añadirEspada(espada.id)

                    UserRepository.guardarMonedas(
                        UserSession.userId,
                        GachaState.monedas.value
                    )

                    UserRepository.guardarEspada(
                        UserSession.userId,
                        espada.id
                    )
                }
            }
        ) {
            Text("✨ INVOCAR ✨", fontSize = 18.sp)
        }

        Spacer(Modifier.height(40.dp))

        resultado?.let { espada ->


            val colorFondo = when (espada.rareza) {
                Rareza.COMUN -> Color.Gray
                Rareza.RARA -> Color.Cyan
                Rareza.EPICA -> Color.Magenta
                Rareza.LEGENDARIA -> Color.Yellow
            }

            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                shape = MaterialTheme.shapes.large,
                elevation = CardDefaults.cardElevation(8.dp),
                        colors = CardDefaults.cardColors(
                        containerColor = colorFondo
                        )
            ) {

                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {

                    Image(
                        painter = painterResource(espada.imagen),
                        contentDescription = espada.nombre,
                        modifier = Modifier
                            .size(180.dp)
                    )

                    Spacer(Modifier.height(16.dp))

                    Text(
                        espada.nombre,
                        style = MaterialTheme.typography.titleLarge
                    )

                    Spacer(Modifier.height(8.dp))
                    val colorFondo = when (espada.rareza) {
                        Rareza.COMUN -> Color.LightGray
                        Rareza.RARA -> Color.Cyan
                        Rareza.EPICA -> Color.Magenta
                        Rareza.LEGENDARIA -> Color.Yellow
                    }

                    val estrellas = when (espada.rareza) {
                        Rareza.COMUN -> 1
                        Rareza.RARA -> 2
                        Rareza.EPICA -> 3
                        Rareza.LEGENDARIA -> 4
                    }
                    Text(
                        "⭐".repeat(estrellas),
                        fontSize = 20.sp
                    )
                }
            }
        }

        Spacer(Modifier.height(30.dp))

        Button(onClick = {
            navController.navigate("coleccion")
        }) {
            Text(" Colección")
        }
    }
}