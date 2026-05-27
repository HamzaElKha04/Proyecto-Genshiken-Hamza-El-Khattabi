package com.example.proyecto_genshiken

import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf

object GachaState {

    var monedas = mutableStateOf(0)

    var espadasDesbloqueadas = mutableStateListOf<Int>()

    fun añadirEspada(id: Int) {

        if (!espadasDesbloqueadas.contains(id)) {
            espadasDesbloqueadas.add(id)
        }
    }
}