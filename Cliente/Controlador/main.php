<?php
session_start();
include "../Modelo/conexion.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
    exit;
}
var_dump($_SESSION['usuario_id']);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ubicacion = $_POST['ubicacion'];
    $piso = isset($_POST['piso']) ? $_POST['piso'] : null;
    $especificacion = $_POST['especificacion'];
    $descripcion = $_POST['descripcion'];
    $sintomas = $_POST['sintomas'];
    $notas = $_POST['notas'];
    $id_usuario = $_SESSION['usuario_id'];

    try {
        $db->beginTransaction();

        $sql_alertas = "INSERT INTO alertas (ubicacion, piso, especificacion, descripcion, sintomas, notas, id_usuario) 
                        VALUES (:ubicacion, :piso, :especificacion, :descripcion, :sintomas, :notas, :id_usuario)";
        $stmt_alertas = $db->prepare($sql_alertas);
        $stmt_alertas->bindParam(':ubicacion', $ubicacion);
        $stmt_alertas->bindParam(':piso', $piso);
        $stmt_alertas->bindParam(':especificacion', $especificacion);
        $stmt_alertas->bindParam(':descripcion', $descripcion);
        $stmt_alertas->bindParam(':sintomas', $sintomas);
        $stmt_alertas->bindParam(':notas', $notas);
        $stmt_alertas->bindParam(':id_usuario', $id_usuario);

        if ($stmt_alertas->execute()) {
            $id_alerta = $db->lastInsertId();

            $sql_historial = "INSERT INTO historialalertas (id_alerta, fecha, estado) 
                              VALUES (:id_alerta, NOW(), 'Pendiente')";
            $stmt_historial = $db->prepare($sql_historial);
            $stmt_historial->bindParam(':id_alerta', $id_alerta);

            if ($stmt_historial->execute()) {
                $db->commit();
                echo json_encode(['status' => 'success', 'message' => '¡Alerta médica enviada correctamente!']);
                exit;
            } else {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Error al enviar la alerta.']);
                exit;
            }
        } else {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Error al enviar la alerta.']);
            exit;
        }
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error al enviar la alerta.', 'error' => $e->getMessage()]);
        exit;
    }
}

$db = null;
?>
