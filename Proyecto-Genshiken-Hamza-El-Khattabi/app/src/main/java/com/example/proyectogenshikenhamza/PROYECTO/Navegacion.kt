package com.example.proyectogenshikenhamza.PROYECTO

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable

@Composable
fun Navegacion(navController: NavHostController) {
    NavHost(
        navController = navController,
        startDestination = "inicio"
    ) {
        composable("inicio") {
            PantallaInicio(navController)
        }

        composable("inicio_sesion_casual") {
            InicioCasual(navController)
        }

        composable("inicio_sesion_competi") {
            InicioCompetitivo(navController)
        }

        composable("ranking") {
            Ranking(navController)
        }

        composable("registro_casual") {
            RegistroCasual(navController)
        }

        composable("registro_competi") {
            RegistroCompeti(navController)
        }

        composable("juego") {
            PantallaJuego()
        }
    }
}

@Composable
fun PantallaJuego() {
    Column(
        modifier = Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Pantalla de juego")
    }
}