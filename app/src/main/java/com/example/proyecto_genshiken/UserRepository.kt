package com.example.proyecto_genshiken

import android.os.Build
import okhttp3.ResponseBody
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

/*
--------------------------------------------------
Repositorio de datos
--------------------------------------------------

Este archivo actúa como puente entre las pantallas
de Android y el backend PHP.

Aquí se centralizan las llamadas a:
- login.php
- register.php
- getPreguntas.php
- guardarPuntuacion.php
- getRanking.php
- registrarDescarga.php

La idea es que las pantallas no tengan que saber
cómo funciona Retrofit por dentro.
*/
object UserRepository {

    /*
    --------------------------------------------------
    Login de usuario
    --------------------------------------------------

    Se usa en el modo competitivo.

    La API login.php devuelve un JSON parecido a:
    {
        "status": "OK",
        "id": 1,
        "nombre": "Hamza"
    }
    */
    fun login(
        correo: String,
        password: String,
        onResult: (Boolean, Int, String) -> Unit
    ) {
        RetrofitClient.api.login(correo, password)
            .enqueue(object : Callback<ResponseBody> {

                override fun onResponse(
                    call: Call<ResponseBody>,
                    response: Response<ResponseBody>
                ) {
                    val json = response.body()?.string()

                    try {
                        if (json != null) {
                            val obj = JSONObject(json)

                            if (obj.optString("status") == "OK") {
                                val id = obj.optInt("id", 0)
                                val nombre = obj.optString("nombre", "")

                                onResult(true, id, nombre)
                            } else {
                                onResult(false, 0, "")
                            }
                        } else {
                            onResult(false, 0, "")
                        }
                    } catch (e: Exception) {
                        onResult(false, 0, "")
                    }
                }

                override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                    onResult(false, 0, "")
                }
            })
    }

    /*
    --------------------------------------------------
    Registro de usuario
    --------------------------------------------------

    La API register.php devuelve texto plano:
    - OK
    - EXISTE
    - ERROR
    */
    fun register(
        nombre: String,
        correo: String,
        password: String,
        onResult: (String) -> Unit
    ) {
        RetrofitClient.api.register(nombre, correo, password)
            .enqueue(object : Callback<ResponseBody> {

                override fun onResponse(
                    call: Call<ResponseBody>,
                    response: Response<ResponseBody>
                ) {
                    val result = response.body()?.string()?.trim() ?: "ERROR"
                    onResult(result)
                }

                override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                    onResult("ERROR")
                }
            })
    }

    /*
    --------------------------------------------------
    Obtener preguntas desde MySQL
    --------------------------------------------------

    Conecta con:
    getPreguntas.php?nivel=1

    Esto permite que la app use las preguntas creadas
    desde el panel admin web.
    */
    fun getPreguntas(
        nivel: Int = 1,
        onResult: (Boolean, List<PreguntaApi>) -> Unit
    ) {
        RetrofitClient.api.getPreguntas(nivel)
            .enqueue(object : Callback<List<PreguntaApi>> {

                override fun onResponse(
                    call: Call<List<PreguntaApi>>,
                    response: Response<List<PreguntaApi>>
                ) {
                    if (response.isSuccessful) {
                        onResult(true, response.body() ?: emptyList())
                    } else {
                        onResult(false, emptyList())
                    }
                }

                override fun onFailure(call: Call<List<PreguntaApi>>, t: Throwable) {
                    onResult(false, emptyList())
                }
            })
    }

    /*
    --------------------------------------------------
    Guardar puntuación
    --------------------------------------------------

    Conecta con:
    guardarPuntuacion.php

    Envía al backend:
    - nombre del jugador
    - puntos
    - tiempo total

    El backend guarda en la tabla puntuaciones y
    devuelve posición/top3.
    */
    fun saveScore(
        usuarioId: Int,
        puntuacion: Int,
        tiempo: Int,
        onResult: (Boolean, GuardarPuntuacionResponse?) -> Unit = { _, _ -> }
    ) {
        val nombreJugador = if (UserSession.userName.isNotBlank()) {
            UserSession.userName
        } else {
            "Anónimo"
        }

        val datos = GuardarPuntuacionRequest(
            nombre = nombreJugador,
            puntos = puntuacion,
            tiempo = tiempo
        )

        RetrofitClient.api.guardarPuntuacion(datos)
            .enqueue(object : Callback<GuardarPuntuacionResponse> {

                override fun onResponse(
                    call: Call<GuardarPuntuacionResponse>,
                    response: Response<GuardarPuntuacionResponse>
                ) {
                    if (response.isSuccessful && response.body() != null) {
                        onResult(true, response.body())
                    } else {
                        onResult(false, null)
                    }
                }

                override fun onFailure(
                    call: Call<GuardarPuntuacionResponse>,
                    t: Throwable
                ) {
                    onResult(false, null)
                }
            })
    }

    /*
    --------------------------------------------------
    Obtener ranking
    --------------------------------------------------

    Conecta con:
    getRanking.php

    La API devuelve:
    posicion, usuario, puntos, tiempo y fecha.

    Aquí convertimos esos datos al modelo Player que
    usa la pantalla Ranking.kt.
    */
    fun getRanking(
        onResult: (List<Player>) -> Unit
    ) {
        RetrofitClient.api.getRanking()
            .enqueue(object : Callback<List<RankingApiResponse>> {

                override fun onResponse(
                    call: Call<List<RankingApiResponse>>,
                    response: Response<List<RankingApiResponse>>
                ) {
                    val ranking = response.body()?.map {
                        Player(
                            nombre = it.usuario,
                            puntuacion = it.puntos,
                            posicion = it.posicion,
                            tiempo = it.tiempo,
                            fecha = it.fecha ?: ""
                        )
                    } ?: emptyList()

                    onResult(ranking)
                }

                override fun onFailure(
                    call: Call<List<RankingApiResponse>>,
                    t: Throwable
                ) {
                    onResult(emptyList())
                }
            })
    }

    /*
    --------------------------------------------------
    Registrar descarga / uso inicial de la app
    --------------------------------------------------

    Conecta con:
    registrarDescarga.php

    Para prácticas lo usamos como registro de uso de app.
    Se guarda en admin/descargas.php:
    - usuario
    - dispositivo
    - versión
    */
    fun registrarDescarga(
        nombreUsuario: String,
        usuarioId: Int? = null,
        onResult: (Boolean, String) -> Unit = { _, _ -> }
    ) {
        val dispositivo = obtenerNombreDispositivo()

        val datos = RegistrarDescargaRequest(
            usuario_id = usuarioId,
            nombre_usuario = if (nombreUsuario.isBlank()) "Anónimo" else nombreUsuario,
            dispositivo = dispositivo,
            version_app = "1.0.0"
        )

        RetrofitClient.api.registrarDescarga(datos)
            .enqueue(object : Callback<ApiSimpleResponse> {

                override fun onResponse(
                    call: Call<ApiSimpleResponse>,
                    response: Response<ApiSimpleResponse>
                ) {
                    val body = response.body()

                    if (response.isSuccessful && body != null) {
                        onResult(body.ok, body.mensaje)
                    } else {
                        onResult(false, "No se pudo registrar el uso de la app.")
                    }
                }

                override fun onFailure(
                    call: Call<ApiSimpleResponse>,
                    t: Throwable
                ) {
                    onResult(false, "Error de conexión con registrarDescarga.php.")
                }
            })
    }

    /*
    --------------------------------------------------
    Obtener nombre del dispositivo
    --------------------------------------------------

    Esto genera algo tipo:
    Google Pixel 5
    Samsung SM-A546B
    Xiaomi Redmi Note 12
    */
    private fun obtenerNombreDispositivo(): String {
        val fabricante = Build.MANUFACTURER.replaceFirstChar { it.uppercase() }
        val modelo = Build.MODEL

        return if (modelo.startsWith(fabricante, ignoreCase = true)) {
            modelo
        } else {
            "$fabricante $modelo"
        }
    }

    /*
    --------------------------------------------------
    Cambio de nombre
    --------------------------------------------------

    Se mantiene para no romper la pantalla antigua
    de cambio de nombre.
    */
    fun changeName(
        nombreActual: String,
        nuevoNombre: String,
        email: String,
        password: String,
        onResult: (String) -> Unit
    ) {
        RetrofitClient.api.changeName(nombreActual, nuevoNombre, email, password)
            .enqueue(object : Callback<String> {

                override fun onResponse(
                    call: Call<String>,
                    response: Response<String>
                ) {
                    onResult(response.body() ?: "ERROR")
                }

                override fun onFailure(call: Call<String>, t: Throwable) {
                    onResult("ERROR")
                }
            })
    }
}