<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Not logged in");
}

if (isset($_POST['delete']) && isset($_POST['id_alumno'])) {

    $sql = "DELETE FROM alumno_curso WHERE id_alumno = :id_alumno";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id_alumno' => $_POST['id_alumno']));

    $sql = "DELETE FROM alumno WHERE id_alumno = :id_alumno";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id_alumno' => $_POST['id_alumno']));

    $_SESSION['success'] = 'Registro eliminado exitosamente';
    header('Location: index.php');
    return;
}

if (!isset($_GET['id_alumno'])) {
    $_SESSION['error'] = "No se especificó el alumno a eliminar";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT id_alumno, nombres, apellidos, correo FROM alumno WHERE id_alumno = :id_alumno");
$stmt->execute(array(":id_alumno" => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header('Location: index.php');
    return;
}

$fn = ($row['nombres'] . ", " . $row['apellidos']);
$em = ($row['correo']);
?>

<html>
<head>
    <title>Eliminar alumno</title>
</head>
<body>
    <h1>Eliminar alumno</h1>
    <h1>¿Desea eliminar al alumno: <?= htmlentities($fn) ?>?</h1>
    <form method="post">
        <p>Alumno: <?= htmlentities($fn) ?></p>
        <p>Correo: <?= htmlentities($em) ?></p>
        <input type="hidden" name="id_alumno" value="<?= $row['id_alumno'] ?>">
        <input type="submit" value="Eliminar" name="delete">
        <a href="index.php">Cancelar</a>
    </form>
</body>
</html>
