package com.example.proyecto_genshiken

import okhttp3.ResponseBody
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

/*
--------------------------------------------------
Repositorio de datos
--------------------------------------------------

Este archivo hace de puente entre las pantallas
de Android y las APIs PHP.

La idea es que las pantallas no llamen directamente
a Retrofit. Llaman a UserRepository, y este se
encarga de hablar con el backend.
*/
object UserRepository {

    /*
    --------------------------------------------------
    Login de usuario
    --------------------------------------------------
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
    Obtener preguntas reales desde el backend
    --------------------------------------------------

    Esta función llama a:
    getPreguntas.php?nivel=1

    Es una parte clave de la integración porque
    permite que Android use las preguntas creadas
    desde el panel admin.
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

    Esta función llama a guardarPuntuacion.php.

    Se guarda:
    - nombre del jugador
    - puntos
    - tiempo
    */
    fun saveScore(
        usuarioId: Int,
        puntuacion: Int,
        tiempo: Int = 0,
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

                override fun onFailure(call: Call<GuardarPuntuacionResponse>, t: Throwable) {
                    onResult(false, null)
                }
            })
    }

    /*
    --------------------------------------------------
    Obtener ranking
    --------------------------------------------------

    Esta función llama a getRanking.php y adapta
    el JSON al modelo Player que usa Ranking.kt.
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

                override fun onFailure(call: Call<List<RankingApiResponse>>, t: Throwable) {
                    onResult(emptyList())
                }
            })
    }

    /*
    --------------------------------------------------
    Registrar descarga o uso inicial
    --------------------------------------------------
    */
    fun registrarDescarga(
        nombreUsuario: String,
        dispositivo: String,
        versionApp: String,
        usuarioId: Int? = null,
        onResult: (Boolean, String) -> Unit = { _, _ -> }
    ) {
        val datos = RegistrarDescargaRequest(
            usuario_id = usuarioId,
            nombre_usuario = if (nombreUsuario.isBlank()) "Anónimo" else nombreUsuario,
            dispositivo = dispositivo,
            version_app = versionApp
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
                        onResult(false, "No se pudo registrar la descarga.")
                    }
                }

                override fun onFailure(call: Call<ApiSimpleResponse>, t: Throwable) {
                    onResult(false, "Error de conexión con el backend.")
                }
            })
    }

    /*
    --------------------------------------------------
    Cambio de nombre
    --------------------------------------------------
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