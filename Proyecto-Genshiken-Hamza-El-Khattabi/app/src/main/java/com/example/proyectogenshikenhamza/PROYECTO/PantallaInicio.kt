package com.example.proyectogenshikenhamza.PROYECTO

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import com.example.proyectogenshikenhamza.R

@Composable
fun PantallaInicio(navController: NavController) {

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {

        Text(
            text = "Bienvenido a la aplicación",
            fontSize = 28.sp,
            fontWeight = FontWeight.Bold
        )

        Spacer(modifier = Modifier.height(12.dp))

        Text(
            text = "Seleccione el modo al que desea jugar",
            fontSize = 16.sp
        )

        Spacer(modifier = Modifier.height(40.dp))


        // ESPADA MODO CASUAL
        Image(
            painter = painterResource(id = R.drawable.espadacasual),
            contentDescription = "Modo Casual",
            modifier = Modifier.size(120.dp)
        )

        Spacer(modifier = Modifier.height(8.dp))

        Button(
            onClick = { navController.navigate("inicio_sesion_casual") },
            modifier = Modifier.fillMaxWidth()
        ) {
            Text("Modo Casual")
        }


        Spacer(modifier = Modifier.height(30.dp))


        // ESPADA MODO COMPETITIVO
        Image(
            painter = painterResource(id = R.drawable.espadacasual),
            contentDescription = "Modo Competitivo",
            modifier = Modifier.size(120.dp)
        )

        Spacer(modifier = Modifier.height(8.dp))

        Button(
            onClick = { navController.navigate("inicio_sesion_competi") },
            modifier = Modifier.fillMaxWidth()
        ) {
            Text("Modo Competitivo")
        }


        Spacer(modifier = Modifier.height(30.dp))


        // CORONA RANKING
        Image(
            painter = painterResource(id = R.drawable.corona),
            contentDescription = "Ranking",
            modifier = Modifier.size(100.dp)
        )

        Spacer(modifier = Modifier.height(8.dp))

        Button(
            onClick = { navController.navigate("ranking") },
            modifier = Modifier.fillMaxWidth()
        ) {
            Text("Ranking")
        }
    }
}