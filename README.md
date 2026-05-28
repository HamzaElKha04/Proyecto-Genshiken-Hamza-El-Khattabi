**Commit recomendado:**

```text
Actualiza README con estado final del proyecto
```

````markdown
# Proyecto Genshiken

Proyecto desarrollado durante el periodo de prácticas de 2º DAM.

El objetivo principal del proyecto es crear una aplicación tipo trivia basada en anime y cultura japonesa, donde los usuarios puedan responder preguntas, competir por puntuación y consultar rankings.

El sistema está dividido en dos partes principales:

1. **Aplicación Android**, utilizada por los usuarios finales.
2. **Panel web de administración**, backend PHP, base de datos MySQL y APIs.

---

## Objetivo del proyecto

La finalidad del proyecto es que la aplicación Android consuma datos gestionados desde un panel web de administración.

Desde el panel web se pueden administrar preguntas, respuestas, usuarios, rankings e instalaciones detectadas de la aplicación.

La app Android obtiene la información desde el backend PHP mediante APIs conectadas a una base de datos MySQL.

Flujo principal del sistema:

```text
Panel admin web → Base de datos MySQL → APIs PHP → App Android
````

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
│   ├── config.php
│   └── style.css
│
├── api/
│   ├── getPreguntas.php
│   ├── guardarPuntuacion.php
│   ├── getRanking.php
│   └── registrarDescarga.php
│
├── css/
├── js/
├── img/
│
├── index.html
├── juego.html
└── ranking.html
```

---

## Panel de administración web

El panel de administración permite gestionar el contenido y consultar la información principal del juego.

Funcionalidades principales:

* Inicio de sesión de administrador.
* Dashboard principal.
* Visualización de usuarios registrados.
* Gestión de preguntas y respuestas.
* Creación, edición y eliminación de preguntas.
* Visualización de respuestas asociadas a cada pregunta.
* Consulta del ranking actual.
* Reset del ranking.
* Guardado del ranking en histórico mensual.
* Consulta de ganadores mensuales.
* Registro de instalaciones o primeros usos detectados desde la app.

---

## Gestión de preguntas y respuestas

Desde el panel de administración se pueden gestionar las preguntas que después consume la aplicación Android.

Cada pregunta puede tener:

* Texto de pregunta.
* Imagen asociada.
* Nivel.
* Respuestas asociadas.
* Una única respuesta correcta.

El panel permite revisar si una pregunta está correctamente configurada, comprobando si tiene respuestas y si existe una respuesta marcada como correcta.

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

---

## APIs principales

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
* Sistema de colección.
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

## Estado actual del proyecto

Actualmente el proyecto cuenta con:

* Panel de administración funcional.
* Backend PHP conectado a MySQL.
* Base de datos desplegada en hosting.
* APIs funcionando online.
* Gestión de preguntas y respuestas.
* Ranking actual.
* Histórico mensual.
* Registro de instalaciones de la app.
* App Android conectada mediante APIs.
* Pantalla inicial mejorada visualmente.

---

## Seguridad

El archivo `config.php` contiene la configuración de conexión a la base de datos.

Por seguridad, no se deben subir credenciales reales del hosting, contraseñas privadas, usuarios FTP ni claves de base de datos a repositorios públicos.

El proyecto local puede utilizar una configuración diferente a la del hosting.

---

## Resumen final

El proyecto permite gestionar el contenido del juego desde un panel web y servir esos datos a una aplicación Android mediante APIs PHP.

El panel administra preguntas, respuestas, ranking, usuarios e instalaciones detectadas, mientras que la app Android consume esos datos para ofrecer la experiencia final al usuario.

```
```
