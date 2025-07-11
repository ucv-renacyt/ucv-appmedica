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

    public function registrarUsuario($username, $apellido_paterno, $apellido_materno, $carrera, $email, $hashed_password, $token, $db) {
        $id_rol = 2; // Estudiante

        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, carrera, correo_institucional, clave, id_rol, token)
                VALUES (:username, :apellido_paterno, :apellido_materno, :carrera, :email, :hashed_password, :id_rol, :token)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':apellido_paterno', $apellido_paterno);
        $stmt->bindParam(':apellido_materno', $apellido_materno);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hashed_password', $hashed_password);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $db->lastInsertId();
    }
}

?>
