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
        @Field("correo") correo: String,
        @Field("password") password: String
    ): Call<ResponseBody>
    /*
    --------------------------------------------------
    Registrar instalación / primer uso de la app
    --------------------------------------------------

    IMPORTANTE:
    RetrofitClient sigue apuntando a api_genshiken.

    Esta llamada concreta usa URL completa porque el
    archivo registrarDescarga.php está en la API normal:

    /WEB_genshi/api/registrarDescarga.php
    */

    @FormUrlEncoded
    @POST("save_score.php")
    fun saveScore(

        @Field("usuario_id")
        usuarioId: Int,

        @Field("puntuacion")
        puntuacion: Int,

        @Field("tiempo")
        tiempo: Int

    ): Call<ResponseBody>


    @GET("get_ranking.php")
    fun getRanking(

        @Query("mes") mes:Int,

        @Query("anio") anio:Int

    ): Call<List<Player>>

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
    @GET("obtener_espadas_gacha.php")
    fun obtenerEspadasGacha():
            Call<List<EspadaOnline>>
    @FormUrlEncoded
    @POST("http://www.shopkatanas.com/WEB_genshi/api/registrarDescarga.php")
    fun registrarDescarga(
        @Field("usuario_id") usuarioId: Int,
        @Field("nombre_usuario") nombreUsuario: String,
        @Field("dispositivo") dispositivo: String,
        @Field("version_app") versionApp: String
    ): Call<ResponseBody>
}