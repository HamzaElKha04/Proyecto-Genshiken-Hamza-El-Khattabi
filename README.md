# Trivia Genshiken C.S.

Proyecto desarrollado durante el periodo de prácticas de **2º DAM**.

**Trivia Genshiken C.S.** es una aplicación tipo trivia basada en anime, cultura japonesa y espadas. El sistema permite a los usuarios registrarse, iniciar sesión, responder preguntas por niveles, competir en un ranking y desbloquear espadas mediante un sistema de gacha.

El proyecto está formado por dos partes principales:

* **Aplicación Android**, utilizada por los usuarios finales.
* **Panel web de administración**, backend PHP, APIs y base de datos MySQL.

---

## Objetivo del proyecto

El objetivo principal es crear una aplicación Android conectada a un backend real, donde el contenido del juego pueda administrarse desde una web.

Desde el panel de administración se pueden gestionar:

* Preguntas y respuestas.
* Imágenes de preguntas y respuestas.
* Usuarios registrados.
* Ranking.
* Instalaciones detectadas de la app.
* Catálogo de espadas del gacha.
* Enlaces en las descripciones de espadas.

La app Android consume la información desde el backend PHP mediante APIs conectadas a una base de datos MySQL.

Flujo general del sistema:

```text
Panel web de administración
        ↓
Base de datos MySQL
        ↓
APIs PHP
        ↓
Aplicación Android
```

---

## Tecnologías utilizadas

### Aplicación Android

* Kotlin
* Jetpack Compose
* Android Studio
* Retrofit
* Gson
* Coil
* MediaPlayer
* Navigation Compose
* Material 3

### Web, backend y base de datos

* PHP
* MySQL
* HTML
* CSS
* JavaScript
* phpMyAdmin
* XAMPP
* FileZilla
* Hosting web

### Control de versiones

* Git
* GitHub

---

## Estructura general del proyecto

```text
Proyecto-Genshiken-Hamza-El-Khattabi/
│
├── app/
│   └── Aplicación Android desarrollada con Kotlin y Jetpack Compose
│
└── web/
    └── Parte web, panel de administración, APIs PHP e imágenes
```

---

## Estructura de la parte web

```text
WEB_genshi/
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── usuarios.php
│   ├── preguntas.php
│   ├── crear_pregunta.php
│   ├── editar_pregunta.php
│   ├── ver_respuestas.php
│   ├── eliminar_pregunta.php
│   ├── ranking.php
│   ├── reset_ranking.php
│   ├── ganadores.php
│   ├── descargas.php
│   ├── espadas.php
│   ├── crear_espada.php
│   ├── editar_espada.php
│   ├── eliminar_espada.php
│   ├── config.php
│   └── style.css
│
├── api/
│   ├── getPreguntas.php
│   ├── guardarPuntuacion.php
│   ├── getRanking.php
│   └── registrarDescarga.php
│
├── api_genshiken/
│   ├── db.php
│   ├── login.php
│   ├── register.php
│   ├── obtener_preguntas.php
│   ├── get_ranking.php
│   ├── save_score.php
│   ├── guardar_monedas.php
│   ├── obtener_monedas.php
│   ├── guardar_espada.php
│   ├── obtener_espadas.php
│   └── obtener_espadas_gacha.php
│
├── css/
├── js/
├── img/
│   ├── nivel1/
│   ├── nivel2/
│   └── gacha/
│
├── index.html
├── juego.html
└── ranking.html
```

---

## Funcionalidades principales

### Aplicación Android

La app Android es la parte final utilizada por el usuario.

Funcionalidades principales:

* Pantalla inicial con logo, animación y música.
* Registro de usuario.
* Inicio de sesión.
* Modo casual.
* Modo competitivo.
* Juego por niveles.
* Carga de preguntas desde la base de datos.
* Imágenes en preguntas.
* Ranking de jugadores.
* Sistema de monedas.
* Sistema de gacha.
* Colección de espadas desbloqueadas.
* Música de menú y música de partida.
* Enlaces clicables en la descripción de las espadas.
* Registro de instalación o primer uso desde el login competitivo.

---

### Panel de administración

El panel de administración permite gestionar el contenido principal del juego desde el navegador.

Funcionalidades principales:

