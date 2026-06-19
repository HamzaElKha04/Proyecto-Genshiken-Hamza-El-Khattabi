package com.example.proyecto_genshiken

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.compose.rememberNavController
import com.example.proyecto_genshiken.ui.theme.Proyecto_GenshikenTheme

class MainActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {

        super.onCreate(savedInstanceState)

        enableEdgeToEdge()

        setContent {

            val context = LocalContext.current

            ThemeState.isDarkMode.value =
                ThemePreferences.loadDarkMode(context)

            Proyecto_GenshikenTheme {

                Surface(
                    color = MaterialTheme.colorScheme.background
                ) {

                    val navController =
                        rememberNavController()

                    Navegacion(navController)
                }
            }
        }
    }

    /*
    --------------------------------------------------
    Cierre de la app
    --------------------------------------------------

    Cuando se destruye la actividad, se libera la música
    para evitar que siga sonando en segundo plano.
    */
    override fun onDestroy() {
        super.onDestroy()

        MusicManager.pararMusica()
    }
}
