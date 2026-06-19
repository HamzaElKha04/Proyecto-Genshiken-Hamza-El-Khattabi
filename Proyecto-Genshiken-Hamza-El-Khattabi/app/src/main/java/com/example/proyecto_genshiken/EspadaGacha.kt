package com.example.proyecto_genshiken

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue

enum class Rareza {
    COMUN, RARA, EPICA, LEGENDARIA
}

data class Espada(
    val id: Int,
    val nombre: String,
    val rareza: Rareza,
    val descripcion: String,
    val imagen: Int
)

object EspadasData {

    val lista = listOf(
        Espada(1, "Tesoro Sagrado Lostvayne", Rareza.EPICA, "La fiable espada del capitan de los Siete Pecados Capitales, vendida una vez para afrontar los costed del Boar Hat. Con ella Meliodas puede hacer uso de sus clones virtuales", R.drawable.espada_lostavayne),
        Espada(2, "Espadas de Caos", Rareza.LEGENDARIA, "Las Espadas de Caos, forjadas en las nauseabundas profundidades del Hades. Una vez adheridas, las hojas se mantuvieron así, encadenadas a la carne chamuscada, una prolongación del cuerpo de su portador, un recordatorio permanente de la promesa de Kratos", R.drawable.kratos),
        Espada(3, "La Yoru", Rareza.COMUN, "Espada usada por el antiguo Señor de la Guerra del Mar: Dracule Mihawk. Al combinarse con las habilidades de Mihawk, la Yoru adquiere un increíble poder.", R.drawable.mihawk),
        Espada(4, "Hoja Candente", Rareza.RARA, "La Hoja candente es un sable pistola, siendo el arma característica de Lightning. Es su arma inicial en Final Fantasy XIII, y la porta en la mayoría de sus apariciones posteriores como su arma principal", R.drawable.lighting),
        Espada(5, "Nozarashi", Rareza.EPICA, "La hoja de Kenpachi Zaraki,capitán de la Undécima División del Gotei 13. esta hoja y las habilidades de su portador causan el terror a aquellos que osan enfrentarse a el", R.drawable.kempachi),
        Espada(6, "Tachi",  Rareza.COMUN, "La espada de Afro Samurai, el guerrero invencible que busca la venganza y que, además, tiene un pelazo", R.drawable.afrosamurai),
        Espada(7, "Yubashiri", Rareza.LEGENDARIA, "Una de las espadas de Roronoa Zoro, esta fue un regalo de Ipponmatsu tras ver la habilidad y el coraje que Zoro mostró en las pruebas de la maldición de la Sandai Kitetsu. Yubashiri era una espada extremadamente ligera, pero fuerte. Su peso ligero permitía acciones y reflejos rápidos, mientras que mantenía su nitidez por el poder de corte.", R.drawable.zoro),
        Espada(8, "Sandai Kitetsu ", Rareza.EPICA, "Una de las espadas de Ronronoa Zoro,  es una de las Wazamono y, como el resto de espadas Kitetsu, está maldita. Fue forjada por Kozuki Sukiyaki. Zoro probó su suerte contra la maldición y finalmente acabó superando la prueba. Permitiendo a Zoro quedarse con la espada", R.drawable.zoroespada),
        Espada(9, "Espada Atlante", Rareza.RARA, "Es una espada pesada, de gran tamaño, forjada en la mítica Atlantis. Se dice que la Espada Atlante otorga fuerza y resistencia sobrehumanas a su portado. Es por esto que Conan la toma como su compañera de aventuras", R.drawable.conan),
        Espada(10, "Katana Hattori Hanzo",  Rareza.COMUN, "La primera katana creada por Hattori Hanzo en mas de un cuarto de siglo, esta katana fue creada para 'La novia' con la intencion de asesinar a Bill, quien, habia ofendido a su maestro por sus acciones deshonrosas ", R.drawable.kill_bill)

    )
}





