<?php
session_start();
include '../../Cliente/Modelo/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
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
        .rest{
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
        }
        .btn:hover {
            background-color: #0056b3!important;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="rest">
            <!-- Restablecer contraseña -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <h1>Restablece tu contraseña de Super Administrador</h1>
                <div class="input-box">
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>
                <div class="input-box">
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmar contraseña" required>
                </div>
            
                <button type="submit" class="btn" name="restablecer">RESTABLECER</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include '../../Cliente/Modelo/conexion.php';

// Verificar si la sesión ya existe
if (!isset($_SESSION['token']) || !isset($_SESSION['email'])) {
    header('Location: ../Vista/sadmin-login.html');
    exit;
}

if (isset($_POST["restablecer"])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $token = $_SESSION['token']; // Acceder al token desde la sesión
    $email = $_SESSION['email']; // Acceder al correo electrónico desde la sesión

    // Verificar si las contraseñas coinciden
    if ($password != $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden. Por favor, inténtelo de nuevo.";
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        exit;
    }

    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    try {
        $stmt = $db->prepare('UPDATE usuarios SET clave = :password, token = NULL WHERE correo_institucional = :email');
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Eliminar el token de la sesión
        unset($_SESSION['token']);
        unset($_SESSION['email']);

        $_SESSION['success'] = "Contraseña restablecida con éxito. Puede iniciar sesión ahora.";
        echo "<script>alert('" . $_SESSION['success'] . "'); window.location.replace('../Vista/sadmin-login.html');</script>";
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al restablecer contraseña. Por favor, inténtelo de nuevo.";
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        exit;
    }
}
?>
