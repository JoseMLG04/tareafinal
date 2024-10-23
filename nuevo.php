<?php 
require_once "db.php";
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die("Ingresar al sistema");
}

if (isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['correo']) && isset($_POST['aficiones'])) {
    if (strlen($_POST['nombres']) < 1 || strlen($_POST['apellidos']) < 1) {
        $_SESSION['error'] = 'Datos incompletos';
        header("Location: nuevo.php");
        return;
    }

    if (strpos($_POST['correo'], '@') === false) {
        $_SESSION['error'] = 'Correo electrónico no válido';
        header("Location: nuevo.php");
        return;
    }

    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['anio' . $i]) || !isset($_POST['curso' . $i])) continue;
        $anio = htmlentities($_POST['anio' . $i]);
        $curso = htmlentities($_POST['curso' . $i]);

        if (strlen($anio) == 0 || strlen($curso) == 0) {
            $_SESSION['error'] = 'Todos los campos son requeridos';
            header("Location: nuevo.php");
            return;
        }

        if (!is_numeric($anio)) {
            $_SESSION['error'] = 'Año debe ser numérico';
            header("Location: nuevo.php");
            return;
        }
    }

    $stmt = $pdo->prepare('INSERT INTO alumno(id_usuario, nombres, apellidos, correo, aficiones)
                           VALUES (:ide, :nom, :ape, :cor, :afi)');
    $stmt->execute(array(
        ':ide' => $_SESSION['id_usuario'],
        ':nom' => htmlentities($_POST['nombres']),
        ':ape' => htmlentities($_POST['apellidos']),
        ':cor' => htmlentities($_POST['correo']),
        ':afi' => htmlentities($_POST['aficiones'])
    ));

    $id_alumno = $pdo->lastInsertId();

    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) continue;
        $anio = $_POST['cur_anio' . $i];
        $curso = $_POST['cur_nombre' . $i];

        $stmt = $pdo->prepare('SELECT id_curso FROM curso WHERE nombre = :nom');
        $stmt->execute(array(':nom' => $curso));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_curso = $row ? $row['id_curso'] : false;

        if (!$id_curso) {
            $stmt = $pdo->prepare('INSERT INTO curso(nombre) VALUES (:nom)');
            $stmt->execute(array(':nom' => $curso));
            $id_curso = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare('INSERT INTO alumno_curso(id_alumno, anio, id_curso) 
                               VALUES (:ide, :anio, :idc)');
        $stmt->execute(array(
            ':ide' => $id_alumno,
            ':anio' => $anio,
            ':idc' => $id_curso
        ));
    }

    $_SESSION['success'] = 'Registro agregado correctamente';
    header('Location: index.php');
    return;
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . "</div>\n";
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once('head.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <title>Ingreso de nuevo estudiante</title>
</head>
<body class="container">
    <h1 class="my-4">Ingreso de nuevo estudiante</h1>

    <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="fn" class="form-label">Nombres:</label>
            <input type="text" name="nombres" id="fn" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="ln" class="form-label">Apellidos:</label>
            <input type="text" name="apellidos" id="ln" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="em" class="form-label">Correo:</label>
            <input type="email" name="correo" id="em" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="afi" class="form-label">Aficiones:</label>
            <textarea name="aficiones" rows="5" cols="80" id="afi" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <button id="addCurso" class="btn btn-primary">Agregar cursos y/o certificaciones</button>
        </div>
        <div id="curso_fields" class="mb-3"></div>

        <button type="submit" class="btn btn-success">Agregar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <script>
        let cuentaCur = 0;
        $(document).ready(function() {
            $('#addCurso').click(function(event) {
                event.preventDefault();
                if (cuentaCur >= 9) {
                    alert("Número máximo de cursos ingresados");
                    return;
                }
                cuentaCur++;
                $('#curso_fields').append(
                    `<div id="curso${cuentaCur}" class="mb-3">
                        <label>Año: <input type="text" name="cur_anio${cuentaCur}" class="form-control" /></label>
                        <label>Curso: <input type="text" name="cur_nombre${cuentaCur}" class="form-control cursos" /></label>
                        <input type="button" value="-" class="btn btn-danger btn-sm" onclick="$('#curso${cuentaCur}').remove();">
                    </div>`
                );

                $('.cursos').autocomplete({ source: "cursos.php" });
            });
        });
    </script>
</body>
</html>
