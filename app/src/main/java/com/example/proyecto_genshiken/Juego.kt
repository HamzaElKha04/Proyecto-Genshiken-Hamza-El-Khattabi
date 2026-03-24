package com.example.proyecto_genshiken

import androidx.benchmark.traceprocessor.Row
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.text.input.rememberTextFieldState
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextField
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import androidx.navigation.NavHostController
import com.android.volley.Header
import kotlinx.coroutines.delay
import kotlin.collections.forEachIndexed

@Composable
fun Juego(navController: NavHostController){
    val preguntas = PreguntasJuego.PreguntasJuego.level1

    var nivel by remember { mutableStateOf(1) }
    var numeroPregunta by remember { mutableStateOf(0) }
    var puntuacion by remember { mutableStateOf(0) }
    var respuestaCorrecta by remember { mutableStateOf(0) }

    var repuestaElegida by remember { mutableStateOf<Int?>(null) }
    var colorFondo by remember { mutableStateOf(Color.Transparent) }

    val estadoRespuesta = remember {
        mutableStateListOf<EstadoRespuesta>().apply {
            repeat(10){ add(EstadoRespuesta.PENDING) }
        }
    }

    var tiempo by remember { mutableStateOf(0) }

    LaunchedEffect(Unit){
        while(true){
            delay(1000)
            tiempo++
        }
    }

    val pregunta = preguntas[numeroPregunta]

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {

        Spacer(Modifier.height(40.dp))
        Header(nivel,tiempo,puntuacion)
        Spacer(Modifier.height(40.dp))

        Image(
            painter = painterResource(pregunta.imagen),
            contentDescription = null,
            modifier = Modifier.size(200.dp)
        )

        Spacer(Modifier.height(80.dp))

        Box(
            modifier = Modifier
                .background(colorFondo)
                .padding(8.dp)
        ){
            Text(
                pregunta.preguntas,
                fontSize = 20.sp
            )
        }

        Spacer(Modifier.height(40.dp))

        Opciones (
            opciones = pregunta.opciones,
            respuestaElegida = repuestaElegida,
            onClick = { index ->

                if(repuestaElegida==null){

                    repuestaElegida = index

                    if(index == pregunta.opcionCorrecta){

                        puntuacion += 1000
                        respuestaCorrecta++
                        estadoRespuesta[numeroPregunta] = EstadoRespuesta.CORRECT

                    }else{

                        puntuacion -= 200
                        estadoRespuesta[numeroPregunta] = EstadoRespuesta.WRONG
                    }

                }
            }
        )

        Spacer(Modifier.height(40.dp))

        QuestionProgress(estadoRespuesta,numeroPregunta)

        Spacer(Modifier.height(40.dp))

        if(numeroPregunta == 9){

            Button(onClick = {

                val timeBonus = (600-tiempo)*10
                puntuacion += timeBonus

                UserRepository.saveScore(UserSession.userId, puntuacion)


                if(respuestaCorrecta >=5){

                    navController.navigate("Nivel2")

                }else{

                    navController.navigate("Ranking")

                }

            }){

                Text("Finalizar nivel")
            }

        }else{

            Button(onClick = {

                if(repuestaElegida!=null){

                    numeroPregunta++
                    repuestaElegida = null
                    colorFondo = Color.Transparent
                    estadoRespuesta[numeroPregunta] = EstadoRespuesta.CURRENT
                }

            }){

                Text("Siguiente")
            }
        }
    }
}

@Composable
fun Header(nivel:Int,tiempo:Int,puntuacion:Int){

    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween
    ){

        Text("Nivel $nivel")

        Text("Tiempo $tiempo")

        Column {
            Text("Puntuación")
            Text("$puntuacion")
        }
    }
}

@Composable
fun Opciones(
    opciones: List<String>,
    respuestaElegida:Int?,
    onClick:(Int)->Unit
){

    Column {

        Row {

            Boton(opciones[0],Color.Red){ onClick(0) }
            Spacer(Modifier.width(16.dp))
            Boton(opciones[1],Color.Yellow){ onClick(1) }

        }

        Spacer(Modifier.height(16.dp))

        Row {

            Boton(opciones[2],Color.Cyan){ onClick(2) }
            Spacer(Modifier.width(16.dp))
            Boton(opciones[3],Color.Green){ onClick(3) }

        }
    }
}

@Composable
fun Boton(
    text:String,
    color:Color,
    onClick:()->Unit
){

    Box(
        modifier = Modifier
            .background(color)
            .padding(12.dp)
            .clickable { onClick() }
    ){
        Text(text,fontSize = 18.sp)
    }
}
@Composable
fun QuestionProgress(
    states:List<EstadoRespuesta>,
    currentIndex:Int
){

    Row {

        states.forEachIndexed { index, state ->

            val color = when(state){

                EstadoRespuesta.CORRECT -> Color.Green
                EstadoRespuesta.WRONG -> Color.Red
                EstadoRespuesta.CURRENT -> Color(0xFFFFA500)
                else -> Color.LightGray
            }

            Box(
                modifier = Modifier
                    .size(30.dp)
                    .background(color)
                    .border(1.dp,Color.Black),
                contentAlignment = Alignment.Center
            ){

                Text("${index+1}")
            }

            Spacer(Modifier.width(4.dp))
        }
    }
}
