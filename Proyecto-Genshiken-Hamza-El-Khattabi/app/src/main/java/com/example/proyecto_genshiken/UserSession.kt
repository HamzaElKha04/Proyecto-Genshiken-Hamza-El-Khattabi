package com.example.proyecto_genshiken

//Aqui es como guardamos la sesion de un usuario, en inicio sesion cuando el usuario se loguee en la aplicacion esta guardara su nombre y su id de usuario segun la base de datos. esto se seguira guardando hasta que el usuario cierre la aplicacion
object UserSession {
    var userId: Int = 0
    var userName: String = ""
}
object GameSession {
    var lastScore: Int = 0
    var lastTime: Int = 0
}