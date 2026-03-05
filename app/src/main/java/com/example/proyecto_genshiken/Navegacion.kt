package com.example.proyecto_genshiken
import InicioCompetitivo
import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable

@Composable
fun Navegacion(navController: NavHostController){
        // esta clase sirve para definir que paginas llevaran a que otras.
    NavHost(
        navController = navController,
        startDestination = "inicio"
    ){
        composable ("inicio"){
            PantallaInicio(navController)

        }
        composable ("inicioSesionCasual") {
            InicioCasual(navController)


        }
        composable ("inicioSesionCompeti"){
            InicioCompetitivo(navController)
        }
        composable ("Ranking") {
            Ranking(navController)
        }
        composable ("RegistroCasual"){
            RegistroCasual( navController)
        }
        composable ("RegistroCompeti"){
            RegistroCompeti(navController)
        }
        composable("Juego"){

        }
    }
}