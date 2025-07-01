<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../Vista/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
          <!-- Formulario de Verificación de cuenta -->
          <form action="#" method="POST">
            <h1>Verificación de cuenta</h1>
            <div class="input-box">
                <input type="text" id="otp" name="otp_code" placeholder="Ingresar Código OTP" required>
            </div>
            
            <button type="submit" class="btn" name="verificar">VERIFICAR</button>
          </form>
    </div>
</body>
</html>

<?php
include "../Modelo/conexion.php";

// Verificar si el formulario ha sido enviado
if (isset($_POST["verificar"])) {
    // Verificar si las variables de sesión están establecidas
    if (isset($_SESSION['otp']) && isset($_SESSION['mail'])) {
        $otp = $_SESSION['otp'];
        $email = $_SESSION['mail'];

        // Obtener el código OTP ingresado por el usuario
        $otp_code = $_POST['otp_code'];

        // Verificar si el código OTP ingresado coincide con el almacenado en la sesión
        if ($otp != $otp_code) {
            // Si no coinciden, mostrar un mensaje de error amigable
            echo '<script>alert("Código OTP no válido, por favor inténtalo de nuevo.");</script>';
        } else {
            // Si coinciden, actualizar el estado del usuario en la base de datos
            try {
                $db->beginTransaction();

                // Actualizar estado del usuario
                $stmt = $db->prepare('UPDATE usuarios SET estado = 1 WHERE correo_institucional = :email');
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // Obtener el id_usuario del usuario verificado
                $stmt = $db->prepare('SELECT id_usuario FROM usuarios WHERE correo_institucional = :email');
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_usuario = $user['id_usuario'];

                // Actualizar el campo id_tipo en la tabla registroacceso para que sea 1 (Activo)
                $stmt = $db->prepare('UPDATE registroacceso SET id_tipo = 1, fecha_hora = NOW() WHERE id_usuario = :id_usuario');
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->execute();

                $db->commit();

                // Mostrar un mensaje de éxito y redirigir al usuario a la página de inicio de sesión
                echo '<script>alert("Verificación de cuenta realizada, puedes iniciar sesión ahora."); window.location.replace("../Vista/index.html");</script>';
            } catch (PDOException $e) {
                // En caso de error, revertir la transacción
                $db->rollBack();
                echo '<script>alert("Hubo un error en la verificación: ' . $e->getMessage() . '");</script>';
            }
        }
    } else {
        echo '<script>alert("No hemos podido verificar tu cuenta. Por favor, intenta registrarte de nuevo.");</script>';
    }
}
?>