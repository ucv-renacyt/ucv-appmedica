<?php
session_start();
include '../Modelo/mregistro.php';
require __DIR__ . '/../../Mail/phpmailer/PHPMailerAutoload.php';
include '../../Cliente/Modelo/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar y validar los datos del formulario
    $username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
    $apellido_paterno = trim(filter_var($_POST['apellido_paterno'], FILTER_SANITIZE_STRING));
    $apellido_materno = trim(filter_var($_POST['apellido_materno'], FILTER_SANITIZE_STRING));
    $carrera = trim(filter_var($_POST['carrera'], FILTER_SANITIZE_STRING));
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password-register'];
    $confirm_password = $_POST['confirm-password'];

    // Validaciones
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@ucvvirtual\.edu\.pe$/", $email)) {
        showErrorAlert('El correo electrónico debe ser válido y pertenecer al dominio de la UCV');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/", $username)) {
        showErrorAlert('El nombre solo puede contener letras y espacios');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]+$/", $apellido_paterno)) {
        showErrorAlert('El apellido paterno solo puede contener letras');
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ]*$/", $apellido_materno)) {
        showErrorAlert('El apellido materno solo puede contener letras');
    } elseif (strlen($password) < 8 || !preg_match("/[a-zA-Z]+/", $password) || !preg_match("/\d+/", $password) || !preg_match("/[\W_]+/", $password)) {
        showErrorAlert('La contraseña debe tener al menos 8 caracteres, una letra, un número y un carácter especial');
    } elseif ($password != $confirm_password) {
        showErrorAlert('Las contraseñas no coinciden');
    } else {
        $registro = new registro();

        if ($registro->existeCorreo($email, $db)) {
            showErrorAlert('El correo electrónico ya está registrado');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16)); // Token único

        try {
            $db->beginTransaction();

            $id_usuario = $registro->registrarUsuario(
                $username,
                $apellido_paterno,
                $apellido_materno,
                $carrera,
                $email,
                $hashed_password,
                $token,
                $db
            );

            $id_tipo = 2; // Inactivo
            $sql = "INSERT INTO registroacceso (id_usuario, fecha_hora, id_tipo) 
                    VALUES (:id_usuario, NOW(), :id_tipo_acceso)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':id_tipo_acceso', $id_tipo);
            $stmt->execute();

            $db->commit();

            // Enviar correo OTP
            $otp = rand(100000, 999999);
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = 'yeremyhuillcaa@gmail.com';
            $mail->Password = 'ldse diid naus oesg';

            $mail->CharSet = 'UTF-8';
            $mail->setFrom('yeremyhuillcaa@gmail.com', 'APP MÉDICA UCV - Verificación');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Tu código de verificación";
            $mail->Body = '
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
                    <p>Su código OTP de verificación es: <strong>' . $otp . '</strong></p>
                    <p>Por favor, ingresa este código en la pantalla de verificación para activar tu cuenta.</p>
                    </td>
                </tr>
                <tr style="background-color: #333; color: #fff; padding: 10px; text-align: center;">
                    <td>
                    <p style="margin-bottom: 0;">Gracias por unirte al equipo médico UCV</p>
                    <p style="margin-bottom: 0;">Atentamente</p>
                    <p style="margin-bottom: 0;">APP MÉDICA UCV</p>
                    </td>
                </tr>
                </table>';

            if (!$mail->send()) {
                showErrorAlert('Ocurrió un error al enviar el correo electrónico');
            } else {
                $_SESSION['otp'] = $otp;
                $_SESSION['mail'] = $email;
                showSuccessAlert('Registro exitoso. Se ha enviado un código OTP a tu correo electrónico.', '../Controlador/cverificacion.php');
            }
        } catch (PDOException $e) {
            $db->rollBack();
            showErrorAlert('Error al registrar el usuario: ' . $e->getMessage());
        }
    }
}

function showErrorAlert($message)
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.replace('../../Administrador/Vista/admin-registro.html');</script>";
    exit();
}

function showSuccessAlert($message, $redirectUrl)
{
    echo "<script>alert('" . addslashes($message) . "'); window.location.replace('" . $redirectUrl . "');</script>";
    exit();
}
