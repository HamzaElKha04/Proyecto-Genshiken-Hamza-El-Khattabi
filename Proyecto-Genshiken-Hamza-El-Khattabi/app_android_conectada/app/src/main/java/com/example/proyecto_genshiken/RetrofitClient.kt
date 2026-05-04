package com.example.proyecto_genshiken

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

/*
--------------------------------------------------
Cliente Retrofit
--------------------------------------------------

Este archivo centraliza la conexión entre la app
Android y el backend PHP.

IMPORTANTE:
- En navegador del PC se usa:
  http://localhost/WEB_genshi/

- En emulador Android se usa:
  http://10.0.2.2/WEB_genshi/

- En móvil físico se usa la IP del ordenador:
  http://192.168.X.X/WEB_genshi/

Ahora estamos probando en emulador, por eso usamos
10.0.2.2.
*/
object RetrofitClient {

    /*
    --------------------------------------------------
    URL base del proyecto web
    --------------------------------------------------

    Esta URL apunta a la carpeta principal de la web.
    Desde aquí también se cargarán las imágenes.
    */
    private const val BASE_WEB_URL = "http://10.0.2.2/WEB_genshi/"

    /*
    --------------------------------------------------
    URL base de las APIs PHP
    --------------------------------------------------

    Todas las llamadas Retrofit se harán dentro de:
    WEB_genshi/api/
    */
    private const val BASE_API_URL = BASE_WEB_URL + "api/"

    val api: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_API_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }

    /*
    --------------------------------------------------
    Construir URL completa de imagen
    --------------------------------------------------

    En MySQL guardas imágenes tipo:
    nivel1/imagen.png

    En la web se encuentran en:
    WEB_genshi/img/nivel1/imagen.png

    Esta función convierte la ruta de la BD en una
    URL válida para Android.
    */
    fun getImageUrl(imagen: String?): String? {
        val rutaLimpia = imagen?.trim()

        if (rutaLimpia.isNullOrEmpty()) {
            return null
        }

        if (rutaLimpia.startsWith("http://") || rutaLimpia.startsWith("https://")) {
            return rutaLimpia
        }

        return BASE_WEB_URL + "img/" + rutaLimpia.trimStart('/')
    }
}