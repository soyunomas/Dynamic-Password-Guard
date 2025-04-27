=== Dynamic Password Guard ===
Contributors: soyunomas
Donate link:
Tags: security, login, password, authentication, dynamic password, time-based, brute force, hardening, seguridad, contraseña, autenticación, contraseña dinámica
Requires at least: 5.2
Tested up to: 6.5  # Actualiza esto a la última versión de WP con la que lo has probado
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Aumenta la seguridad del inicio de sesión requiriendo una contraseña base combinada con datos dinámicos basados en el tiempo, sin alterar la interfaz de usuario estándar.

== Description ==

Dynamic Password Guard (DPG) añade una capa de seguridad significativa a tu sitio WordPress contra ataques de fuerza bruta en el formulario de inicio de sesión. Funciona requiriendo que los usuarios combinen su contraseña habitual (contraseña base) con valores dinámicos basados en la fecha y hora actuales del servidor (según la zona horaria configurada en WordPress).

**Características Principales:**

*   **Protección Sigilosa:** No altera visualmente el formulario de inicio de sesión estándar de WordPress (`wp-login.php`), haciendo que la protección adicional sea invisible para los atacantes. Los mensajes de error son los genéricos de WordPress para no dar pistas.
*   **Contraseña Combinada:** Los usuarios con DPG activado deben introducir `[Valor Pre-Clave][Contraseña Base][Valor Post-Clave]` en el campo de contraseña normal.
*   **Configuración Flexible:**
    *   El Administrador puede habilitar/deshabilitar globalmente el plugin.
    *   El Administrador puede permitir (o no) que los usuarios configuren sus propias reglas.
    *   Los usuarios (si se les permite) pueden activar/desactivar DPG para su cuenta y elegir qué variables de tiempo usar como Pre-Clave y Post-Clave desde su perfil.
*   **Variables de Tiempo:** Selecciona entre Hora (HH), Minuto (MM), Día del Mes (DD), Mes (MM), Año (YY), Día de la Semana Numérico (1-7) o "Ninguna".

**¿Cómo Funciona?**

1.  El plugin intercepta el intento de login antes que WordPress lo procese (usando el filtro `authenticate` con prioridad 10).
2.  Comprueba si el usuario tiene una regla DPG activa.
3.  Si la tiene, calcula los valores esperados de Pre-Clave y Post-Clave basándose en la hora/fecha actual del servidor (según la zona horaria de WordPress).
4.  Verifica si la contraseña introducida **tiene el formato correcto** (empieza por Pre-Clave y termina por Post-Clave).
    *   Si el **formato es incorrecto**, el plugin fuerza un error inmediato para prevenir que se intente validar solo la contraseña base.
    *   Si el **formato es correcto**, extrae la presunta contraseña base del centro.
5.  Verifica la **contraseña base extraída** usando la función estándar `wp_check_password`.
    *   Si la base es **correcta**, permite el acceso.
    *   Si la base es **incorrecta**, devuelve `null` para que WordPress muestre el error genérico de contraseña incorrecta (Modo Sigiloso).

Este enfoque está diseñado para ser robusto contra ataques de fuerza bruta que prueban contraseñas comunes, ya que la contraseña requerida cambia constantemente.

== Installation ==

1.  **Opción 1: Subir el ZIP**
    *   Descarga el archivo `dynamic-password-guard.zip` desde el repositorio de GitHub o donde lo distribuyas.
    *   En tu panel de WordPress, ve a `Plugins` -> `Añadir nuevo`.
    *   Haz clic en `Subir plugin`.
    *   Selecciona el archivo `dynamic-password-guard.zip` y haz clic en `Instalar ahora`.
    *   Activa el plugin a través del menú 'Plugins' en WordPress.
2.  **Opción 2: Manualmente (o vía Git)**
    *   Clona o descarga el repositorio.
    *   Sube la carpeta `dynamic-password-guard` completa al directorio `/wp-content/plugins/` de tu sitio WordPress usando FTP, el administrador de archivos de tu hosting, o Git.
    *   Activa el plugin a través del menú 'Plugins' en WordPress.

**Configuración Inicial:**

1.  Ve a `Ajustes` -> `Dynamic Password Guard`.
2.  Marca la casilla "Habilitar Globalmente" para activar la funcionalidad del plugin.
3.  Decide si quieres "Permitir Configuración por Usuario" y marca la casilla si deseas que los usuarios puedan gestionar sus propias reglas. Guarda los cambios.
4.  Si permitiste la configuración por usuario, cada usuario (incluyendo administradores) deberá ir a `Usuarios` -> `Perfil` y configurar su propia sección de "Dynamic Password Guard" (activarla y elegir Pre/Post claves).

