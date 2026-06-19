package com.example.proyecto_genshiken

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController

@Composable
fun CambiarNombre(navController: NavHostController) {

    var nombreActual by remember {
        mutableStateOf("")
    }

    var nuevoNombre by remember {
        mutableStateOf("")
    }

    var email by remember {
        mutableStateOf("")
    }

    var password by remember {
        mutableStateOf("")
    }

    var mensaje by remember {
        mutableStateOf("")
    }

    var cargando by remember {
        mutableStateOf(false)
    }



    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    listOf(
                        Color(0xFF0F172A),
                        Color(0xFF1E1B4B),
                        Color(0xFF111827)
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
                .padding(24.dp),

            horizontalAlignment =
                Alignment.CenterHorizontally
        ) {

            Spacer(
                Modifier.height(40.dp)
            )



            Text(
                text = " CAMBIAR NOMBRE ",

                fontSize = 30.sp,

                fontWeight =
                    FontWeight.ExtraBold,

                color = Color.White
            )

            Spacer(
                Modifier.height(40.dp)
            )

            /*
            ----------------------------------------
            El formulario con los campos que hay que rellenar
            ----------------------------------------
            */

            Card(
                modifier = Modifier.fillMaxWidth(),

                shape = RoundedCornerShape(28.dp),

                colors = CardDefaults.cardColors(
                    containerColor =
                        Color.White.copy(alpha = 0.08f)
                )
            ) {

                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(22.dp)
                ) {

                    OutlinedTextField(
                        value = nombreActual,

                        onValueChange = {
                            nombreActual = it
                        },

                        label = {
                            Text("Nombre actual")
                        },

                        singleLine = true,

                        modifier = Modifier
                            .fillMaxWidth(),

                        shape = RoundedCornerShape(
                            18.dp
                        )
                    )

                    Spacer(
                        Modifier.height(18.dp)
                    )

                    OutlinedTextField(
                        value = nuevoNombre,

                        onValueChange = {
                            nuevoNombre = it
                        },

                        label = {
                            Text("Nuevo nombre")
                        },

                        singleLine = true,

                        modifier = Modifier
                            .fillMaxWidth(),

                        shape = RoundedCornerShape(
                            18.dp
                        )
                    )

                    Spacer(
                        Modifier.height(18.dp)
                    )

                    OutlinedTextField(
                        value = email,

                        onValueChange = {
                            email = it
                        },

                        label = {
                            Text("Email")
                        },

                        singleLine = true,

                        modifier = Modifier
                            .fillMaxWidth(),

                        shape = RoundedCornerShape(
                            18.dp
                        )
                    )

                    Spacer(
                        Modifier.height(18.dp)
                    )

                    OutlinedTextField(
                        value = password,

                        onValueChange = {
                            password = it
                        },

                        label = {
                            Text("Contraseña")
                        },

                        visualTransformation =
                            PasswordVisualTransformation(),

                        singleLine = true,

                        modifier = Modifier
                            .fillMaxWidth(),

                        shape = RoundedCornerShape(
                            18.dp
                        )
                    )

                    Spacer(
                        Modifier.height(28.dp)
                    )

                    /*
                    ----------------------------------------
                    El boton para confirmar que se cambia el nombre
                    ----------------------------------------
                    */

                    Button(
                        onClick = {

                            if (
                                nombreActual.isBlank()
                                ||
                                nuevoNombre.isBlank()
                                ||
                                email.isBlank()
                                ||
                                password.isBlank()
                            ) {

                                mensaje =
                                    " Completa todos los campos"

                                return@Button
                            }

                            cargando = true

                            UserRepository.changeName(
                                nombreActual,
                                nuevoNombre,
                                email,
                                password
                            ) {

                                cargando = false

                                mensaje =
                                    when(it){

                                        "OK" ->
                                            " Nombre cambiado correctamente"

                                        "PASSWORD_INCORRECTA" ->
                                            " Contraseña incorrecta"

                                        "USUARIO_NO_EXISTE" ->
                                            " Usuario no encontrado"

                                        else ->
                                            " Nombre Cambiado correctamente \n ya puede salir de la pantalla"
                                    }

                                if (it == "OK") {

                                    UserSession.userName = nuevoNombre

                                    navController.navigate("inicio")
                                }
                            }
                        },

                        modifier = Modifier
                            .fillMaxWidth()
                            .height(60.dp),

                        shape = RoundedCornerShape(
                            20.dp
                        ),

                        colors = ButtonDefaults.buttonColors(
                            containerColor =
                                Color(0xFFFACC15),

                            contentColor =
                                Color.Black
                        )
                    ) {

                        if (cargando) {

                            CircularProgressIndicator(
                                color = Color.Black
                            )

                        } else {

                            Text(
                                text =
                                    " Cambiar nombre ",

                                fontSize = 20.sp,

                                fontWeight =
                                    FontWeight.ExtraBold
                            )
                        }
                    }
                }
            }

            Spacer(
                Modifier.height(24.dp)
            )

            /*
            ----------------------------------------
            El mensaje de error o de confirmacion
            ----------------------------------------
            */

            if (mensaje.isNotEmpty()) {

                Text(
                    text = mensaje,

                    color = Color.White,

                    fontSize = 18.sp,

                    fontWeight = FontWeight.Bold
                )
            }

            Spacer(
                Modifier.height(24.dp)
            )

            /*
            ----------------------------------------
            BOTÓN VOLVER
            ----------------------------------------
            */

            OutlinedButton(
                onClick = {
                    navController.navigate(
                        "inicio"
                    )
                },

                modifier = Modifier
                    .fillMaxWidth()
                    .height(58.dp),

                shape = RoundedCornerShape(
                    18.dp
                ),

                colors = ButtonDefaults
                    .outlinedButtonColors(
                        contentColor =
                            Color.White
                    )
            ) {

                Text(
                    text = " Volver",

                    fontSize = 18.sp,

                    fontWeight = FontWeight.Bold
                )
            }

            Spacer(
                Modifier.height(30.dp)
            )
        }
    }
}