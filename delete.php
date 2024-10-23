<?php
require_once "db.php";
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    die("No has iniciado sesión");
}

// Verificar si se envía el formulario de eliminación
if (isset($_POST['delete']) && isset($_POST['id_alumno'])) {
    // Primero eliminamos los cursos asociados
    $sql = "DELETE FROM alumno_curso WHERE id_alumno = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $_POST['id_alumno']));

    // Luego eliminamos al alumno
    $sql = "DELETE FROM alumno WHERE id_alumno = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $_POST['id_alumno']));

    $_SESSION['success'] = 'Registro eliminado correctamente';
    header('Location: index.php');
    return;
}

// Verificar si se pasa el ID del alumno en la URL
if (!isset($_GET['id_alumno'])) {
    $_SESSION['error'] = "No se especificó ningún alumno para eliminar";
    header('Location: index.php');
    return;
}

// Obtener los datos del alumno
$stmt = $pdo->prepare("SELECT id_alumno, nombres, apellidos, correo FROM alumno WHERE id_alumno = :id");
$stmt->execute(array(":id" => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el alumno no existe, redirigir con mensaje de error
if ($row === false) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header('Location: index.php');
    return;
}

// Definir variables
$nombre_completo = $row['nombres'] . " " . $row['apellidos'];
$correo = $row['correo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Eliminar Alumno</h1>

    <div class="alert alert-danger">
        <strong>¿Estás seguro que deseas eliminar al alumno?</strong>
        <p>Nombre: <?= htmlentities($nombre_completo) ?></p>
        <p>Correo: <?= htmlentities($correo) ?></p>
    </div>

    <form method="post" class="mb-3">
        <input type="hidden" name="id_alumno" value="<?= htmlentities($row['id_alumno']) ?>">
        <button type="submit" name="delete" class="btn btn-danger">Eliminar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
