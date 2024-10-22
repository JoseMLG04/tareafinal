<?php
require_once "db.php";
session_start();

if (isset($_POST['correo']) && isset($_POST['pass'])) {
    if (strlen($_POST['correo']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = 'Datos incompletos';
        header("Location: login.php");
        return;
    }

    if (strpos($_POST['correo'], '@') === false) {
        $_SESSION['error'] = 'Correo no válido';
        header("Location: login.php");
        return;
    }

    $check = $_POST['pass'];
    $stmt = $pdo->prepare('SELECT id_usuario, nombre FROM usuario WHERE correo = :em AND password = :pw');
    $stmt->execute(array(':em' => $_POST['correo'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row !== false) {
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['success'] = "Bienvenido " . $row['nombre'];
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = 'Usuario no encontrado, revisar usuario y/o contraseña';
    }
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger text-center" role="alert" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); width: 80%;">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso al sistema</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: gray;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1 class="mb-4">Ingrese sus credenciales</h1>
    <form method="post">
        <div class="mb-3">
            <label for="f_correo" class="form-label">Correo:</label>
            <input type="text" name="correo" id="f_correo" class="form-control">
        </div>
        <div class="mb-3">
            <label for="f_pass" class="form-label">Password:</label>
            <input type="password" name="pass" id="f_pass" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Log In</button>
        <a href="login.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0-alpha3/js/bootstrap.min.js"></script>
</body>
</html>
