<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de contraseña</title>
    <link rel="icon" type="image/x-icon" href="../Vista/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../Vista/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
          <!-- Formulario de Verificación de cuenta -->
          <form action="#" method="POST">
            <h1>Recupera tu contraseña</h1>
            <div class="input-box">
                <input type="text" id="email" name="email" placeholder="Ingresar Correo Electrónico" required>
            </div>
            
            <button type="submit" class="btn" name="recuperar">RECUPERAR</button>
            <div class="register-link">
              <p>Volver a <a
                href="../Vista/index.html">Iniciar sesión</a></p>
            </div>
          </form>
    </div>
</body>
</html>

<?php
include "../Modelo/conexion.php";

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

    // Comprobar si la cuenta está verificada
    if ($fetch['estado'] == 0) {
        echo '<script>alert("Su cuenta no está verificada. Por favor, verifique su cuenta antes de recuperar su contraseña."); window.location.href="../Vista/index.html";</script>';
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
    //$mail->addReplyTo('lamkaizhe16@gmail.com');

    // Cuerpo del correo electrónico en HTML
    $mail->isHTML(true);
    $mail->Subject = "Recupera tu contraseña";
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
            <a href="http://localhost/AppMedicaUCV/Cliente/Controlador/restablecer_psw.php?token='. $token. '" class="button" style="color: #fff; text-decoration: none; font-size: 16px; font-weight: bold;">Restablecer Contraseña</a>
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
        header('Location: ../Vista/notificacion.html');
        exit;
    }
}
?>