* Inicio de sesión de administrador.
* Dashboard principal con estadísticas.
* Gestión de usuarios registrados.
* Gestión de preguntas y respuestas.
* Creación, edición y eliminación de preguntas.
* Selector de archivos para subir imágenes.
* Gestión del ranking.
* Reset del ranking.
* Histórico mensual del ranking.
* Consulta de ganadores.
* Registro de instalaciones de la app.
* Gestión del catálogo de espadas del gacha.
* Creación, edición y eliminación de espadas.
* Enlaces clicables en las descripciones de espadas.

---

## Gestión de preguntas y respuestas

Desde el panel se pueden crear y editar preguntas que después consume la app Android.

Cada pregunta puede tener:

* Texto de pregunta.
* Imagen asociada.
* Nivel.
* Cuatro respuestas.
* Imagen opcional en cada respuesta.
* Una única respuesta correcta.

También se añadió un selector de archivos para facilitar la subida de imágenes. De esta forma, el administrador no tiene que escribir manualmente rutas como:

```text
nivel1/q1.png
```

sino que puede seleccionar la imagen desde su ordenador y el sistema la sube automáticamente a la carpeta correspondiente.

Ejemplos de carpetas usadas:

```text
img/nivel1/
img/nivel2/
```

---

## Sistema de gacha

El proyecto incluye un sistema de gacha donde el usuario puede gastar monedas para conseguir espadas.

Funcionamiento general:

```text
El usuario juega partidas
        ↓
Obtiene monedas según su puntuación
        ↓
Usa monedas en el gacha
        ↓
Consigue espadas aleatorias
        ↓
Las espadas se guardan en su colección
```

Las espadas tienen diferentes rarezas:

```text
COMUN
RARA
EPICA
LEGENDARIA
```

La app obtiene el catálogo de espadas desde la base de datos, por lo que se pueden añadir nuevas espadas desde el panel web sin modificar directamente el código Android.

---

## Gestión de espadas desde el panel

Se añadió una sección específica para administrar las espadas del gacha.

Archivos principales:

```text
admin/espadas.php
admin/crear_espada.php
admin/editar_espada.php
admin/eliminar_espada.php
```

Desde esta sección se puede:

* Ver el catálogo de espadas.
* Crear nuevas espadas.
* Editar espadas existentes.
* Eliminar espadas.
* Asignar nombre.
* Asignar rareza.
* Añadir descripción.
* Subir imagen.
* Añadir enlaces en la descripción.

La tabla principal es:

```text
espadas
```

Campos principales:

```text
id
nombre
rareza
descripcion
imagen_url
```

Las imágenes del gacha se guardan en:

```text
img/gacha/
```

---

## Ranking

El ranking permite mostrar las mejores puntuaciones de los usuarios.

La app y el panel consultan el ranking desde la tabla:

```text
puntuaciones
```

El ranking se ordena por:

1. Mayor puntuación.
2. Menor tiempo.
3. Fecha más antigua en caso de empate.

También existe una tabla de histórico mensual:

```text
ranking_mensual_historico
```

Esta tabla se utiliza cuando el administrador realiza un reset del ranking desde el panel.

---

## Instalaciones de la app

La sección inicialmente planteada como “Descargas” se adaptó a **Instalaciones de la app**.

No se registra una descarga directa desde una tienda, sino el primer uso detectado desde la aplicación Android.

Datos registrados:

* Usuario.
* Dispositivo.
* Versión de la app.
* Fecha de primer uso.

El sistema evita duplicar registros para el mismo usuario, dispositivo y versión.

Diferencia principal:

```text
Usuarios registrados → cuentas creadas en la aplicación
Instalaciones de la app → primeros usos detectados desde un dispositivo
```

---

## APIs principales

El backend cuenta con APIs PHP que permiten comunicar la base de datos con la app Android.

### Obtener preguntas

```text
api/getPreguntas.php?nivel=1
```

Devuelve las preguntas de un nivel junto con sus respuestas.

---

### Guardar puntuación

```text
api/guardarPuntuacion.php
```

Guarda la puntuación obtenida por un jugador.

---

### Obtener ranking

```text
api/getRanking.php
```

Devuelve el ranking actual.

---

### Registrar instalación

```text
api/registrarDescarga.php
```

Registra el primer uso detectado de la app desde un dispositivo.

Aunque el archivo mantiene el nombre `registrarDescarga.php`, su función real es registrar instalaciones o primeros usos detectados desde Android.

