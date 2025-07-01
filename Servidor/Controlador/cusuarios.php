<?php
session_start();
include "../../Cliente/Modelo/conexion.php";

try {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no autenticado']);
        exit;
    }

    $id_usuario = $_SESSION['usuario_id'];
    setlocale(LC_TIME, 'es_PE.UTF-8', 'Spanish_Peru', 'Spanish');

    // === DETALLE USUARIO ===
    if (isset($_GET['action']) && $_GET['action'] === 'ver' && isset($_GET['id_usuario'])) {
        $id_usuario_ver = $_GET['id_usuario'];
        $sql_detalle = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_institucional,
                               r.nombre AS rol_nombre, ta.nombre AS tipo_acceso, ec.fecha_hora
                        FROM usuarios u
                        INNER JOIN roles r ON u.id_rol = r.id_rol
                        LEFT JOIN registroacceso ec ON u.id_usuario = ec.id_usuario
                        LEFT JOIN tipoacceso ta ON ec.id_tipo = ta.id_tipo
                        WHERE u.id_usuario = :id_usuario_ver";
        $stmt = $db->prepare($sql_detalle);
        $stmt->bindParam(':id_usuario_ver', $id_usuario_ver, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            $fechaFormateada = ucfirst(strftime("%A, %d de %B de %Y", strtotime($detalle["fecha_hora"])));
            $fechaFormateada = mb_convert_encoding($fechaFormateada, 'UTF-8', 'HTML-ENTITIES');
            $horaFormateada = date("H:i", strtotime($detalle["fecha_hora"]));

            echo "<p><strong>ID de Usuario:</strong> " . htmlspecialchars($detalle['id_usuario']) . "</p>";
            echo "<p><strong>Nombre:</strong> " . htmlspecialchars($detalle['nombre']) . " " . htmlspecialchars($detalle['apellido_paterno']) . " " . htmlspecialchars($detalle['apellido_materno']) . "</p>";
            echo "<p><strong>Correo Institucional:</strong> " . htmlspecialchars($detalle['correo_institucional']) . "</p>";
            echo "<p><strong>Rol:</strong> " . htmlspecialchars($detalle['rol_nombre']) . "</p>";
            echo "<p><strong>Tipo de Acceso:</strong> " . htmlspecialchars($detalle['tipo_acceso']) . "</p>";
            echo "<p><strong>Fecha de acceso:</strong> " . htmlspecialchars($fechaFormateada) . " a las " . htmlspecialchars($horaFormateada) . "</p>";
        } else {
            echo "<p>No se encontraron detalles para este usuario.</p>";
        }
        $db = null;
        exit;
    }

    // === FORMULARIO EDICIÓN ===
    if (isset($_GET['action']) && $_GET['action'] === 'editar' && isset($_GET['id_usuario'])) {
        $id_usuario_editar = $_GET['id_usuario'];
        $sql = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno, correo_institucional, id_rol
                FROM usuarios WHERE id_usuario = :id_usuario_editar";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_usuario_editar', $id_usuario_editar, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<form id='formEditarUsuario'>";
            echo "<input type='hidden' name='id_usuario' value='" . htmlspecialchars($detalle['id_usuario']) . "'>";
            echo "<div class='mb-3'><label for='nombre' class='form-label'>Nombre</label><input type='text' class='form-control' name='nombre' value='" . htmlspecialchars($detalle['nombre']) . "' required></div>";
            echo "<div class='mb-3'><label for='apellido_paterno' class='form-label'>Apellido Paterno</label><input type='text' class='form-control' name='apellido_paterno' value='" . htmlspecialchars($detalle['apellido_paterno']) . "' required></div>";
            echo "<div class='mb-3'><label for='apellido_materno' class='form-label'>Apellido Materno</label><input type='text' class='form-control' name='apellido_materno' value='" . htmlspecialchars($detalle['apellido_materno']) . "'></div>";
            echo "<div class='mb-3'><label for='correo_institucional' class='form-label'>Correo</label><input type='email' class='form-control' name='correo_institucional' value='" . htmlspecialchars($detalle['correo_institucional']) . "' required></div>";
            echo "<div class='mb-3'><label for='id_rol' class='form-label'>Rol</label><select name='id_rol' class='form-select' required>";
            $stmt_roles = $db->query("SELECT id_rol, nombre FROM roles");
            while ($rol = $stmt_roles->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='" . htmlspecialchars($rol['id_rol']) . "'" . ($detalle['id_rol'] == $rol['id_rol'] ? " selected" : "") . ">" . htmlspecialchars($rol['nombre']) . "</option>";
            }
            echo "</select></div>";
            echo "<button type='submit' class='btn btn-primary'>Guardar cambios</button>";
            echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>";
            echo "</form>";
        } else {
            echo "<p>No se encontraron detalles para este usuario.</p>";
        }
        $db = null;
        exit;
    }

    // === ACTUALIZAR USUARIO ===
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_usuario'])) {
        $sql = "UPDATE usuarios SET nombre = :nombre, apellido_paterno = :apellido_paterno, apellido_materno = :apellido_materno, correo_institucional = :correo_institucional, id_rol = :id_rol WHERE id_usuario = :id_usuario";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':apellido_paterno' => $_POST['apellido_paterno'],
            ':apellido_materno' => $_POST['apellido_materno'],
            ':correo_institucional' => $_POST['correo_institucional'],
            ':id_rol' => $_POST['id_rol'],
            ':id_usuario' => $_POST['id_usuario']
        ]);
        echo "Actualización exitosa";
        $db = null;
        exit;
    }

    // === LISTADO CON BÚSQUEDA Y PAGINACIÓN ===
    if (isset($_GET['pagina']) || isset($_GET['buscar'])) {
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $limite = 5;
        $inicio = ($pagina - 1) * $limite;

        $condicion = "WHERE u.nombre LIKE :buscar OR u.apellido_paterno LIKE :buscar OR u.apellido_materno LIKE :buscar OR u.correo_institucional LIKE :buscar";
        $sql_total = "SELECT COUNT(*) FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol LEFT JOIN registroacceso ec ON u.id_usuario = ec.id_usuario LEFT JOIN tipoacceso ta ON ec.id_tipo = ta.id_tipo $condicion";
        $stmt = $db->prepare($sql_total);
        $stmt->bindValue(':buscar', "%$buscar%", PDO::PARAM_STR);
        $stmt->execute();
        $total = $stmt->fetchColumn();
        $paginas = ceil($total / $limite);

        $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_institucional, r.nombre AS rol_nombre, ta.nombre AS tipo_acceso FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol LEFT JOIN registroacceso ec ON u.id_usuario = ec.id_usuario LEFT JOIN tipoacceso ta ON ec.id_tipo = ta.id_tipo $condicion ORDER BY u.id_usuario LIMIT $inicio, $limite";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':buscar', "%$buscar%", PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<table class='table table-striped'><thead><tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>";
            while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>{$u['id_usuario']}</td><td>{$u['nombre']}</td><td>{$u['apellido_paterno']} {$u['apellido_materno']}</td><td>{$u['correo_institucional']}</td><td>{$u['rol_nombre']}</td><td>{$u['tipo_acceso']}</td><td><button class='btn btn-info btn-sm ver-usuario' data-id='{$u['id_usuario']}'>Ver</button> <button class='btn btn-warning btn-sm editar-usuario' data-id='{$u['id_usuario']}'>Editar</button></td></tr>";
            }
            echo "</tbody></table><div id='paginacion' class='pagination'>";
            if ($paginas > 1) {
                if ($pagina > 1) {
                    echo "<button class='pagination-button anterior'><i class='bx bx-chevron-left'></i></button>";
                }
                for ($i = 1; $i <= $paginas; $i++) {
                    echo "<button class='pagination-button" . ($i == $pagina ? " active" : "") . "'>$i</button>";
                }
                if ($pagina < $paginas) {
                    echo "<button class='pagination-button siguiente'><i class='bx bx-chevron-right'></i></button>";
                }
            }
            echo "</div>";
        } else {
            echo "<p class='text-center'>No hay resultados para la búsqueda.</p>";
        }
        $db = null;
        exit;
    }

    // === LISTADO POR DEFECTO CON PAGINACIÓN ===
    $pagina = 1;
    $limite = 5;
    $inicio = ($pagina - 1) * $limite;

    $sql_total = "SELECT COUNT(*) FROM usuarios";
    $stmt_total = $db->prepare($sql_total);
    $stmt_total->execute();
    $total = $stmt_total->fetchColumn();
    $paginas = ceil($total / $limite);

    $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_institucional, r.nombre AS rol_nombre, ta.nombre AS tipo_acceso 
            FROM usuarios u 
            INNER JOIN roles r ON u.id_rol = r.id_rol 
            LEFT JOIN registroacceso ec ON u.id_usuario = ec.id_usuario 
            LEFT JOIN tipoacceso ta ON ec.id_tipo = ta.id_tipo 
            ORDER BY u.id_usuario 
            LIMIT $inicio, $limite";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "<table class='table table-striped'><thead><tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>";
        while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$u['id_usuario']}</td><td>{$u['nombre']}</td><td>{$u['apellido_paterno']} {$u['apellido_materno']}</td><td>{$u['correo_institucional']}</td><td>{$u['rol_nombre']}</td><td>{$u['tipo_acceso']}</td><td><button class='btn btn-info btn-sm ver-usuario' data-id='{$u['id_usuario']}'>Ver</button> <button class='btn btn-warning btn-sm editar-usuario' data-id='{$u['id_usuario']}'>Editar</button></td></tr>";
        }
        echo "</tbody></table><div id='paginacion' class='pagination'>";
        if ($paginas > 1) {
            if ($pagina > 1) {
                echo "<button class='pagination-button anterior'><i class='bx bx-chevron-left'></i></button>";
            }
            for ($i = 1; $i <= $paginas; $i++) {
                echo "<button class='pagination-button" . ($i == $pagina ? " active" : "") . "'>$i</button>";
            }
            if ($pagina < $paginas) {
                echo "<button class='pagination-button siguiente'><i class='bx bx-chevron-right'></i></button>";
            }
        }
        echo "</div>";
    } else {
        echo "<p class='text-center'>No hay usuarios registrados.</p>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$db = null;
?>