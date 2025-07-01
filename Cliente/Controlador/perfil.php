<?php
session_start();
include "../Modelo/conexion.php";

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../Vista/index.html");
        exit();
    }

    // Obtener el ID del usuario de la sesión
    $id_usuario = $_SESSION['usuario_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Consulta SQL para obtener la información del usuario
        $sql = "SELECT nombre, apellido_paterno, apellido_materno, carrera, correo_institucional, telefono, img_perfil FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        // Verificar si se encontró el usuario
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            // Extraer el nombre de usuario del correo electrónico
            $correo_completo = $usuario['correo_institucional'];
            $usuario_correo = substr($correo_completo, 0, strpos($correo_completo, '@')); // Extraer el nombre antes del '@'
            // Preparar los datos del usuario para enviar como JSON
            $response = [
                'nombre' => $usuario['nombre'],
                'apellido_paterno' => $usuario['apellido_paterno'],
                'apellido_materno' => $usuario['apellido_materno'],
                'nombre_completo' => $usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno'],
                'usuario_correo' => $usuario_correo, // Aquí está el nombre de usuario del correo
                'correo_completo' => $usuario['correo_institucional'], // Conservamos el correo completo por si se necesita más adelante
                'carrera' => $usuario['carrera'],
                'img_perfil' => $usuario['img_perfil']
            ];

            // Devolver la respuesta como JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            echo json_encode(['error' => 'Usuario no encontrado.']);
            exit();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Manejar la edición del perfil
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $carrera = $_POST['carrera'];

        // Verificar si se envió una nueva imagen de perfil
        $img_perfil = null;
        if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
            // Procesar la imagen y guardarla en una ruta específica
            $img_perfil = subirImagenPerfil($_FILES['fotoPerfil']);
            if (!$img_perfil) {
                echo json_encode(['error' => 'Error al subir la imagen de perfil']);
                exit;
            }
        }

        // Actualizar los datos del usuario en la base de datos
        $sql_update = "UPDATE usuarios 
                       SET nombre = :nombre, 
                           apellido_paterno = :apellido_paterno, 
                           apellido_materno = :apellido_materno, 
                           carrera = :carrera, 
                           img_perfil = :img_perfil 
                       WHERE id_usuario = :id_usuario";

        $stmt_update = $db->prepare($sql_update);
        $stmt_update->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt_update->bindParam(':apellido_paterno', $apellido_paterno, PDO::PARAM_STR);
        $stmt_update->bindParam(':apellido_materno', $apellido_materno, PDO::PARAM_STR);
        $stmt_update->bindParam(':carrera', $carrera, PDO::PARAM_STR);
        $stmt_update->bindParam(':img_perfil', $img_perfil, PDO::PARAM_STR);
        $stmt_update->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

        if ($stmt_update->execute()) {
            // Devolver respuesta exitosa
            echo json_encode(['success' => 'Perfil actualizado correctamente']);
            exit;
        } else {
            echo json_encode(['error' => 'Error al actualizar el perfil']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }
} catch (Exception $e) {
    // Manejar errores si la consulta falla
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

// Función para subir una imagen de perfil y retornar la ruta donde se guardó
function subirImagenPerfil($imagen)
{
    // Directorio donde se almacenarán las imágenes de perfil
    $directorio_destino = '../../Servidor/Vista/img/perfiles/';
    
    // Obtener información del archivo
    $nombre_archivo = $imagen['name'];
    $tipo_archivo = $imagen['type'];
    $tamano_archivo = $imagen['size'];
    $nombre_temporal = $imagen['tmp_name'];

    // Generar nombre único para el archivo
    $nombre_unico = uniqid('perfil_') . '_' . $nombre_archivo;

    // Mover el archivo temporal al directorio de destino
    if (move_uploaded_file($nombre_temporal, $directorio_destino . $nombre_unico)) {
        // Retornar la ruta completa donde se guardó la imagen
        return $directorio_destino . $nombre_unico;
    } else {
        return false;
    }
}
// Cerrar la conexión
$db = null;
?>
