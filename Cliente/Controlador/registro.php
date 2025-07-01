<?php
session_start();
include "../Modelo/conexion.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Capturar y validar los datos del formulario
    $username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
    $apellido_paterno = trim(filter_var($_POST['apellido_paterno'], FILTER_SANITIZE_STRING));
    $apellido_materno = trim(filter_var($_POST['apellido_materno'], FILTER_SANITIZE_STRING));
    $carrera = trim(filter_var($_POST['carrera'], FILTER_SANITIZE_STRING));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password-register'];
    $confirm_password = $_POST['confirm-password'];

    // Validar el formato del correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@ucvvirtual\.edu\.pe$/", $email)) {
        showErrorAlert('El correo electrónico debe ser válido y pertenecer al dominio de la UCV');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/", $username)) {
        showErrorAlert('El nombre solo puede contener letras y espacios');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]+$/", $apellido_paterno)) {
        showErrorAlert('El apellido paterno solo puede contener letras');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]+$/", $apellido_materno)) {
        showErrorAlert('El apellido materno solo puede contener letras');
    } elseif (strlen($password) < 8 || !preg_match("/[a-zA-Z]+/", $password) || !preg_match("/\d+/", $password) || !preg_match("/[\W_]+/", $password)) {
        showErrorAlert('La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una letra minúscula, un número y un carácter especial');
    } elseif ($password != $confirm_password) {
        showErrorAlert('Las contraseñas no coinciden');
    } else {
        // Verificar si el correo electrónico ya existe en la base de datos
        $sql = "SELECT * FROM usuarios WHERE correo_institucional = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            showErrorAlert('El correo electrónico ya está registrado');
        } else {
            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // El rol predeterminado del usuario es el id 1
            $id_rol = 1;

            // Iniciar una transacción
            $db->beginTransaction();

            // Preparar la consulta SQL para insertar datos
            $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, carrera, correo_institucional, clave, id_rol) 
                    VALUES (:username, :apellido_paterno, :apellido_materno, :carrera, :email, :hashed_password, :id_rol)";

            try {
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':apellido_paterno', $apellido_paterno);
                $stmt->bindParam(':apellido_materno', $apellido_materno);
                $stmt->bindParam(':carrera', $carrera);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':hashed_password', $hashed_password);
                $stmt->bindParam(':id_rol', $id_rol);
                $stmt->execute();
                
                // Obtener el ID del usuario recién insertado
                $id_usuario = $db->lastInsertId();

                // El ID del tipo de acceso "Inactivo" es 2
                $id_tipo = 2;

                // Insertar datos en la tabla registroacceso
                $sql = "INSERT INTO registroacceso (id_usuario, fecha_hora, id_tipo) 
                        VALUES (:id_usuario, NOW(), :id_tipo_acceso)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':id_tipo_acceso', $id_tipo);
                $stmt->execute();

                // Confirmar la transacción
                $db->commit();
                
                // Generar código OTP
                $otp = rand(100000, 999999);

                // Enviar correo electrónico con código OTP
                require __DIR__. '/../../Mail/phpmailer/PHPMailerAutoload.php';
                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->Host='smtp.gmail.com';
                $mail->Port=587;
                $mail->SMTPAuth=true;
                $mail->SMTPSecure='tls';

                $mail->Username='yeremyhuillcaa@gmail.com';
                $mail->Password='ldse diid naus oesg';

                $mail->CharSet = 'UTF-8';
                $mail->setFrom('yeremyhuillcaa@gmail.com', 'APP MÉDICA UCV - Verificación');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject="Tu código de verificación";
                $body='
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f9f9f9; padding: 20px;">
                <tr>
                    <td align="center">
                    <img src="https://ucv.blackboard.com/branding/_1_1/loginLogo/CustomLoginLogo.png?m=l1qqe9vb" alt="APP MÉDICA" style="width: 100px; margin-bottom: 20px;">
                    </td>
                </tr>
                <tr>
                    <td style="font-family: Arial, sans-serif; font-size: 16px; color: #333;">
                    <h2 style="margin-top: 0;">Estimado usuario,</h2>
                    <p>Gracias por registrarte en APP MÉDICA. Para completar tu registro, necesitamos que verifiques tu cuenta de correo electrónico.</p>
                    <p>Su código OTP de verificación es: <strong>'. $otp. '</strong></p>
                    <p>Por favor, ingresa este código en la pantalla de verificación para activar tu cuenta.</p>
                    </td>
                </tr>
                <tr style="background-color: #333; color: #fff; padding: 10px; text-align: center;">
                    <td>
                    <p style="margin-bottom: 0;">Atentamente</p>
                    <p style="margin-bottom: 0;">APP MÉDICA</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 20px;">
                    <a href="https://www.youtube.com/channel/UCKRZp3mkvL1CBYKFIlxjDdg" target="_blank" style="text-decoration: none; color: #337ab7;">
                        <img src="https://ucv.blackboard.com/branding/_1_1/loginLogo/CustomLoginLogo.png?m=l1qqe9vb" style="width: 140px; margin-right: 10px;">
                        App Médica UCV
                    </a>
                    </td>
                </tr>
                </table>
                ';
                $mail->Body = $body;

                if(!$mail->send()){
                    showErrorAlert('Registro fallido, correo electrónico no válido');
                }else{
                    // Establecer variables de sesión
                    $_SESSION['otp'] = $otp;
                    $_SESSION['mail'] = $email;
                    showSuccessAlert('Registro Exitoso, OTP enviada a '. $email, '../Controlador/verificacion.php');
                }
            } catch (PDOException $e) {
                $db->rollBack();
                showErrorAlert('No se pudo registrar los datos: '. $e->getMessage());
            }
        }
    }
}

function showErrorAlert($message) {
    echo "<script>alert('". $message. "'); window.location.replace('../Vista/registro.html');</script>";
    exit();
}

function showSuccessAlert($message, $redirectUrl) {
    echo "<script>alert('". $message. "'); window.location.replace('" . $redirectUrl . "');</script>";
    exit();
}
?>