package com.example.proyecto_genshiken

import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

/*
--------------------------------------------------
Servicio API
--------------------------------------------------

Aquí se declaran las llamadas HTTP que hará Android
al backend PHP.

Parte importante de integración:
- getPreguntas.php
- guardarPuntuacion.php
- getRanking.php
- registrarDescarga.php

También mantenemos:
- register.php
- login.php
- change_name.php

porque la app ya usa esas pantallas.
*/
interface ApiService {

    /*
    --------------------------------------------------
    Obtener preguntas desde MySQL
    --------------------------------------------------

    Llama a:
    GET getPreguntas.php?nivel=1
    */
    @GET("getPreguntas.php")
    fun getPreguntas(
        @Query("nivel") nivel: Int
    ): Call<List<PreguntaApi>>

    /*
    --------------------------------------------------
    Guardar puntuación final
    --------------------------------------------------

    Llama a:
    POST guardarPuntuacion.php

    Envía JSON:
    {
      "nombre": "Hamza",
      "puntos": 1000,
      "tiempo": 25
    }
    */
    @POST("guardarPuntuacion.php")
    fun guardarPuntuacion(
        @Body datos: GuardarPuntuacionRequest
    ): Call<GuardarPuntuacionResponse>

    /*
    --------------------------------------------------
    Obtener ranking real
    --------------------------------------------------

    Llama a:
    GET getRanking.php
    */
    @GET("getRanking.php")
    fun getRanking(): Call<List<RankingApiResponse>>

    /*
    --------------------------------------------------
    Registrar descarga o primer uso de la app
    --------------------------------------------------

    Llama a:
    POST registrarDescarga.php
    */
    @POST("registrarDescarga.php")
    fun registrarDescarga(
        @Body datos: RegistrarDescargaRequest
    ): Call<ApiSimpleResponse>

    /*
    --------------------------------------------------
    Registro de usuario desde Android
    --------------------------------------------------
    */
    @FormUrlEncoded
    @POST("register.php")
    fun register(
        @Field("nombre") nombre: String,
        @Field("correo") correo: String,
        @Field("password") password: String
    ): Call<ResponseBody>

    /*
    --------------------------------------------------
    Login de usuario desde Android
    --------------------------------------------------
    */
    @FormUrlEncoded
    @POST("login.php")
    fun login(
        @Field("correo") correo: String,
        @Field("password") password: String
    ): Call<ResponseBody>

    /*
    --------------------------------------------------
    Cambio de nombre
    --------------------------------------------------
    */
    @FormUrlEncoded
    @POST("change_name.php")
    fun changeName(
        @Field("nombreActual") nombreActual: String,
        @Field("nuevoNombre") nuevoNombre: String,
        @Field("email") email: String,
        @Field("password") password: String
    ): Call<String>
}