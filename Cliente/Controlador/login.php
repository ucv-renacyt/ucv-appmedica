<?php
session_start();
include "../Modelo/conexion.php";
// Middleware de sesión: si ya está logueado, redirige automáticamente
if (isset($_POST['verificar_sesion'])) {
    if (isset($_SESSION['usuario_id'])) {
        echo json_encode(['success' => true, 'redirect' => '../Vista/main.html']);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Middleware directo: si accede manualmente al login y ya tiene sesión, redirigir
if (!isset($_POST['email']) && isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => true, 'redirect' => '../Vista/main.html']);
    exit;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Construir el correo electrónico completo
    $email = $email . '@ucvvirtual.edu.pe';

    // Preparar la consulta SQL para insertar datos
    $sql = "SELECT * FROM usuarios WHERE correo_institucional = :email";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            if ($usuario['estado'] == 1) { // Verificar si la cuenta está activa
                if (password_verify($password, $usuario["clave"])) {
                    $_SESSION['usuario_id'] = $usuario['id_usuario'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];

                    // Obtener el id_usuario y la fecha actual
                    $id_usuario = $usuario['id_usuario'];

                    // Actualizar el registro de acceso a activo (id_tipo = 1) y actualizar la fecha
                    $sqlUpdate = "UPDATE registroacceso SET id_tipo = 1, fecha_hora = NOW() WHERE id_usuario = :id_usuario";
                    $stmtUpdate = $db->prepare($sqlUpdate);
                    $stmtUpdate->bindParam(':id_usuario', $id_usuario);
                    $stmtUpdate->execute();

                    $response = ['success' => true, 'message' => 'Inicio de sesión exitoso', 'redirect' => '../Vista/main.html'];
                } else {
                    $response = ['success' => false, 'message' => 'El correo electrónico o la contraseña no son válidos, inténtelo de nuevo.'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Verifique la cuenta de correo electrónico antes de iniciar sesión.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Lo sentimos, sus credenciales no son válidas.'];
        }
    } catch (PDOException $e) {
        $response = ['success' => false, 'message' => 'No se pudo iniciar sesión: ' . $e->getMessage()];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
