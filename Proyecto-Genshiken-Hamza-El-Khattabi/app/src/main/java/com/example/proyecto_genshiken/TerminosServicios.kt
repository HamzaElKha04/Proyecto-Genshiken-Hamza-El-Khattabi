package com.example.proyecto_genshiken

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController

@Composable
fun TerminosServicios(
    navController: NavController
) {

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    listOf(
                        Color(0xFF101827),
                        Color(0xFF1E293B),
                        Color(0xFF0F172A)
                    )
                )
            )
    ) {

        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(
                    rememberScrollState()
                )
                .padding(20.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            Text(
                text = "Términos y Servicios",

                fontSize = 30.sp,

                fontWeight =
                    FontWeight.ExtraBold,

                color = Color.White
            )

            Spacer(
                Modifier.height(20.dp)
            )

            Card(
                colors = CardDefaults.cardColors(
                    containerColor =
                        Color.White
                )
            ) {

                Text(
                    text =
                        """
Al crear una cuenta aceptas:

• Utilizar la aplicación de forma legítima.

• No manipular puntuaciones ni rankings.

• No utilizar software externo para alterar el funcionamiento del juego.

• El nombre de usuario podrá mostrarse públicamente en los rankings.

• El correo electrónico se utilizará únicamente para verificación de cuenta y recuperación de acceso.

• El incumplimiento de estas normas puede provocar la suspensión de la cuenta.

Al continuar aceptas estas condiciones.
                    """.trimIndent(),

                    modifier = Modifier.padding(20.dp)
                )
            }

            Spacer(
                Modifier.height(20.dp)
            )

            Button(
                onClick = {

                    navController.popBackStack()
                },

                colors = ButtonDefaults.buttonColors(
                    containerColor =
                        Color(0xFFFACC15),

                    contentColor =
                        Color.Black
                )
            ) {

                Text(
                    "Volver al registro"
                )
            }
        }
    }
}