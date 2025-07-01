<?php
session_start();
include "../Modelo/conexion.php";

// Verificar si la sesión está iniciada
if (isset($_SESSION['usuario_id'])) {
    $id_usuario = $_SESSION['usuario_id'];

    try {
        // Inicia la transacción
        $db->beginTransaction();

        // Establecer el tipo de acceso como inactivo (por ejemplo, id_tipo = 2)
        $id_tipo = 2;

        // Actualizar el registro de acceso
        $sqlUpdate = "UPDATE registroacceso SET id_tipo = :id_tipo, fecha_hora = NOW() WHERE id_usuario = :id_usuario";
        $stmtUpdate = $db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id_tipo', $id_tipo);
        $stmtUpdate->bindParam(':id_usuario', $id_usuario);
        $stmtUpdate->execute();

        // Confirmar la transacción
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo "Error al cerrar sesión: " . $e->getMessage();
        exit();
    }
}

// Ahora sí: limpiar y destruir la sesión
session_unset();
session_destroy();

// Redirigir a login
header("Location: ../../Cliente/Vista/index.html");
exit();
?>
