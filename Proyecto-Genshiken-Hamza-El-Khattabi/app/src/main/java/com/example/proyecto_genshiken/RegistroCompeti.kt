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
import androidx.compose.foundation.layout.width
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
import androidx.compose.material3.Checkbox
import androidx.compose.material3.CheckboxDefaults

@Composable
fun RegistroCompeti(navController: NavHostController) {

    var usuario by RegistroState.usuario

    var email by RegistroState.email

    var contraseña by RegistroState.contraseña

    var aceptaTerminos by RegistroState.aceptaTerminos

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

            /*
            --------------------------------------------------
            Flecha volver
            --------------------------------------------------
            */

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

            /*
            --------------------------------------------------
            TITULO
            --------------------------------------------------
            */

            Text(
                text = "Crear Cuenta",
                fontSize = 34.sp,
                fontWeight = FontWeight.ExtraBold,
                color = Color.White
            )

            Spacer(modifier = Modifier.height(10.dp))

            Text(
                text = "Regístrate para competir en el ranking global y demostrar quién es el verdadero maestro de las espadas.",
                fontSize = 16.sp,
                color = Color(0xFFE5E7EB),
                textAlign = TextAlign.Center,
                modifier = Modifier.padding(horizontal = 8.dp)
            )

            Spacer(modifier = Modifier.height(35.dp))

            /*
            --------------------------------------------------
            CARD PRINCIPAL DONDE ESTAN LOS CAMPOS
            --------------------------------------------------
            */

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
                        text = "Registro competitivo",
                        fontSize = 24.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color(0xFF1E3A8A)
                    )

                    Spacer(modifier = Modifier.height(20.dp))



                    Spacer(modifier = Modifier.height(25.dp))



                    OutlinedTextField(
                        value = usuario,
                        onValueChange = {
                            usuario = it
                        },
                        label = {
                            Text("Nombre de usuario")
                        },
                        singleLine = true,
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp)
                    )

                    Spacer(modifier = Modifier.height(18.dp))



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
                        visualTransformation =
                            PasswordVisualTransformation(),
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(16.dp)
                    )

                    Spacer(modifier = Modifier.height(20.dp))

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        verticalAlignment = Alignment.CenterVertically
                    ) {

                        Checkbox(
                            checked = aceptaTerminos,
                            onCheckedChange = {
                                aceptaTerminos = it
                            },
                            colors = CheckboxDefaults.colors(
                                checkedColor = Color(0xFF1E3A8A)
                            )
                        )

                        Text(
                            text = "Acepto los términos y servicios",
                            color = Color(0xFF1E3A8A),
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.clickable {
                                navController.navigate("terminos")
                            }
                        )
                    }

                    Button(
                        onClick = {

                            if (
                                contraseña.isBlank() ||
                                usuario.isBlank() ||
                                email.isBlank()
                            ) {

                                mensajeError =
                                    "Todos los campos deben completarse"

                                return@Button
                            }

                            if (contraseña.length < 8) {

                                mensajeError =
                                    "La contraseña debe tener mínimo 8 caracteres"

                                return@Button
                            }

                            if (usuario.length > 20) {

                                mensajeError =
                                    "El nombre de usuario es demasiado largo"

                                return@Button
                            }
                            if (!aceptaTerminos) {

                                mensajeError =
                                    "Debes aceptar los términos y servicios"

                                return@Button
                            }
                            UserRepository.register(
                                usuario,
                                email,
                                contraseña
                            ) {

                                when (it) {

                                    "EXISTE" -> {

                                        mensajeError =
                                            "Ese correo ya está registrado"
                                    }

                                    "OK" -> {

                                        mensajeError =
                                            "Te hemos enviado un correo de verificación"

                                        navController.navigate(
                                            "inicioSesionCompeti"
                                        )
                                    }

                                    else -> {

                                        mensajeError = it
                                    }
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
                            text = "Crear cuenta",
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

                    Spacer(modifier = Modifier.height(20.dp))



                    OutlinedButton(
                        onClick = {
                            navController.navigate(
                                "inicioSesionCompeti"
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
                            text = "¿Ya tienes cuenta? Inicia Sesión",
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(20.dp))



            Text(
                text = "Modo competitivo GenshikenC.S.",
                color = Color(0xFF94A3B8),
                fontSize = 13.sp
            )

            Spacer(modifier = Modifier.height(20.dp))
        }
    }
}