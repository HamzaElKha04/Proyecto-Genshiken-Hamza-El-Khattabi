package com.example.proyecto_genshiken

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object RetrofitClient {

    private const val BASE_URL = "http://genshiapp.esy.es/api_genshiken/" // Si esto por lo que sea no funciona cambia la URL por esto y en principio ya deberia funcionar --> "http://31.220.106.243/api_genshiken/"
    val api: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}