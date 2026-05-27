package com.example.proyecto_genshiken


import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavHostController

@Composable
fun CambiarNombre(navController: NavHostController) {

    var nombreActual by remember { mutableStateOf("") }
    var nuevoNombre by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var mensaje by remember { mutableStateOf("") }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(Modifier.height(40.dp))

        Text("Cambiar nombre", fontSize = 26.sp)

        Spacer(Modifier.height(16.dp))

        OutlinedTextField(nombreActual, { nombreActual = it }, label = { Text("Nombre actual") })
        OutlinedTextField(nuevoNombre, { nuevoNombre = it }, label = { Text("Nuevo nombre") })
        OutlinedTextField(email, { email = it }, label = { Text("Email") })
        OutlinedTextField(password, { password = it }, label = { Text("Contraseña") })

        Spacer(Modifier.height(16.dp))

        Button(onClick = {

            if (nombreActual.isBlank() || nuevoNombre.isBlank() || email.isBlank() || password.isBlank()) {
                mensaje = "Tienes que completar todos los campos"
                return@Button
            }

            UserRepository.changeName(nombreActual, nuevoNombre, email, password) {
                mensaje = it

                if (it == "OK") {
                    navController.navigate("inicio")
                }
            }

        }) {
            Text("Cambiar nombre")
        }

        Spacer(Modifier.height(10.dp))
        Text(mensaje)
    }
}