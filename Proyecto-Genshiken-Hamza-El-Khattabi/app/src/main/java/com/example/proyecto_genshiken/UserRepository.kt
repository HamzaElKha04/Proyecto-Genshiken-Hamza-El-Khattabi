package com.example.proyecto_genshiken

import android.os.Build
import com.example.proyecto_genshiken.Player
import com.example.proyecto_genshiken.RetrofitClient
import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

object UserRepository {

    // Esta es la funcion para el Login
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

                    if (json != null) {
                        val obj = org.json.JSONObject(json)

                        if (obj.getString("status") == "OK") {

                            val id = obj.getInt("id")
                            val nombre = obj.getString("nombre")

                            onResult(true, id, nombre)

                        } else {
                            onResult(false, 0, "")
                        }
                    }
                }

                override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                    onResult(false, 0, "")
                }
            })
    }

    // Esta es la funcion Para el registro
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


    // Con esta funcion Guardaremos la puntuacion del jugador
    fun saveScore(
        userId: Int,
        puntuacion: Int,
        tiempo: Int,
        onResult: (Boolean) -> Unit
    ) {

        RetrofitClient.api.saveScore(
            userId,
            puntuacion,
            tiempo
        ).enqueue(object : Callback<ResponseBody> {

            override fun onResponse(
                call: Call<ResponseBody>,
                response: Response<ResponseBody>
            ) {

                val resultado =
                    response.body()?.string()?.trim()

                onResult(resultado == "OK")
            }

            override fun onFailure(
                call: Call<ResponseBody>,
                t: Throwable
            ) {

                onResult(false)
            }
        })
    }

// Con esto obtendremos el Ranking de los mejores jugadores, para asi poder ponerlos en la tabla

    fun getRanking(
        mes: Int,
        anio: Int,
        onResult: (List<Player>) -> Unit
    ) {

        RetrofitClient.api.getRanking(
            mes,
            anio
        ).enqueue(object : Callback<List<Player>> {

            override fun onResponse(
                call: Call<List<Player>>,
                response: Response<List<Player>>
            ) {

                onResult(response.body() ?: emptyList())
            }

            override fun onFailure(
                call: Call<List<Player>>,
                t: Throwable
            ) {

                onResult(emptyList())
            }
        })
    }

    // con esta funcion permitiremos el cambio de nombre
    fun changeName(
        nombreActual: String,
        nuevoNombre: String,
        email: String,
        password: String,
        onResult: (String) -> Unit
    ) {

        RetrofitClient.api.changeName(nombreActual, nuevoNombre, email, password)
            .enqueue(object : retrofit2.Callback<String> {

                override fun onResponse(
                    call: retrofit2.Call<String>,
                    response: retrofit2.Response<String>
                ) {
                    onResult(response.body() ?: "ERROR")
                }

                override fun onFailure(call: retrofit2.Call<String>, t: Throwable) {
                    onResult("ERROR")
                }
            })
    }

    fun guardarMonedas(
        usuarioId: Int,
        monedas: Int
    ) {

        RetrofitClient.api.guardarMonedas(usuarioId, monedas)
            .enqueue(object : Callback<String> {

                override fun onResponse(
                    call: Call<String>,
                    response: Response<String>
                ) {
                }

                override fun onFailure(
                    call: Call<String>,
                    t: Throwable
                ) {
                }
            })
    }

    fun obtenerMonedas(
        usuarioId: Int,
        onResult: (Int) -> Unit
    ) {

        RetrofitClient.api.obtenerMonedas(usuarioId)
            .enqueue(object : Callback<String> {

                override fun onResponse(
                    call: Call<String>,
                    response: Response<String>
                ) {

                    val monedas =
                        response.body()?.toIntOrNull() ?: 0

                    onResult(monedas)
                }

                override fun onFailure(
                    call: Call<String>,
                    t: Throwable
                ) {
                    onResult(0)
                }
            })
    }

    fun guardarEspada(
        usuarioId: Int,
        espadaId: Int
    ) {

        RetrofitClient.api.guardarEspada(usuarioId, espadaId)
            .enqueue(object : Callback<String> {

                override fun onResponse(
                    call: Call<String>,
                    response: Response<String>
                ) {
                }

                override fun onFailure(
                    call: Call<String>,
                    t: Throwable
                ) {
                }
            })
    }

    fun obtenerEspadas(
        usuarioId: Int,
        onResult: (List<Int>) -> Unit
    ) {

        RetrofitClient.api.obtenerEspadas(usuarioId)
            .enqueue(object : Callback<List<Int>> {

                override fun onResponse(
                    call: Call<List<Int>>,
                    response: Response<List<Int>>
                ) {

                    onResult(response.body() ?: emptyList())
                }

                override fun onFailure(
                    call: Call<List<Int>>,
                    t: Throwable
                ) {

                    onResult(emptyList())
                }
            })
    }

    fun reenviarVerificacion(
        correo: String,
        onResult: (String) -> Unit
    ) {

        RetrofitClient.api.reenviarVerificacion(correo)
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

    fun obtenerPreguntas(
        nivelId: Int,
        onResult: (List<Preguntas>) -> Unit
    ) {

        RetrofitClient.api.obtenerPreguntas(nivelId)
            .enqueue(object : Callback<List<Preguntas>> {

                override fun onResponse(
                    call: Call<List<Preguntas>>,
                    response: Response<List<Preguntas>>
                ) {

                    onResult(response.body() ?: emptyList())
                }

                override fun onFailure(
                    call: Call<List<Preguntas>>,
                    t: Throwable
                ) {

                    onResult(emptyList())
                }
            })
    }

    fun obtenerEspadasGacha(
        onResult: (List<EspadaOnline>) -> Unit
    ) {

        RetrofitClient.api.obtenerEspadasGacha()
            .enqueue(object : Callback<List<EspadaOnline>> {

                override fun onResponse(
                    call: Call<List<EspadaOnline>>,
                    response: Response<List<EspadaOnline>>
                ) {

                    onResult(response.body() ?: emptyList())
                }

                override fun onFailure(
                    call: Call<List<EspadaOnline>>,
                    t: Throwable
                ) {

                    onResult(emptyList())
                }
            })

    }

    /*
   --------------------------------------------------
   Registrar instalación / primer uso de la app
   --------------------------------------------------

   Se llamará SOLO desde el login competitivo.

   No bloquea el funcionamiento de la app si falla.
   */
    fun registrarDescarga(
        usuarioId: Int,
        nombreUsuario: String
    ) {
        val dispositivo = obtenerNombreDispositivo()
        val versionApp = "1.0.0"

        RetrofitClient.api.registrarDescarga(
            usuarioId = usuarioId,
            nombreUsuario = nombreUsuario,
            dispositivo = dispositivo,
            versionApp = versionApp
        ).enqueue(object : Callback<ResponseBody> {

            override fun onResponse(
                call: Call<ResponseBody>,
                response: Response<ResponseBody>
            ) {
                /*
                No mostramos nada al usuario porque esto
                es solo para el panel de administración.
                */
            }

            override fun onFailure(
                call: Call<ResponseBody>,
                t: Throwable
            ) {
                /*
                No bloqueamos la app si falla esta estadística.
                */
            }
        })
    }

    /*
    --------------------------------------------------
    Obtener nombre del dispositivo
    --------------------------------------------------
    */
    private fun obtenerNombreDispositivo(): String {
        val fabricante = Build.MANUFACTURER
        val modelo = Build.MODEL

        return "$fabricante $modelo"
            .replaceFirstChar { it.uppercase() }
            .trim()
    }
}



