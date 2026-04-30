package com.example.proyecto_genshiken

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable

/*
--------------------------------------------------
Navegación principal
--------------------------------------------------

Define las pantallas principales de la app.

Se mantiene la ruta "Juego" y también se añade
"juego" en minúscula porque alguna pantalla antigua
navega usando esa ruta.
*/
@Composable
fun Navegacion(navController: NavHostController) {

    NavHost(
        navController = navController,
        startDestination = "inicio"
    ) {
        composable("inicio") {
            PantallaInicio(navController)
        }

        composable("inicioSesionCasual") {
            InicioCasual(navController)
        }

        composable("inicioSesionCompeti") {
            InicioCompetitivo(navController)
        }

        composable("Ranking") {
            Ranking(navController)
        }

        composable("RegistroCasual") {
            RegistroCasual(navController)
        }

        composable("RegistroCompeti") {
            RegistroCompeti(navController)
        }

        composable("Juego") {
            Juego(navController)
        }

        composable("juego") {
            Juego(navController)
        }

        composable("cambiarNombre") {
            CambiarNombre(navController)
        }
    }
}