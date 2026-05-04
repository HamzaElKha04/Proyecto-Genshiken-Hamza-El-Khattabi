# Proyecto Genshiken

Proyecto desarrollado como parte del periodo de prácticas.

El objetivo del proyecto es crear una aplicación tipo trivia basada en anime, que permita a los usuarios responder preguntas sobre diferentes personajes y objetos.

## Tecnologías utilizadas

- Android Studio (Kotlin)
- PHP
- MySQL
- XAMPP
- HTML / CSS
- GitHub

## Estructura del proyecto

El proyecto está dividido en dos partes principales:

### Aplicación móvil

La aplicación Android permite a los usuarios:

- Registrarse
- Iniciar sesión
- Jugar a los niveles de preguntas
- Ver el ranking

### Panel de administración web

Se ha desarrollado un panel web para gestionar el contenido del juego.

Funciones actuales:

- Login de administrador
- Visualización de preguntas
- Visualización de respuestas
- Edición de preguntas
- API para obtener preguntas desde la base de datos

## API

La aplicación obtiene las preguntas mediante una API en PHP:
/api/getPreguntas.php?nivel=1

La API devuelve las preguntas en formato JSON para que puedan ser utilizadas por la aplicación móvil.

## Estado del proyecto

Actualmente el sistema cuenta con:

- Panel de administración funcional
- Sistema de autenticación para administrador
- API conectada a la base de datos
- Visualización y edición de preguntas
- Integración de imágenes en las preguntas

Este sistema permitirá gestionar el contenido del juego desde la web y servir los datos a la aplicación móvil.
