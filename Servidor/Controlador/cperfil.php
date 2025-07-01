<?php
// --- Configuración segura de cookies de sesión ---
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
header('Content-Type: application/json');

include "../../Cliente/Modelo/conexion.php";

//  Para depuración
file_put_contents("debug_sesion.txt", json_encode($_SESSION));

// Alternativa: autenticación por token en encabezado
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (str_starts_with($authHeader, 'Bearer ')) {
    $token = str_replace('Bearer ', '', $authHeader);
    // Aquí podrías validar un JWT o un token personalizado si decides usarlo
    // Este fragmento queda como base para una futura implementación
}

// Validar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // ----------------------------
    // MÉTODO GET - Cargar perfil
    // ----------------------------
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $stmt = $db->prepare("SELECT nombre, apellido_paterno, apellido_materno, telefono, correo_institucional, carrera, img_perfil FROM usuarios WHERE id_usuario = :id");
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($usuario ?: ['error' => 'Usuario no encontrado']);
        exit;
    }

    // ----------------------------
    // MÉTODO POST - Actualizar perfil
    // ----------------------------
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nombre = $_POST['nombre'] ?? '';
        $apellido_paterno = $_POST['apellido_paterno'] ?? '';
        $apellido_materno = $_POST['apellido_materno'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $carrera = $_POST['carrera'] ?? '';
        $clave = $_POST['clave'] ?? '';
        $confirmar_clave = $_POST['confirmar_clave'] ?? '';

        if ($clave !== $confirmar_clave) {
            echo json_encode(['error' => 'Las contraseñas no coinciden']);
            exit;
        }

        // Imagen (si aplica)
        $img_perfil = null;
        if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === 0) {
            $nombreArchivo = basename($_FILES['fotoPerfil']['name']);
            $rutaTemporal = $_FILES['fotoPerfil']['tmp_name'];
            $rutaDestino = "../../Cliente/Vista/img/perfiles/" . $nombreArchivo;
            move_uploaded_file($rutaTemporal, $rutaDestino);
            $img_perfil = $rutaDestino;
        }

        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET 
            nombre = :nombre, 
            apellido_paterno = :apellido_paterno, 
            apellido_materno = :apellido_materno, 
            telefono = :telefono, 
            carrera = :carrera, 
            clave = :clave";

        if ($img_perfil) {
            $sql .= ", img_perfil = :img_perfil";
        }

        $sql .= " WHERE id_usuario = :id_usuario";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt->bindParam(':apellido_materno', $apellido_materno);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->bindParam(':clave', $clave_hash);
        if ($img_perfil) {
            $stmt->bindParam(':img_perfil', $img_perfil);
        }
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        echo json_encode(['success' => 'Perfil actualizado correctamente']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}
