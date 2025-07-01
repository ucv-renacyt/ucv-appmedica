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
    <link rel="stylesheet" href="../../Cliente/Vista/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .wrapper {
            background: rgba(45, 52, 87, 0.9);
            border-radius: 10px;
            padding: 30px 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            backdrop-filter: none;
        }
        .verifi{
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            font-size: 1.25rem!important; /* Tamaño más pequeño */
            color: #007bff;
            margin-bottom: 20px; /* Añadido para espaciar del input-box */
        }
        .input-box {
            position: relative;
            margin-bottom: 20px;
        }
        .input-box input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            color: black;
            padding: 10px 45px 10px 20px;
        }
        .input-box input::placeholder {
            color: #888;
        }
        .input-box i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            font-size: 20px;
            color: #888;
        }
        .btn {
            color: #fff!important;
            background-color: #007bff!important;
            border-radius: 5px!important;
        }
        .btn:hover {
            background-color: #0056b3!important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="verifi">
            <!-- Formulario de Verificación de cuenta -->
            <form action="#" method="POST">
                <h1 class="card-title">Verificación de cuenta</h1>
                <div class="input-box">
                    <input type="text" id="otp" name="otp_code" placeholder="Ingresar Código OTP" required>
                </div>
                
                <button type="submit" class="btn" name="verificar">VERIFICAR</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include "../../Cliente/Modelo/conexion.php";

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

                // Obtener id_usuario del usuario
                $stmt = $db->prepare('SELECT id_usuario FROM usuarios WHERE correo_institucional = :email');
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $id_usuario = $stmt->fetchColumn();

                // El ID del tipo de acceso "Activo" es 1
                $id_tipo = 1;

                // Actualizar registroacceso para el usuario
                $stmt = $db->prepare('UPDATE registroacceso SET id_tipo = :id_tipo, fecha_hora = NOW() WHERE id_usuario = :id_usuario');
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':id_tipo', $id_tipo);
                $stmt->execute();

                // Confirmar la transacción
                $db->commit();

                // Mostrar un mensaje de éxito y redirigir al usuario a la página de inicio de sesión
                echo '<script>alert("Verificación de cuenta realizada, puedes iniciar sesión ahora."); window.location.replace("../Vista/sadmin-login.html");</script>';
           } catch (PDOException $e) {
    $db->rollBack();
    echo '<script>
        alert("Error en la verificación: ' . $e->getMessage() . '");
        window.location.replace("../../Aministrador/Vista/admin-registro.html");
    </script>';
    exit();
}

        }
    } else {
        echo '<script>alert("No hemos podido verificar tu cuenta. Por favor, intenta registrarte de nuevo.");</script>';
    }
}
?>