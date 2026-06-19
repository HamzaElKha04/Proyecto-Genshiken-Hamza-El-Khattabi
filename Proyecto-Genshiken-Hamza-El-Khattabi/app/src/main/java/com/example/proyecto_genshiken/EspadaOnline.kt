package com.example.proyecto_genshiken

data class EspadaOnline(

    val id:Int,

    val nombre:String,

    val rareza:String,

    val descripcion:String,

    val imagen_url:String
)

fun tirarGacha(): EspadaOnline? {

    if (GachaState.listaEspadasOnline.isEmpty()) {
        return null
    }

    val random = (1..100).random()

    val rareza = when {

        random <= 60 -> "COMUN"
        random <= 90 -> "RARA"
        random <= 99 -> "EPICA"
        else -> "LEGENDARIA"
    }

    val posibles =
        GachaState.listaEspadasOnline.filter {

            it.rareza.uppercase().trim() == rareza
        }

    if (posibles.isEmpty()) {

        return GachaState.listaEspadasOnline.random()
    }

    return posibles.random()
}