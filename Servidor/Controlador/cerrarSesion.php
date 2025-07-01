<?php
session_start();
include '../../Cliente/Modelo/conexion.php';

// Verificar si la sesión está iniciada
if (isset($_SESSION['usuario_id'])) {
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // Inicia la transacción
        $db->beginTransaction();

        // El ID del tipo de acceso "Inactivo" es 2
        $id_tipo = 2;

        // Actualizar el registroacceso para el usuario al cerrar sesión
        $sqlUpdate = "UPDATE registroacceso SET id_tipo = :id_tipo, fecha_hora = NOW() WHERE id_usuario = :id_usuario";
        $stmtUpdate = $db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id_tipo', $id_tipo);
        $stmtUpdate->bindParam(':id_usuario', $id_usuario);
        $stmtUpdate->execute();

        // Confirmar la transacción
        $db->commit();

        // Destruir la sesión
        session_destroy();

        // Redirigir al usuario a la página de inicio de sesión
        header("Location: ../Vista/admin-login.html");
        exit();
    } catch (PDOException $e) {
        // Si hay un error, revertir la transacción y mostrar un mensaje de error
        $db->rollBack();
        echo "Error al cerrar sesión: " . $e->getMessage();
    }
} else {
    // Si la sesión no está iniciada, redirigir al usuario a la página de inicio de sesión
    header("Location: ../Vista/admin-login.html");
    exit();
}
?>
