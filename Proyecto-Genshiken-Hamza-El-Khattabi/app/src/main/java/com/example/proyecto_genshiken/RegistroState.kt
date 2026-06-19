package com.example.proyecto_genshiken

import androidx.compose.runtime.mutableStateOf

object RegistroState {

    var usuario = mutableStateOf("")
    var email = mutableStateOf("")
    var contraseña = mutableStateOf("")

    var aceptaTerminos = mutableStateOf(false)
}