# Proyecto Genshiken

Proyecto desarrollado durante el periodo de prácticas de 2º DAM.

El objetivo principal del proyecto es crear una aplicación tipo trivia basada en anime y cultura japonesa, donde los usuarios puedan responder preguntas, competir por puntuación, consultar rankings y desbloquear elementos mediante un sistema de gacha.

El sistema está dividido en dos partes principales:

1. **Aplicación Android**, utilizada por los usuarios finales.
2. **Panel web de administración**, backend PHP, base de datos MySQL y APIs.

---

## Objetivo del proyecto

La finalidad del proyecto es que la aplicación Android consuma datos gestionados desde un panel web de administración.

Desde el panel web se pueden administrar preguntas, respuestas, usuarios, rankings, instalaciones detectadas de la aplicación y el catálogo de espadas del gacha.

La app Android obtiene la información desde el backend PHP mediante APIs conectadas a una base de datos MySQL.

Flujo principal del sistema:

```text
Panel admin web → Base de datos MySQL → APIs PHP → App Android
```

---

## Tecnologías utilizadas

### Web, backend y base de datos

* PHP
* MySQL
* HTML
* CSS
* JavaScript
* XAMPP
* phpMyAdmin
* FileZilla

### Aplicación Android

* Android Studio
* Kotlin
* Jetpack Compose
* Retrofit
* Gson

### Control de versiones

* Git
* GitHub

---

## Estructura general del proyecto web

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
│   └── obtener_espadas.php
│
├── css/
├── js/
├── img/
│   ├── nivel1/
│   └── gacha/
│
├── index.html
├── juego.html
└── ranking.html
```

---

## Panel de administración web

El panel de administración permite gestionar el contenido principal del juego y consultar la información registrada por la aplicación.

Funcionalidades principales:

* Inicio de sesión de administrador.
* Dashboard principal.
* Visualización de usuarios registrados.
* Gestión de preguntas y respuestas.
* Creación, edición y eliminación de preguntas.
* Selector de archivos para subir imágenes en preguntas y respuestas.
* Visualización de respuestas asociadas a cada pregunta.
* Consulta del ranking actual.
* Reset del ranking.
* Guardado del ranking en histórico mensual.
* Consulta de ganadores mensuales.
* Registro de instalaciones o primeros usos detectados desde la app.
* Gestión del catálogo de espadas del gacha.

---

## Gestión de preguntas y respuestas

Desde el panel de administración se pueden gestionar las preguntas que después consume la aplicación Android.

Cada pregunta puede tener:

* Texto de pregunta.
* Imagen asociada.
* Nivel.
* Cuatro respuestas.
* Imagen opcional en cada respuesta.
* Una única respuesta correcta.

El panel permite crear, editar y eliminar preguntas, además de revisar si las respuestas están correctamente configuradas.

También se añadió un selector de archivos para facilitar la subida de imágenes. De esta forma, el administrador no tiene que escribir manualmente rutas como `nivel1/q1.png`, sino que puede seleccionar la imagen desde su ordenador y el sistema la sube automáticamente a la carpeta correspondiente del proyecto.

Las imágenes de preguntas se guardan en carpetas como:

```text
img/nivel1/
```

Ejemplo de ruta guardada:

```text
nivel1/q1.png
```

---

## Gestión del gacha y espadas

Se ha añadido una sección específica en el panel de administración para gestionar el catálogo de espadas del gacha.

Archivos principales:

```text
admin/espadas.php
admin/crear_espada.php
admin/editar_espada.php
admin/eliminar_espada.php
```

Desde esta sección el administrador puede:

* Ver todas las espadas registradas.
* Crear nuevas espadas.
* Editar espadas existentes.
* Eliminar espadas.
* Asignar nombre.
* Asignar rareza.
* Añadir descripción.
* Subir imagen desde el panel.

La tabla principal usada para el catálogo del gacha es:

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

Las rarezas disponibles son:

```text
COMUN
RARA
EPICA
LEGENDARIA
```

Las imágenes del gacha se guardan en:

```text
img/gacha/
```

Ejemplo de URL de imagen:

```text
http://www.shopkatanas.com/WEB_genshi/img/gacha/kratos.png
```

Esta mejora permite que en el futuro se puedan añadir nuevas espadas o colecciones desde el panel web sin tener que modificar directamente el código de la aplicación Android.

---

## Ranking

El ranking actual se gestiona mediante la tabla de puntuaciones.

La app y el panel consultan el ranking actual desde la tabla:

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

Flujo del ranking:

```text
Los jugadores juegan
↓
La puntuación se guarda en puntuaciones
↓
La app y el panel muestran el ranking actual
↓
El administrador realiza reset mensual
↓
El ranking se guarda en ranking_mensual_historico
↓
La tabla puntuaciones queda preparada para el nuevo periodo
```

---

## Instalaciones de la app

La sección inicialmente planteada como “Descargas” se adaptó a **“Instalaciones de la app”**.

No se registra una descarga directa desde Play Store, sino el primer uso detectado desde la aplicación Android.

Esta sección permite guardar información como:

* Usuario.
* Dispositivo.
* Versión de la app.
* Fecha de primer uso.

El sistema evita duplicar registros para el mismo usuario, dispositivo y versión de la app.

Esto permite diferenciar entre:

```text
Usuarios registrados → cuentas creadas en la aplicación
Instalaciones de la app → dispositivos o primeros usos detectados
```

---

## APIs principales

El backend cuenta con APIs PHP que permiten comunicar la base de datos con la aplicación Android.

---

### Obtener preguntas

```text
api/getPreguntas.php?nivel=1
```

Devuelve las preguntas de un nivel junto con sus respuestas en formato JSON.

---

### Guardar puntuación

```text
api/guardarPuntuacion.php
```

Recibe los datos de una partida y guarda la puntuación en la base de datos.

Datos principales:

```text
nombre
puntos
tiempo
```

Devuelve si la puntuación se ha guardado correctamente, la posición del jugador y si ha entrado en el Top 3.

---

### Obtener ranking

```text
api/getRanking.php
```

Devuelve el Top 10 del ranking actual en formato JSON.

---

### Registrar instalación o primer uso

```text
api/registrarDescarga.php
```

Registra el primer uso detectado de la app desde un dispositivo.

Aunque el archivo mantiene el nombre `registrarDescarga.php` por compatibilidad, su función real es registrar instalaciones o primeros usos detectados desde Android.

---

### APIs específicas usadas por la app Android

La aplicación Android también utiliza APIs dentro de:

```text
api_genshiken/
```

Algunas de las APIs principales son:

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
```

