<?php
session_start();
include "../Modelo/conexion.php";

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        echo "Usuario no autenticado";
        exit;
    }
    $id_usuario = $_SESSION['usuario_id'];

    // Configurar la localización en español para Perú
    setlocale(LC_TIME, 'es_PE.UTF-8', 'Spanish_Peru', 'Spanish');

    // Si se pasa el parámetro id_alerta en la solicitud GET, devolver los detalles de esa alerta
    if (isset($_GET['id_alerta'])) {
        $id_alerta = $_GET['id_alerta'];

        // Consulta SQL para obtener los detalles de la alerta específica
        $sql_detalle = "SELECT a.id_alerta, a.ubicacion, a.piso, a.especificacion, a.descripcion, a.sintomas, a.notas, ha.fecha, ha.estado 
                        FROM alertas a 
                        INNER JOIN historialalertas ha ON a.id_alerta = ha.id_alerta 
                        WHERE a.id_alerta = :id_alerta AND a.id_usuario = :id_usuario";
        $stmt_detalle = $db->prepare($sql_detalle);
        $stmt_detalle->bindParam(':id_alerta', $id_alerta, PDO::PARAM_INT);
        $stmt_detalle->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_detalle->execute();

        // Comprobar si se encontró el registro
        if ($stmt_detalle->rowCount() > 0) {
            $detalle = $stmt_detalle->fetch(PDO::FETCH_ASSOC);
            echo json_encode($detalle);
        } else {
            echo json_encode(['error' => 'No se encontraron detalles para esta alerta.']);
        }
        exit; // Salir para no continuar con el resto del código
    }

    //Editar alerta
    if (isset($_POST['editar_alerta']) && $_POST['editar_alerta'] == '1' && isset($_POST['id_alerta'])) {
        $id_alerta = $_POST['id_alerta'];
        $ubicacion = $_POST['ubicacion'];
        $piso = $_POST['piso'];
        $especificacion = $_POST['especificacion'];
        $descripcion = $_POST['descripcion'];
        $sintomas = $_POST['sintomas'];
        $notas = $_POST['notas'];

        $sql_editar = "UPDATE alertas 
                       SET ubicacion = :ubicacion,
                           piso = :piso,
                           especificacion = :especificacion,
                           descripcion = :descripcion,
                           sintomas = :sintomas,
                           notas = :notas
                       WHERE id_alerta = :id_alerta";
        $stmt_editar = $db->prepare($sql_editar);
        $stmt_editar->bindParam(':ubicacion', $ubicacion);
        $stmt_editar->bindParam(':piso', $piso);
        $stmt_editar->bindParam(':especificacion', $especificacion);
        $stmt_editar->bindParam(':descripcion', $descripcion);
        $stmt_editar->bindParam(':sintomas', $sintomas);
        $stmt_editar->bindParam(':notas', $notas);
        $stmt_editar->bindParam(':id_alerta', $id_alerta, PDO::PARAM_INT);

        if ($stmt_editar->execute()) {
            echo json_encode(['success' => true, 'message' => 'La alerta ha sido actualizada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la alerta.']);
        }
        exit;
    }


    // Si se recibe una solicitud POST para cancelar una alerta
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_alerta'])) {
        $id_alerta = $_POST['id_alerta'];

        // Consulta SQL para cancelar la alerta actualizando su estado
        $sql_cancelar = "UPDATE historialalertas SET estado = 'Cancelado' WHERE id_alerta = :id_alerta AND estado IN ('Pendiente', 'En proceso')";
        $stmt_cancelar = $db->prepare($sql_cancelar);
        $stmt_cancelar->bindParam(':id_alerta', $id_alerta, PDO::PARAM_INT);
        $stmt_cancelar->execute();

        // Comprobar si se actualizó correctamente
        if ($stmt_cancelar->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'La alerta ha sido cancelada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo cancelar la alerta. Verifique el estado actual de la alerta.']);
        }
        exit; // Salir para no continuar con el resto del código
    }

    // Consulta SQL para obtener las alertas con estado 'Pendiente' y 'En proceso' del usuario ordenadas por fecha descendente
    $sql = "SELECT ha.id_historial, ha.fecha, ha.estado, a.id_alerta, a.ubicacion, a.descripcion
            FROM historialalertas ha 
            INNER JOIN alertas a ON ha.id_alerta = a.id_alerta 
            WHERE a.id_usuario = :id_usuario AND ha.estado IN ('Pendiente', 'En proceso')
            ORDER BY ha.fecha DESC"; // Ordenar por fecha descendente

    // Preparar la consulta
    $stmt = $db->prepare($sql);

    // Vincular el parámetro
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();

    // Comprobar si se encontraron registros
    if ($stmt->rowCount() > 0) {
        // Inicializar una variable para almacenar la última fecha y su respectivo grupo de alertas
        $ultimaFecha = null;
        $alertasPorFecha = [];

        // Iniciar el contenedor principal para tablas responsivas
        echo "<div class='table-responsive'>";

        // Iterar sobre los resultados y organizarlos por fechas
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener el nombre del día en español
            $fechaFormateada = ucfirst(strftime("%A, %d de %B de %Y", strtotime($row["fecha"])));

            // Convertir a UTF-8 si es necesario
            $fechaFormateada = mb_convert_encoding($fechaFormateada, 'UTF-8', 'HTML-ENTITIES');
            // Obtener la hora
            $hora = date("H:i", strtotime($row["fecha"])); // Formato "HH:MM"
            $estado = htmlspecialchars($row["estado"]);
            $id_alerta = htmlspecialchars($row["id_alerta"]);

            // Si la fecha actual es diferente a la última fecha, imprimir el grupo anterior y comenzar uno nuevo
            if ($fechaFormateada != $ultimaFecha) {
                // Si ya hay alertas almacenadas en $alertasPorFecha, imprimir el grupo anterior
                if ($ultimaFecha !== null) {
                    echo "</tbody>";
                    echo "</table>";
                }

                // Actualizar la última fecha
                $ultimaFecha = $fechaFormateada;

                // Imprimir la nueva fecha como encabezado
                echo "<h5 class='mt-4'>" . $fechaFormateada . "</h5>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>Hora</th><th class='text-center'>Estado</th><th class='text-center'>Acciones</th></tr></thead>";
                echo "<tbody>";
            }

            // Imprimir cada alerta como una fila en la tabla
            echo "<tr>";
            echo "<td>" . $hora . "</td>";
            echo "<td class='text-center'><button class='btn " . getEstadoColor($estado) . "' disabled>" . $estado . "</button></td>";
            echo "<td class='text-center'>";
            echo "<button class='btn btn-primary ver-alerta' data-id-alerta='" . $id_alerta . "' data-bs-toggle='modal' data-bs-target='#alertaModal'>Ver</button>";
            echo "<button class='btn btn-danger cancelar-alerta' data-id-alerta='" . $id_alerta . "'>Cancelar</button>";
            /*echo "<button class='btn btn-warning editar-alerta' data-id-alerta='" . $id_alerta . "'>Editar</button>";*/
            echo "</td>";
            echo "</tr>";
        }

        // Finalizar la última tabla y el contenedor principal
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

    } else {
        echo "No hay registros de alertas pendientes o en proceso.";
    }
} catch (Exception $e) {
    // Manejar errores si la consulta falla
    echo "Error: " . $e->getMessage();
}

// Función para obtener el color de estado basado en el valor del estado
function getEstadoColor($estado) {
    switch ($estado) {
        case 'Pendiente':
            return 'btn-danger';
        case 'En proceso':
            return 'btn-warning';
        case 'Atendido':
            return 'btn-success';
        default:
            return 'btn-secondary';
    }
}

// Cerrar la conexión
$db = null;
?>
