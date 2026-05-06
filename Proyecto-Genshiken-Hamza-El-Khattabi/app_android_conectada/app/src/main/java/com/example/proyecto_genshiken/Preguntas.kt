package com.example.proyecto_genshiken
data class Preguntas(
    val imagen: Int,
    val preguntas: String,
    val opciones: List<String>,
    val opcionCorrecta: Int
)
