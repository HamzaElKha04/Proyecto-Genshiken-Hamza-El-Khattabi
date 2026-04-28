package com.example.proyecto_genshiken

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.Composable
import androidx.compose.ui.tooling.preview.Preview
import androidx.navigation.compose.rememberNavController
import com.example.proyecto_genshiken.ui.theme.Proyecto_GenshikenTheme

class MainActivity : ComponentActivity() {

    // esta clase solo se usa para arrancar el programa, como verás, el navController te lleva a la pantalla inicial, que es la que está marcada como NavHost.
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {

            val context = this

            // Aqui es donde voy a cargar el modo elegido por el usuario a la hora de volver a iniciar la app, para que asi el modo oscuro o claro prevalezca hasta que se cambie de nuevo la opcion
            ThemeState.isDarkMode.value = ThemePreferences.loadDarkMode(context)

            Proyecto_GenshikenTheme {
                Surface(color = MaterialTheme.colorScheme.background) {
                    val navController = rememberNavController()
                    Navegacion(navController)
                }
            }
        }
    }
}