== Frequently Asked Questions ==

= ¿Cómo sé qué contraseña dinámica usar? =

Debes combinar los valores de tiempo/fecha actuales (según la zona horaria de tu WordPress) con tu contraseña base, siguiendo el patrón que configuraste en tu perfil.
*   **Ejemplo:** Si tu contraseña base es `MiPass123`, tu Pre-Clave es `Hora (HH)` y tu Post-Clave es `Día del Mes (DD)`, y la hora actual en WordPress son las **14**:30 del día **08**, deberás introducir `14MiPass12308` en el campo de contraseña.

= ¿Qué variables de tiempo están disponibles? =

*   Ninguna
*   Hora (00-23) (Formato HH)
*   Minuto (00-59) (Formato MM)
*   Día del Mes (01-31) (Formato DD)
*   Mes (01-12) (Formato MM)
*   Año (últimos 2 dígitos, ej. 24) (Formato YY)
*   Día de la Semana (1=Lunes, 7=Domingo) (Formato N)

= ¿Qué pasa si olvido mi patrón o no puedo acceder? =

Si un usuario no puede acceder porque olvidó su patrón o la hora del servidor es incorrecta:
1.  Un **Administrador** puede ir al perfil de ese usuario (`Usuarios` -> `Todos los usuarios` -> `Editar`).
2.  En la sección "Dynamic Password Guard" del perfil del usuario, el administrador puede **desmarcar** la casilla "Activar Contraseña Dinámica" y guardar los cambios.
3.  Ahora el usuario podrá iniciar sesión usando **solo su contraseña base normal**.
4.  El usuario podrá entonces reconfigurar su patrón DPG si lo desea.

= ¿Funciona con el reseteo de contraseña estándar de WordPress? =

Sí, el mecanismo "He olvidado mi contraseña" de WordPress debería funcionar normalmente, ya que no suele pasar por el mismo flujo de autenticación que el login directo. Si tienes problemas, desactiva temporalmente DPG para ese usuario como se describe arriba.

= ¿Es compatible con plugins de Autenticación de Dos Factores (2FA)? =

En teoría, debería ser compatible, ya que DPG actúa sobre el primer factor (la contraseña). DPG se engancha a `authenticate` con prioridad 10. Si tu plugin 2FA se engancha más tarde o en otros hooks, debería funcionar. Sin embargo, **se recomienda probar exhaustivamente** la compatibilidad con cualquier otro plugin que modifique el proceso de login.

= ¿Funciona con gestores de contraseñas? =

No directamente. Como la contraseña requerida cambia constantemente según la hora/fecha, los gestores de contraseñas no pueden autocompletarla correctamente. Deberás introducirla manualmente cada vez.

= ¿Qué importancia tiene la hora del servidor? =

¡Es **CRUCIAL**! El plugin se basa en la hora actual del servidor donde está alojado tu WordPress (interpretada según la zona horaria configurada en `Ajustes` -> `Generales`). Si la hora del servidor es incorrecta, los valores dinámicos calculados serán incorrectos y no podrás iniciar sesión. Asegúrate de que tu servidor tiene la hora sincronizada correctamente (usando NTP, por ejemplo).

== Screenshots ==

1.  **Página de Ajustes Globales:** Muestra las opciones disponibles para el administrador en `Ajustes` -> `Dynamic Password Guard`.
2.  **Sección en el Perfil de Usuario:** Muestra los campos que ve el usuario (si está permitido) en su página de `Usuarios` -> `Perfil`.
3.  **Formulario de Login:** Destaca que el formulario de `wp-login.php` no cambia visualmente.

== Changelog ==

= 1.1.0 =
* (Fix) Corregida la lógica para prevenir el inicio de sesión con solo la contraseña base cuando DPG está activo para un usuario. Ahora solo la contraseña dinámica completa permite el acceso.
* (Tweak) Ajustada la prioridad del filtro `authenticate` a 10 para asegurar que se ejecuta antes que las validaciones estándar de WP.
* (Dev) Limpieza de código y eliminación de logs de depuración. Versión lista para uso inicial.

= 1.0.0 =
* Versión inicial del plugin. Funcionalidad MVP.

