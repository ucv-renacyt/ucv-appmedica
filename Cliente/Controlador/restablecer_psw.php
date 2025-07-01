<?php
session_start();
include "../Modelo/conexion.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../Vista/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
          <!-- Restablecer contraseña -->
          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <h1>Restablece tu contraseña</h1>
            <div class="input-box">
              <input type="password" id="password" name="password" placeholder="Contraseña" required>
            </div>
            <div class="input-box">
              <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirmar contraseña" required>
            </div>
        
            <button type="submit" class="btn" name="restablecer">RESTABLECER</button>
          </form>
    </div>
</body>
</html>

<?php
include "../Modelo/conexion.php";

// Verificar si la sesión ya existe
if (!isset($_SESSION['token']) || !isset($_SESSION['email'])) {
    header('Location: ../Vista/index.html');
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
        echo "<script>alert('" . $_SESSION['success'] . "'); window.location.replace('../Vista/index.html');</script>";
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al restablecer contraseña. Por favor, inténtelo de nuevo.";
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        exit;
    }
}
?>