Estas APIs permiten que la app gestione usuarios, preguntas, ranking, monedas y colección de espadas.

---

## Base de datos

La base de datos MySQL contiene las tablas necesarias para gestionar el funcionamiento completo del proyecto.

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

* `usuarios`: almacena los usuarios registrados en la app.
* `preguntas`: almacena las preguntas del juego.
* `respuestas`: almacena respuestas asociadas a preguntas.
* `niveles`: almacena los niveles disponibles.
* `puntuaciones`: almacena el ranking actual.
* `ranking_mensual_historico`: almacena rankings guardados antes de un reset.
* `descargas`: registra instalaciones o primeros usos detectados.
* `monedas_usuario`: almacena las monedas de cada usuario.
* `coleccion_usuario`: almacena las espadas desbloqueadas por cada usuario.
* `espadas`: almacena el catálogo oficial de espadas del gacha.

---

## Hosting

El backend y el panel web se han desplegado en hosting para permitir que la aplicación Android pueda consumir las APIs de forma online.

URL base del proyecto web:

```text
http://www.shopkatanas.com/WEB_genshi/
```

Panel de administración:

```text
http://www.shopkatanas.com/WEB_genshi/admin/login.php
```

API de preguntas:

```text
http://www.shopkatanas.com/WEB_genshi/api/getPreguntas.php?nivel=1
```

API de ranking:

```text
http://www.shopkatanas.com/WEB_genshi/api/getRanking.php
```

Se utiliza FileZilla para subir y actualizar archivos del proyecto en el hosting.

Carpetas importantes en hosting:

```text
/WEB_genshi/admin/
/WEB_genshi/api/
/WEB_genshi/api_genshiken/
/WEB_genshi/img/nivel1/
/WEB_genshi/img/gacha/
```

---

## Aplicación Android

La app Android es la parte final utilizada por el usuario.

Funcionalidades principales:

* Registro de usuario.
* Inicio de sesión.
* Juego por niveles.
* Carga de preguntas desde la base de datos.
* Visualización del ranking.
* Sistema de monedas.
* Sistema de gacha.
* Sistema de colección de espadas.
* Pantalla inicial mejorada con logo y animación.

La aplicación obtiene los datos desde el backend PHP mediante APIs y no gestiona directamente la base de datos.

---

## Pantalla inicial de la app

Se ha mejorado la pantalla inicial de la aplicación para la versión 1.0.

Mejoras realizadas:

* Uso del logo de Genshiken C.S.
* Logo recortado con mejor calidad.
* Animación suave del logo.
* Fondo visual más cuidado.
* Accesos reorganizados a las secciones principales.
* Botones más claros para los modos de juego.
* Presentación más profesional de la app.

Accesos principales desde la pantalla inicial:

* Modo casual.
* Modo competitivo.
* Ranking.
* Gacha.
* Colección.

---

## Sistema de gacha en la app

La app incluye un sistema de gacha donde los usuarios pueden gastar monedas para conseguir espadas.

Funcionamiento general:

```text
El usuario juega partidas
↓
Obtiene monedas según su puntuación
↓
Usa monedas en el gacha
↓
Consigue espadas
↓
Las espadas desbloqueadas se guardan en su colección
```

Tablas relacionadas:

```text
monedas_usuario
coleccion_usuario
espadas
```

La tabla `espadas` actúa como catálogo oficial del gacha, mientras que `coleccion_usuario` guarda qué espadas ha conseguido cada usuario.

---

## Estado actual del proyecto

Actualmente el proyecto cuenta con:

* Panel de administración funcional.
* Backend PHP conectado a MySQL.
* Base de datos desplegada en hosting.
* APIs funcionando online.
* Gestión de preguntas y respuestas.
* Selector de archivos para subir imágenes de preguntas y respuestas.
* Ranking actual.
* Histórico mensual del ranking.
* Registro de instalaciones de la app.
* Gestión de usuarios registrados.
* Gestión del catálogo de espadas del gacha.
* Subida de imágenes del gacha desde el panel.
* App Android conectada mediante APIs.
* Pantalla inicial mejorada visualmente.
* Sistema de monedas, gacha y colección en Android.

---

## Seguridad

El archivo `config.php` contiene la configuración de conexión a la base de datos.

Por seguridad, no se deben subir credenciales reales del hosting, contraseñas privadas, usuarios FTP ni claves de base de datos a repositorios públicos.

El proyecto local puede utilizar una configuración diferente a la del hosting.

---

## Resumen final

El proyecto permite gestionar el contenido del juego desde un panel web y servir esos datos a una aplicación Android mediante APIs PHP.

El panel administra preguntas, respuestas, ranking, usuarios, instalaciones detectadas y el catálogo de espadas del gacha.

La aplicación Android consume esos datos para ofrecer la experiencia final al usuario: jugar, competir, obtener monedas, usar el gacha y completar su colección de espadas.