---

### APIs usadas por la app Android

La app Android utiliza principalmente APIs ubicadas en:

```text
api_genshiken/
```

Algunas APIs importantes son:

```text
api_genshiken/login.php
api_genshiken/register.php
api_genshiken/obtener_preguntas.php
api_genshiken/get_ranking.php
api_genshiken/save_score.php
api_genshiken/guardar_monedas.php
api_genshiken/obtener_monedas.php
api_genshiken/guardar_espada.php
api_genshiken/obtener_espadas.php
api_genshiken/obtener_espadas_gacha.php
```

---

## Base de datos

La base de datos MySQL contiene las tablas necesarias para el funcionamiento completo del sistema.

Tablas principales:

```text
usuarios
preguntas
respuestas
niveles
puntuaciones
ranking_mensual_historico
descargas
monedas_usuario
coleccion_usuario
espadas
```

Descripción general:

* `usuarios`: almacena los usuarios registrados.
* `preguntas`: almacena las preguntas del juego.
* `respuestas`: almacena las respuestas asociadas a cada pregunta.
* `niveles`: almacena los niveles disponibles.
* `puntuaciones`: almacena las puntuaciones del ranking.
* `ranking_mensual_historico`: almacena rankings guardados antes de un reset.
* `descargas`: registra instalaciones o primeros usos.
* `monedas_usuario`: almacena las monedas de cada usuario.
* `coleccion_usuario`: almacena las espadas desbloqueadas por cada usuario.
* `espadas`: almacena el catálogo oficial de espadas del gacha.

---

## Hosting

El backend y el panel web están desplegados en hosting para permitir que la aplicación Android consuma las APIs de forma online.

URL base del proyecto web:

```text
http://www.shopkatanas.com/WEB_genshi/
```

Panel de administración:

```text
http://www.shopkatanas.com/WEB_genshi/admin/login.php
```

Carpetas importantes en hosting:

```text
/WEB_genshi/admin/
/WEB_genshi/api/
/WEB_genshi/api_genshiken/
/WEB_genshi/img/nivel1/
/WEB_genshi/img/nivel2/
/WEB_genshi/img/gacha/
```

---

## Estado actual del proyecto

Actualmente el proyecto cuenta con:

* Panel de administración funcional.
* Backend PHP conectado a MySQL.
* Base de datos desplegada en hosting.
* APIs funcionando online.
* Gestión de preguntas y respuestas.
* Selector de archivos para subir imágenes.
* Preguntas organizadas por niveles.
* Nivel 1 y nivel 2 funcionales.
* Ranking actual.
* Histórico mensual del ranking.
* Registro de instalaciones de la app.
* Gestión de usuarios registrados.
* Gestión del catálogo de espadas del gacha.
* Subida de imágenes del gacha desde el panel.
* Enlaces clicables en panel y app.
* App Android conectada mediante APIs.
* Pantalla inicial mejorada.
* Sistema de monedas, gacha y colección.
* Música de menú y música de juego.

---

## Seguridad

Por seguridad, no se deben subir credenciales reales del hosting, contraseñas privadas, usuarios FTP ni claves de base de datos a repositorios públicos.

El archivo `config.php` del repositorio debe contener valores de ejemplo o cargar una configuración local privada no versionada.

Ejemplo recomendado:

```text
config.local.php
```

Este archivo debe estar incluido en `.gitignore` y no debe subirse a GitHub.

---

## Posibles mejoras futuras

Algunas mejoras que podrían añadirse en el futuro son:

* Añadir más niveles.
* Añadir más preguntas.
* Añadir más espadas al gacha.
* Mejorar animaciones del gacha.
* Añadir perfil de usuario.
* Añadir estadísticas personales.
* Mejorar recuperación de contraseña.
* Añadir sistema de temporadas.
* Mejorar diseño responsive de la web.
* Publicar la app en una tienda oficial.

---

## Resumen final

**Trivia Genshiken C.S.** es un proyecto completo compuesto por una app Android, un panel web de administración, APIs PHP y una base de datos MySQL.

El panel permite administrar el contenido del juego, mientras que la app Android consume esos datos para ofrecer la experiencia final al usuario: jugar, competir, obtener monedas, usar el gacha y completar una colección de espadas.

El proyecto queda preparado para seguir ampliándose con nuevos niveles, preguntas, espadas y funcionalidades.
