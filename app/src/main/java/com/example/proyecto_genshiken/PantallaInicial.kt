package com.example.proyecto_genshiken

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import com.example.proyecto_genshiken.ui.theme.Proyecto_GenshikenTheme
import androidx.compose.foundation.Image
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.ui.Alignment
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import androidx.navigation.NavHostController
import androidx.navigation.compose.rememberNavController



@Composable
fun PantallaInicio(navController: NavController) {
        //  Aqui tenemos la pantalla inicial, ojo, te dara error en las imagenes. de igual manera, te aviso cuando llega esa parte jaja
    Box(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp)
    ) {

        Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            modifier = Modifier.fillMaxSize()
        ) {

            Spacer(modifier = Modifier.height(20.dp))

            Text(
                text = "Bienvenido a la aplicación",
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "Seleccione el modo al que desea jugar",
                fontSize = 16.sp
            )

            Spacer(modifier = Modifier.height(40.dp))

            // Esta parte te dara error puesto que no tienes las imagenes. tampoco son importantes de momento, las puse para ver como se veian y cuanto podian ocupar, habria que hacer o que nos den las definitivas. sino siempre se puede poner un boton aqui
            Image(
                painter = painterResource(id = R.drawable.espadacasual),
                contentDescription = "Casual",
                modifier = Modifier
                    .fillMaxWidth()
                    .height(120.dp)
                    .clickable {
                        navController.navigate("inicioSesionCasual")
                    },
                contentScale = ContentScale.Fit
            )

            Spacer(modifier = Modifier.height(40.dp))

            // lo mismo PARA EL COMPETI
            Image(
                painter = painterResource(id = R.drawable.espadacasual),
                contentDescription = "Competitivo",
                modifier = Modifier
                    .fillMaxWidth()
                    .height(120.dp)
                    .clickable {
                        if (UserSession.userId==0) {
                            navController.navigate("inicioSesionCompeti")
                        } else{
                            navController.navigate("Juego")
                        }
                    },
                contentScale = ContentScale.Fit
            )

            Spacer(modifier = Modifier.height(60.dp))

            // Ranking
            Image(
                painter = painterResource(id = R.drawable.corona),
                contentDescription = "Ranking",
                modifier = Modifier
                    .size(180.dp)
                    .clickable {
                        navController.navigate("Ranking")
                    },
                contentScale = ContentScale.Fit
            )
        }
    }
}

