package com.example.proyecto_genshiken

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController

@Composable
fun InicioCompetitivo(navController: NavHostController) {

    var email by remember {
        mutableStateOf("")
    }

    var contraseña by remember {
        mutableStateOf("")
    }

    var mensajeError by remember {
        mutableStateOf("")
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(
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
                .verticalScroll(rememberScrollState())
                .padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {

            Spacer(modifier = Modifier.height(20.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically
            ) {

                Text(
                    text = "← Volver",
                    fontSize = 18.sp,
                    color = Color.White,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.clickable {
                        navController.navigate("inicio")
                    }
                )
            }

            Spacer(modifier = Modifier.height(30.dp))

            Text(
                text = "Modo Competitivo",
                fontSize = 34.sp,
                fontWeight = FontWeight.ExtraBold,
                color = Color.White,
                textAlign = TextAlign.Center
            )

            Spacer(modifier = Modifier.height(12.dp))

            Text(
                text = "Enfréntate a otros jugadores, consigue la mejor puntuación y escala posiciones en el ranking global.",
                fontSize = 16.sp,
                color = Color(0xFFE5E7EB),
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(horizontal = 8.dp)
            )

            Spacer(modifier = Modifier.height(36.dp))

            Card(
                modifier = Modifier.fillMaxWidth(),
                shape = RoundedCornerShape(24.dp),
                colors = CardDefaults.cardColors(
                    containerColor = Color.White.copy(alpha = 0.96f)
                ),
                elevation = CardDefaults.cardElevation(
                    defaultElevation = 10.dp
                )
            ) {

                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(24.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {

                    Text(
                        text = "Iniciar Sesión",
                        fontSize = 26.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color(0xFF1E3A8A)
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    OutlinedTextField(
                        value = email,
                        onValueChange = {
                            email = it
                        },
                        label = {
                            Text("Correo electrónico")
                        },
                        singleLine = true,
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp)
                    )

                    Spacer(modifier = Modifier.height(18.dp))

                    OutlinedTextField(
                        value = contraseña,
                        onValueChange = {
                            contraseña = it
                        },
                        label = {
                            Text("Contraseña")
                        },
                        singleLine = true,
                        visualTransformation = PasswordVisualTransformation(),
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp)
                    )

                    Spacer(modifier = Modifier.height(26.dp))

                    Button(
                        onClick = {

                            UserRepository.login(
                                email,
                                contraseña
                            ) { success, id, nombre ->

                                if (success) {

                                    UserSession.userId = id
                                    UserSession.userName = nombre

                                    /*
                                    --------------------------------------------------
                                    Registrar instalación
                                    --------------------------------------------------

                                    Solo se registra cuando el usuario inicia sesión
                                    correctamente en modo competitivo.
                                    */
                                    UserRepository.registrarDescarga(
                                        usuarioId = id,
                                        nombreUsuario = nombre
                                    )

                                    UserRepository.obtenerMonedas(id) { monedas ->

                                        GachaState.monedas.value =
                                            monedas
                                    }

                                    UserRepository.obtenerEspadas(id) { espadas ->

                                        GachaState
                                            .espadasDesbloqueadas
                                            .clear()

                                        GachaState
                                            .espadasDesbloqueadas
                                            .addAll(espadas)
                                    }

                                    navController.navigate("Juego")

                                } else {

                                    mensajeError =
                                        "Correo no verificado o datos incorrectos"
                                }
                            }
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(55.dp),
                        shape = RoundedCornerShape(16.dp),
                        colors = ButtonDefaults.buttonColors(
                            containerColor = Color(0xFFFACC15),
                            contentColor = Color(0xFF111827)
                        )
                    ) {

                        Text(
                            text = "Entrar",
                            fontSize = 16.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }

                    Spacer(modifier = Modifier.height(14.dp))

                    if (mensajeError.isNotEmpty()) {

                        Text(
                            text = mensajeError,
                            color = MaterialTheme.colorScheme.error,
                            textAlign = TextAlign.Center
                        )
                    }

                    Spacer(modifier = Modifier.height(24.dp))

                    OutlinedButton(
                        onClick = {
                            navController.navigate(
                                "RegistroCompeti"
                            )
                        },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp),
                        border = BorderStroke(
                            1.dp,
                            Color(0xFF1E3A8A)
                        ),
                        colors = ButtonDefaults.outlinedButtonColors(
                            contentColor = Color(0xFF1E3A8A)
                        )
                    ) {

                        Text(
                            text = "¿No tienes cuenta? Creala",
                            fontWeight = FontWeight.Bold
                        )
                    }

                    Spacer(modifier = Modifier.height(14.dp))

                    Text(
                        text = "Reenviar correo de verificación",
                        color = Color(0xFF2563EB),
                        fontWeight = FontWeight.SemiBold,
                        modifier = Modifier.clickable {

                            UserRepository.reenviarVerificacion(email) {

                                mensajeError = when (it) {

                                    "ENVIADO" ->
                                        "Correo enviado correctamente"

                                    "YA_VERIFICADO" ->
                                        "La cuenta ya está verificada"

                                    else ->
                                        "Error enviando correo"
                                }
                            }
                        }
                    )
                }
            }

            Spacer(modifier = Modifier.height(30.dp))

            Text(
                text = "Modo Competitivo GenshikenC.S.",
                color = Color(0xFF94A3B8),
                fontSize = 13.sp
            )

            Spacer(modifier = Modifier.height(20.dp))
        }
    }
}
