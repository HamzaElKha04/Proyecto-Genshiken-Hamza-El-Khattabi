package com.example.proyecto_genshiken

import android.R
import android.R.attr.onClick
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.text.input.rememberTextFieldState
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonColors
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextField
import androidx.compose.material3.contentColorFor
import androidx.compose.material3.rememberSearchBarState
import androidx.compose.remote.creation.random
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Outline
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController
import kotlin.math.sin

@Composable
fun RegistroCompeti(navController: NavHostController){

    val curiosidad = remember {
        Curiosidades.lista.random()
    }
    var usuario by remember {
        mutableStateOf("")
    }
    var email by remember {
        mutableStateOf("")
    }
    var contraseña by remember {
        mutableStateOf("")
    }
    var mensajeError by remember{
        mutableStateOf("")
    }





    Column(
        modifier = Modifier.fillMaxSize().padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally

    )
    {
        Spacer(modifier = Modifier.height(32.dp))
        Text(
            text = "Registro",
            fontSize=32.sp,
            fontWeight = FontWeight.Bold

        )

        Spacer(modifier = Modifier.height(20.dp))

        Text(
            text = "Registrate en este modo, tu puntuación y tu nombre se guardarán en una clasificación. ¡Se el mejor y podrás llevarte un Jugoso premio!",
            fontSize = 16.sp
        )

        Spacer(modifier = Modifier.height(20.dp))

        Card(

        ) {
            Column(
                modifier = Modifier.padding(16.dp).fillMaxSize(),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                OutlinedTextField(
                    value = usuario,
                    onValueChange = { usuario = it },
                    label = { Text("Nombre de usuario") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(modifier = Modifier.height(20.dp))

                OutlinedTextField(
                    value=email,
                    onValueChange = { email =it },
                    label = {Text("Email") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()

                )

                Spacer(modifier = Modifier.height(20.dp))

                OutlinedTextField(
                    value=contraseña,
                    onValueChange = { contraseña = it },
                    label = {Text("Contraseña") },
                    singleLine = true,
                    visualTransformation = PasswordVisualTransformation(),
                    modifier = Modifier.fillMaxWidth()
                )
                Spacer(modifier = Modifier.height(20.dp))

               Button(
                   onClick = {

                       if (contraseña.isBlank() || usuario.isBlank() || email.isBlank()) {
                           mensajeError = "Todos los campos deben ser rellenados"
                           return@Button
                       }

                       if (contraseña.length < 8) {
                           mensajeError = "La contraseña no puede tener menos de 8 caracteres"
                           return@Button
                       }

                       if (usuario.length > 20) {
                           mensajeError = "El nombre de usuario no puede ser tan largo"
                           return@Button
                       }

                       // ESTO OCURRIRÁ SOLO SI TODO ESTÁ BIEN
                       UserRepository.register(usuario, email, contraseña) {

                           when (it) {
                               "EXISTE" -> mensajeError = "El correo ya está registrado"
                               "OK" -> {
                                   mensajeError = "Registro correcto"
                                   navController.navigate("inicio")
                               }
                               else -> mensajeError = "Error en registro"
                           }
                       }
                   },
                   modifier = Modifier.fillMaxWidth(),
                   colors = ButtonDefaults.buttonColors(containerColor = Color.Red)

               ) {
                   Text("Enviar")
               }

                Spacer(modifier = Modifier.height(10.dp))
                 if (mensajeError.isNotEmpty()){
                     Text(
                         text = mensajeError,
                         color = MaterialTheme.colorScheme.error
                     )
                 }

                Spacer(modifier = Modifier.height(20.dp))

                Text(
                    text = "¿Ya te has registrado? Inicia Sesión",
                    color = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.clickable{
                        navController.navigate("inicioSesionCompeti")
                    }
                )

                Spacer(modifier = Modifier.height(30.dp))

                Card(
                    modifier = Modifier.fillMaxWidth()
                ) {

                    Column(

                        modifier = Modifier.padding(16.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {

                        Text(
                            text = "¿Sabías que?",
                            fontWeight = FontWeight.Bold,
                            fontSize = 32.sp
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        Text(
                            text = curiosidad,
                            fontSize = 18.sp,
                            textAlign = TextAlign.Center
                        )
                    }
                }



            }
        }


    }
}