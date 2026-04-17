package com.example.proyecto_genshiken

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

                override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {

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

                override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {

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
        usuarioId: Int,
        puntuacion: Int
    ) {

        RetrofitClient.api.saveScore(usuarioId, puntuacion)
            .enqueue(object : Callback<String> {

                override fun onResponse(call: Call<String>, response: Response<String>) {}

                override fun onFailure(call: Call<String>, t: Throwable) {}
            })
    }

    // Con esto obtendremos el Ranking de los mejores jugadores, para asi poder ponerlos en la tabla

    fun getRanking(
        onResult: (List<Player>) -> Unit
    ) {

        RetrofitClient.api.getRanking()
            .enqueue(object : Callback<List<Player>> {

                override fun onResponse(call: Call<List<Player>>, response: Response<List<Player>>) {
                    onResult(response.body() ?: emptyList())
                }

                override fun onFailure(call: Call<List<Player>>, t: Throwable) {
                    onResult(emptyList())
                }
            })
    }

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
}