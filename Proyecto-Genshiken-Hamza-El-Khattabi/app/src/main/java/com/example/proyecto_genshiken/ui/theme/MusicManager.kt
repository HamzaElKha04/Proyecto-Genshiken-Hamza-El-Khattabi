package com.example.proyecto_genshiken

import android.content.Context
import android.media.AudioAttributes
import android.media.MediaPlayer
import android.util.Log

/*
--------------------------------------------------
Gestor de música de la app
--------------------------------------------------

Controla la música de fondo de la aplicación.

Archivos necesarios:
app/src/main/res/raw/musica_menu.mp3
app/src/main/res/raw/musica_juego.mp3
*/
object MusicManager {

    private var mediaPlayer: MediaPlayer? = null
    private var musicaActual: String = ""

    fun reproducirMenu(context: Context) {
        reproducirMusica(
            context = context,
            recurso = R.raw.musica_menu,
            nombreMusica = "menu"
        )
    }

    fun reproducirJuego(context: Context) {
        reproducirMusica(
            context = context,
            recurso = R.raw.musica_juego,
            nombreMusica = "juego"
        )
    }

    private fun reproducirMusica(
        context: Context,
        recurso: Int,
        nombreMusica: String
    ) {
        try {
            if (
                musicaActual == nombreMusica &&
                mediaPlayer != null &&
                mediaPlayer?.isPlaying == true
            ) {
                Log.d("MusicManager", "Ya está sonando: $nombreMusica")
                return
            }

            pararMusica()

            val afd = context.resources.openRawResourceFd(recurso)

            val player = MediaPlayer()

            player.setAudioAttributes(
                AudioAttributes.Builder()
                    .setUsage(AudioAttributes.USAGE_MEDIA)
                    .setContentType(AudioAttributes.CONTENT_TYPE_MUSIC)
                    .build()
            )

            player.setDataSource(
                afd.fileDescriptor,
                afd.startOffset,
                afd.length
            )

            afd.close()

            player.isLooping = true
            player.setVolume(1.0f, 1.0f)

            player.setOnPreparedListener {
                it.start()
                Log.d("MusicManager", "Reproduciendo música: $nombreMusica")
            }

            player.setOnErrorListener { _, what, extra ->
                Log.e("MusicManager", "Error MediaPlayer what=$what extra=$extra")
                true
            }

            player.prepareAsync()

            mediaPlayer = player
            musicaActual = nombreMusica

        } catch (e: Exception) {
            Log.e("MusicManager", "Error reproduciendo música: $nombreMusica", e)
        }
    }

    fun pararMusica() {
        try {
            mediaPlayer?.let { player ->
                if (player.isPlaying) {
                    player.stop()
                }

                player.release()
            }
        } catch (e: Exception) {
            Log.e("MusicManager", "Error al parar música", e)
        } finally {
            mediaPlayer = null
            musicaActual = ""
        }
    }
}

