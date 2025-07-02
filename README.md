# APP MÉDICA UCV

Aplicación web para la gestión de alertas médicas y usuarios en la Universidad César Vallejo.

## Estructura del Proyecto

- `Cliente/`: Código fuente del cliente (usuarios)
- `Servidor/`: Código fuente del servidor (administradores)
- `Mail/`: Librerías para envío de correos
- `APP MÉDICA UCV - BD.txt`: Script SQL para la base de datos

## Requisitos

- PHP >= 7.0
- Servidor web (Apache, Nginx, XAMPP, etc.)
- MySQL o MariaDB

## Instalación

1. Clona este repositorio:
   ```bash
   git clone https://github.com/ucv-renacyt/ucv-appmedica.git
   ```
2. Configura la base de datos usando el archivo `APP MÉDICA UCV - BD.txt`.
3. Configura los archivos de conexión en `Cliente/Modelo/conexion.php` y `Servidor/Modelo/config.php`.
4. Coloca el proyecto en tu servidor web local.

## Base de Datos

### Estructura de Tablas

#### Tabla `roles`

- `id_rol`: Identificador único del rol
- `nombre`: Nombre del rol (Estudiante, Administrador)

#### Tabla `tipoacceso`

- `id_tipo`: Identificador único del tipo de acceso
- `nombre`: Nombre del tipo de acceso

#### Tabla `usuarios`

- `id_usuario`: Identificador único del usuario
- `nombre`: Nombre del usuario
- `apellido_paterno`: Apellido paterno
- `apellido_materno`: Apellido materno
- `telefono`: Número de teléfono
- `img_perfil`: Ruta de la imagen de perfil
- `carrera`: Carrera del estudiante
- `correo_institucional`: Correo electrónico institucional
- `clave`: Contraseña encriptada
- `estado`: Estado del usuario (activo/inactivo)
- `token`: Token de autenticación
- `id_rol`: Referencia al rol del usuario

#### Tabla `alertas`

- `id_alerta`: Identificador único de la alerta
- `ubicacion`: Ubicación donde ocurrió la emergencia
- `piso`: Piso del edificio
- `especificacion`: Especificación adicional de la ubicación
- `descripcion`: Descripción detallada de la emergencia
- `sintomas`: Síntomas reportados
- `notas`: Notas adicionales
- `fecha_hora`: Fecha y hora de la alerta
- `id_usuario`: Usuario que reportó la alerta

#### Tabla `historialalertas`

- `id_historial`: Identificador único del registro histórico
- `fecha`: Fecha del registro
- `estado`: Estado de la alerta
- `id_alerta`: Referencia a la alerta

#### Tabla `registroacceso`

- `id_registro`: Identificador único del registro de acceso
- `id_usuario`: Usuario que accedió
- `fecha_hora`: Fecha y hora del acceso
- `id_tipo`: Tipo de acceso realizado

### Configuración de la Base de Datos

1. Crea una base de datos MySQL llamada `appmedica`
2. Ejecuta el script SQL contenido en `APP MÉDICA UCV - BD.txt`
3. Configura las credenciales de conexión en los archivos de configuración

## Uso

- Accede a la carpeta `Cliente/Vista/index.html` para el portal de usuarios.
- Accede a `Servidor/Vista/sadmin-login.html` para el portal de administradores.

## Subir a GitHub

1. Inicializa el repositorio:
   ```bash
   git init
   git add .
   git commit -m "Primer commit"
   git branch -M main
   git remote add origin https://github.com/ucv-renacyt/ucv-appmedica.git
   git push -u origin main
   ```
