package com.example.proyecto_genshiken

data class Preguntas(

    val id: Int,

    val pregunta: String,

    val imagenPregunta: String?,

    val opciones: List<String>?,

    val opcionesImagenes: List<String>?,

    val opcionCorrecta: Int
)