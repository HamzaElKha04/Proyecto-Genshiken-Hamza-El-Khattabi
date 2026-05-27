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

    @FormUrlEncoded
    @POST("change_name.php")
    fun changeName(
        @Field("nombreActual") nombreActual: String,
        @Field("nuevoNombre") nuevoNombre: String,
        @Field("email") email: String,
        @Field("password") password: String
    ): retrofit2.Call<String>

    @FormUrlEncoded
    @POST("guardar_monedas.php")
    fun guardarMonedas(
        @Field("usuario_id") usuarioId:Int,
        @Field("monedas") monedas:Int
    ): Call<String>

    @GET("obtener_monedas.php")
    fun obtenerMonedas(
        @Query("usuario_id") usuarioId:Int
    ): Call<String>

    @FormUrlEncoded
    @POST("guardar_espada.php")
    fun guardarEspada(
        @Field("usuario_id") usuarioId:Int,
        @Field("espada_id") espadaId:Int
    ): Call<String>

    @GET("obtener_espadas.php")
    fun obtenerEspadas(
        @Query("usuario_id") usuarioId:Int
    ): Call<List<Int>>
    @FormUrlEncoded
    @POST("resend_verification.php")
    fun reenviarVerificacion(
        @Field("correo") correo: String
    ): Call<String>
    @GET("obtener_preguntas.php")
    fun obtenerPreguntas(
        @Query("nivel_id") nivelId:Int
    ): Call<List<Preguntas>>
}