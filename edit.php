<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Ingresar al sistema");
}

if (isset($_GET['id_alumno'])) {
    $id_alumno = $_GET['id_alumno'];
    $stmt = $pdo->prepare('SELECT a.nombres, a.apellidos, a.correo, a.aficiones FROM alumno a WHERE id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($alumno === false) {
        $_SESSION['error'] = 'Estudiante no encontrado';
        header("Location: index.php");
        return;
    }
    
    $stmt = $pdo->prepare('SELECT ac.anio, c.nombre FROM alumno_curso ac JOIN curso c ON ac.id_curso = c.id_curso WHERE ac.id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$nombres = isset($alumno['nombres']) ? htmlentities($alumno['nombres']) : '';
$apellidos = isset($alumno['apellidos']) ? htmlentities($alumno['apellidos']) : '';
$correo = isset($alumno['correo']) ? htmlentities($alumno['correo']) : '';
$aficiones = isset($alumno['aficiones']) ? htmlentities($alumno['aficiones']) : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (strlen($_POST['nombres']) < 1 || strlen($_POST['apellidos']) < 1) {
        $_SESSION['error'] = 'Datos incompletos';
        header("Location: edit.php?id_alumno=" . $id_alumno);
        return;
    }
    if (strpos($_POST['correo'], '@') === false) {
        $_SESSION['error'] = 'Correo electrónico no válido';
        header("Location: edit.php?id_alumno=" . $id_alumno);
        return;
    }

    $stmt = $pdo->prepare('UPDATE alumno SET nombres = :nom, apellidos = :ape, correo = :cor, aficiones = :afi WHERE id_alumno = :id');
    $stmt->execute(array(
        ':nom' => htmlentities($_POST['nombres']),
        ':ape' => htmlentities($_POST['apellidos']),
        ':cor' => htmlentities($_POST['correo']),
        ':afi' => htmlentities($_POST['aficiones']),
        ':id' => $id_alumno
    ));

    $stmt = $pdo->prepare('DELETE FROM alumno_curso WHERE id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));

    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) continue;
        $anio = $_POST['cur_anio' . $i];
        $curso = $_POST['cur_nombre' . $i];

        if (empty($anio) || empty($curso)) continue;

        $stmt = $pdo->prepare('SELECT id_curso FROM curso WHERE nombre = :nom');
        $stmt->execute(array(':nom' => $curso));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $id_curso = $row['id_curso'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO curso(nombre) VALUES (:nom)');
            $stmt->execute(array(':nom' => $curso));
            $id_curso = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare('INSERT INTO alumno_curso(id_alumno, anio, id_curso) VALUES (:ide, :anio, :idc)');
        $stmt->execute(array(
            ':ide' => $id_alumno,
            ':anio' => $anio,
            ':idc' => $id_curso
        ));
    }

    $_SESSION['success'] = 'Registro actualizado!';
    header("Location: index.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Editando Estudiante</h1>
    <form method="post">
    <div class="mb-3">
            <label for="fn" class="form-label">Nombres:</label>
            <input type="text" class="form-control" name="nombres" id="fn" value="<?= $nombres ?>">
        </div>
        <div class="mb-3">
            <label for="ln" class="form-label">Apellidos:</label>
            <input type="text" class="form-control" name="apellidos" id="ln" value="<?= $apellidos ?>">
        </div>
        <div class="mb-3">
            <label for="em" class="form-label">Correo:</label>
            <input type="email" class="form-control" name="correo" id="em" value="<?= $correo ?>">
        </div>
        <div class="mb-3">
            <label for="afi" class="form-label">Aficiones:</label>
            <textarea name="aficiones" class="form-control" rows="8" id="afi"><?= $aficiones ?></textarea>
        </div>

        <div class="mb-3">
            <label>Cursos y/o certificaciones:</label>
            <input type="button" id="addCurso" class="btn btn-primary mb-3" value="+">
        </div>

        <div id="curso_fields">
            <?php
            $cuentaCur = 0;
            foreach ($cursos as $curso) {
                $cuentaCur++;
                
                $anio = isset($curso['anio']) ? htmlentities($curso['anio']) : '';
                $nombre = isset($curso['nombre']) ? htmlentities($curso['nombre']) : '';

                echo('<div id="curso' . $cuentaCur . '" class="mb-3">');
                echo('<div class="row mb-2"><div class="col"><label>Año:</label><input type="text" class="form-control" name="cur_anio' . $cuentaCur . '" value="' . $anio . '" /></div>');
                echo('<div class="col"><input type="button" class="btn btn-danger" value="-" onclick="$(\'#curso' . $cuentaCur . '\').remove();return false;"></div></div>');
                echo('<label>Curso:</label><input type="text" class="form-control cursos" name="cur_nombre' . $cuentaCur . '" value="' . $nombre . '" /></div>');
            }
            ?>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
    let cuentaCur = <?= $cuentaCur ?>;
    $(document).ready(function() {
        $('#addCurso').click(function(event) {
            event.preventDefault();
            if (cuentaCur >= 9) {
                alert("Número máximo de cursos ingresados");
                return;
            }
            cuentaCur++;
            $('#curso_fields').append(
                '<div id="curso' + cuentaCur + '" class="mb-3"> \
                <div class="row mb-2"><div class="col"><label>Año:</label> \
                <input type="text" class="form-control" name="cur_anio' + cuentaCur + '" value="" /></div> \
                <div class="col"><input type="button" class="btn btn-danger" value="-" onclick="$(\'#curso' + cuentaCur + '\').remove();return false;"></div></div> \
                <label>Curso:</label><input type="text" class="form-control cursos" name="cur_nombre' + cuentaCur + '" value="" /></div>'
            );
            $('.cursos').autocomplete({
                source: "cursos.php"
            });
        });
        $('.cursos').autocomplete({
            source: "cursos.php"
        });
    });
</script>
</body>
</html>