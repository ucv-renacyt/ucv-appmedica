<?php
// Forzar configuración segura de la cookie de sesión
session_set_cookie_params([
    'lifetime' => 0,               // Solo sesión actual
    'path' => '/',                 // Válido para todo el dominio
    'httponly' => true,            // No accesible vía JS
    'samesite' => 'Lax'            // Compatible con fetch y formularios
]);

session_start();
header('Content-Type: application/json');

include '../../Cliente/Modelo/conexion.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM usuarios WHERE correo_institucional = :email AND id_rol = 2";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['estado'] == 1 && password_verify($password, $usuario["clave"])) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            // ⚠️ Reforzar la cookie manualmente si el hosting no lo hace automáticamente
            if (!headers_sent()) {
                setcookie("PHPSESSID", session_id(), [
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            // Registrar acceso
            $id_usuario = $usuario['id_usuario'];
            $sqlUpdate = "UPDATE registroacceso SET id_tipo = 1, fecha_hora = NOW() WHERE id_usuario = :id_usuario";
            $stmtUpdate = $db->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':id_usuario', $id_usuario);
            $stmtUpdate->execute();

            $response = [
                'success' => true,
                'redirect' => '../Vista/admin-main.html'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Credenciales inválidas o cuenta no verificada.'
            ];
        }

    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

echo json_encode($response);
