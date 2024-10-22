<?php
require_once "db.php";
session_start();

if (isset($_POST['correo']) && isset($_POST['pass'])) {
    if (strlen($_POST['correo']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = 'datos incompletos';
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
        $_SESSION['error'] = 'usuario no encontrado, revisar usuario y/o contraseña';
    }
}

if (isset($_SESSION['error'])) {
    echo '<p style="color:red">' . $_SESSION['error'] . "</p>\n";
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>Ingreso al sistema</title>
</head><body>
<?php    
echo "<center>";
echo "<h1>José Mauricio López García, Carnet: 5190-21-352</h1>";  
echo "<h1>Emerson Sebastian Hernandez Rojas, Carnet: XXXXXX</h1>";  
echo "</center>";
echo "<p>";
echo "<strong>Archivo:</strong>" . $_SERVER['PHP_SELF'] . "<br>";
echo "<strong>Servidor:</strong>" . $_SERVER['SERVER_NAME'] . "<br>";
echo "<strong>Cliente IP:</strong>" . $_SERVER['REMOTE_ADDR'] . "<br>";
echo "<strong>Cliente Nombre:</strong>" . (isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : 'No disponible') . "<br>";
echo "<strong>User Agent:</strong>". $_SERVER['HTTP_USER_AGENT'] . "<br>";
echo "</p>";
?>
<h1>Ingrese sus credenciales</h1>
<div class="container">
<form method="post">
<p>correo:
<input type="text" name="correo" id="f_correo"></p>

<p>Password:
<input type="password" name="pass" id="f_pass"></p>

<p><input type="submit" onclick="return doValidate();" value="Log In"/>
<a href="login.php">Cancel</a></p>
</form>

<script>
function doValidate() {
         console.log('Validating...');
         try {
             pw = document.getElementById('f_pass').value;
			 em = document.getElementById('f_correo').value;
             console.log("Validating pw="+pw);
			 console.log("Validating em="+em);
             if (pw == null || pw == "") {
                 alert("Debe especificar ambos campos");
                 return false;
             }
             return true;
         } catch(e) {
             return false;
         }
         return false;
     }
</script>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0-alpha3/js/bootstrap.min.js"></script>
</body>
</html>
