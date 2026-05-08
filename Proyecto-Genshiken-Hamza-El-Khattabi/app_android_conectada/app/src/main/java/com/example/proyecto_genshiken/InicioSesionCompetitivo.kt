package com.example.proyecto_genshiken

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController

/*
--------------------------------------------------
Pantalla de inicio de sesión competitivo
--------------------------------------------------

Esta pantalla permite iniciar sesión contra el
backend PHP.

Cuando el login es correcto:
- guarda el id y el nombre en UserSession
- registra el uso/descarga en registrarDescarga.php
- entra al juego

Con esto se comprueba el flujo:
Android → login.php → MySQL
Android → registrarDescarga.php → admin/descargas.php
*/
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

    var cargando by remember {
        mutableStateOf(false)
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "←",
                fontSize = 24.sp,
                modifier = Modifier.clickable {
                    navController.navigate("inicio")
                }
            )
        }

        Spacer(modifier = Modifier.height(20.dp))

        Text(
            text = "Modo Competitivo",
            fontSize = 32.sp,
            fontWeight = FontWeight.Bold
        )

        Spacer(modifier = Modifier.height(16.dp))

        Text(
            text = "Bienvenido al modo competitivo, demuestra tu conocimiento acerca del maravilloso mundo de las espadas frente a otros usuarios. ¡Con la oportunidad de llevarte un jugoso PREMIO!",
            fontSize = 16.sp
        )

        Spacer(modifier = Modifier.height(40.dp))

        Card(
            modifier = Modifier.fillMaxWidth(),
        ) {
            Column(
                modifier = Modifier.padding(20.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {

                Text(
                    text = "INICIA SESION",
                    fontSize = 22.sp,
                    fontWeight = FontWeight.Bold
                )

                Spacer(modifier = Modifier.height(20.dp))

                /*
                --------------------------------------------------
                Campo email
                --------------------------------------------------
                */
                OutlinedTextField(
                    value = email,
                    onValueChange = { email = it },
                    label = { Text("Correo Electrónico") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(modifier = Modifier.height(16.dp))

                /*
                --------------------------------------------------
                Campo contraseña
                --------------------------------------------------
                */
                OutlinedTextField(
                    value = contraseña,
                    onValueChange = { contraseña = it },
                    label = { Text("Contraseña") },
                    singleLine = true,
                    visualTransformation = PasswordVisualTransformation(),
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(modifier = Modifier.height(20.dp))

                /*
                --------------------------------------------------
                Botón login
                --------------------------------------------------

                Primero valida campos vacíos.
                Después llama a login.php.
                Si el login es correcto, registra descarga/uso.
                */
                Button(
                    enabled = !cargando,
                    onClick = {

                        if (email.isBlank() || contraseña.isBlank()) {
                            mensajeError = "No pueden haber campos sin información."
                            return@Button
                        }

                        cargando = true
                        mensajeError = ""

                        UserRepository.login(email, contraseña) { success, id, nombre ->

                            cargando = false

                            if (success) {

                                UserSession.userId = id
                                UserSession.userName = nombre

                                /*
                                --------------------------------------------------
                                Registro de descarga / uso
                                --------------------------------------------------

                                No bloqueamos la entrada al juego aunque falle.
                                Si falla, el usuario puede jugar igualmente.
                                */
                                UserRepository.registrarDescarga(
                                    nombreUsuario = nombre,
                                    usuarioId = id
                                )

                                navController.navigate("Juego")

                            } else {
                                mensajeError = "Correo o contraseña incorrectos."
                            }
                        }
                    },
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text(
                        text = if (cargando) "Comprobando..." else "Enviar"
                    )
                }

                Spacer(modifier = Modifier.height(10.dp))

                if (mensajeError.isNotEmpty()) {
                    Text(
                        text = mensajeError,
                        color = MaterialTheme.colorScheme.error
                    )
                }

                Spacer(modifier = Modifier.height(20.dp))

                Text(
                    text = "¿No tienes cuenta? Regístrate",
                    color = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.clickable {
                        navController.navigate("RegistroCompeti")
                    }
                )
            }
        }
    }
}