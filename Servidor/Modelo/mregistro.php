<?php

include '../../Cliente/Modelo/conexion.php'; // La ruta del archivo de conexión

class registro {

    public function existeCorreo($email, $db) {
        $sql = "SELECT id_usuario FROM usuarios WHERE correo_institucional = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function registrarUsuario($username, $apellido_paterno, $apellido_materno, $carrera, $email, $hashed_password, $db) {
        // Aquí asignamos el rol de Estudiante (id_rol = 1)
        $id_rol = 2;

        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, carrera, correo_institucional, clave, id_rol) 
                    VALUES (:username, :apellido_paterno, :apellido_materno, :carrera, :email, :hashed_password, :id_rol)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt->bindParam(':apellido_materno', $apellido_materno);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashed_password', $hashed_password);
        $stmt->bindParam(':id_rol', $id_rol); // Asignamos el id_rol correspondiente a Estudiante
        $stmt->execute();

        // Retornar el id_usuario del usuario recién registrado
        return $db->lastInsertId();
    }
}

?>
