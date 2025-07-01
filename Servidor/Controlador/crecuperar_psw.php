<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña para Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../../Cliente/Vista/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .wrapper {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin: 20px;
            backdrop-filter: none;
        }
        .recup{
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            font-size: 1.25em!important;
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
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3!important;
        }
        .register-link p {
            color: black;
        }
        .register-link p a {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="recup">
            <!-- Formulario de Verificación de cuenta -->
            <form action="#" method="POST">
                <h1>Recupera tu contraseña de Administrador</h1>
                <div class="input-box">
                    <input type="text" id="email" name="email" placeholder="Ingresar Correo Electrónico" required>
                </div>
                
                <button type="submit" class="btn" name="recuperar">RECUPERAR</button>
                <div class="register-link">
                <p>Volver a <a href="../Vista/admin-login.html">Iniciar sesión</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de notificación -->
    <div class="modal fade" id="notificacionModal" tabindex="-1" aria-labelledby="notificacionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificacionModalLabel">Correo electrónico enviado con éxito!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h2 class="alert-heading">Correo electrónico enviado con éxito!</h2>
                    <p>Por favor revise la bandeja de entrada de su correo electrónico.</p>
                    <hr>
                    <p class="mb-0">Si no ha recibido el correo electrónico, por favor revise su carpeta de spam o inténtelo de nuevo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="window.location.href='../Vista/admin-login.html';">REGRESAR A INICIO DE SESIÓN</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript de Bootstrap y Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-BM6tYC+UwUo7ZCq4GlN5p7OnYCkPhQ3/F2CdnDR4aXx7Y+aw5r7J/pKtuFq5ncR1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <?php
    include '../../Cliente/Modelo/conexion.php';

    // Verificar si el formulario ha sido enviado
    if (isset($_POST["recuperar"])) {
        // Obtener el correo electrónico ingresado por el usuario
        $email = $_POST['email'];

        // Validar el correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '<script>alert("El correo electrónico ingresado no es válido. Por favor, inténtelo de nuevo.");</script>';
            exit;
        }

        // Verificar si el correo electrónico existe en la base de datos
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE correo_institucional = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $fetch = $stmt->fetch();

        if (!$fetch) {
            // Si no existe, mostrar un mensaje de error amigable
            echo '<script>alert("No encontramos una cuenta asociada a ese correo electrónico.");</script>';
            exit;
        }

        // Comprobar si la cuenta está activa o verificada
        if ($fetch['estado'] == 0) {
            echo '<script>alert("Su cuenta no está verificada. Por favor, verifique su cuenta antes de recuperar su contraseña."); window.location.href="../Vista/admin-login.html";</script>';
            exit;
        }

        // Generar un token de recuperación de contraseña
        $token = password_hash(random_bytes(50), PASSWORD_BCRYPT);

        // Almacenar el token en la base de datos
        $stmt = $db->prepare('UPDATE usuarios SET token = :token WHERE correo_institucional = :email');
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Almacenar el token y el correo electrónico en la sesión
        $_SESSION['token'] = $token;
        $_SESSION['email'] = $email;

        // Enviar un correo electrónico de recuperación de contraseña
        require __DIR__. '/../../Mail/phpmailer/PHPMailerAutoload.php';
        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';

        // Cuenta de Gmail
        $mail->Username = 'yeremyhuillcaa@gmail.com';
        $mail->Password = 'ldse diid naus oesg';

        // Enviar desde la cuenta de Gmail
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('yeremyhuillcaa@gmail.com', 'APP MÉDICA UCV - Recuperación de contraseña');
        $mail->addAddress($email);

        // Cuerpo del correo electrónico en HTML
        $mail->isHTML(true);
        $mail->Subject = "Recupera tu contraseña de Administrador";
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Restablecer Contraseña</title>
          <style>
                body {
                font-family: Arial, sans-serif;
                background-color: #f9f99f9;
                }
                .container {
                    max-width: 600px;
                    margin: 40px auto;
                    padding: 20px;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background-color: #333;
                    color: #fff;
                    padding: 10px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                }
                .content {
                    padding: 20px;
                }
                .button {
                    background-color: #0059b3; /* Azul oscuro */
                    padding: 10px 20px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                .button:hover {
                    background-color: #003A6C; /* Azul más oscuro aún */
                }
            </style>
            </head>
            <body>
            <div class="container">
                <div class="header">
                <h1>Restablecer Contraseña</h1>
                </div>
                <div class="content">
                <p>Hola, <strong>'. $fetch['nombre']. '</strong>!</p>
                <p>Has solicitado restablecer tu contraseña. Haz clic en el botón para restablecerla:</p>
                <a href="http://localhost:3000/Servidor/Controlador/crestablecer_psw.php?token='. $token. '" class="button" style="color: #fff; text-decoration: none; font-size: 16px; font-weight: bold;">Restablecer Contraseña</a>
                <p>Si no has solicitado restablecer tu contraseña, ignora este correo electrónico.</p>
                <p>Atentamente,</p>
                <b>APP MÉDICA UCV</b>
                </div>
            </div>
            </body>
            </html>
            ';

        if (!$mail->send()) {
            echo '<script>alert("Error al enviar correo electrónico de recuperación de contraseña.");</script>';
        } else {
            // Mostrar el modal solo si se ha enviado el correo correctamente
            echo '<script>document.addEventListener("DOMContentLoaded", function() { var myModal = new bootstrap.Modal(document.getElementById("notificacionModal")); myModal.show(); });</script>';
        }
    }
    ?>
</body>
</html>
