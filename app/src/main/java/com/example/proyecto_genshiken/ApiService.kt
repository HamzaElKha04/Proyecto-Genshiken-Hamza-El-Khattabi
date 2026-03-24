package com.example.proyecto_genshiken

import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.http.*

interface ApiService {

    @FormUrlEncoded
    @POST("register.php")
    fun register(
        @Field("nombre") nombre: String,
        @Field("correo") correo: String,
        @Field("password") password: String
    ): Call<ResponseBody>

    @FormUrlEncoded
    @POST("login.php")
    fun login(
        @Field("correo") correo:String,
        @Field("password") password:String
    ): Call<ResponseBody>

    @FormUrlEncoded
    @POST("save_score.php")
    fun saveScore(
        @Field("usuario_id") usuarioId:Int,
        @Field("puntuacion") puntuacion:Int
    ): Call<String>

    @GET("get_ranking.php")
    fun getRanking(): Call<List<Player>>
}