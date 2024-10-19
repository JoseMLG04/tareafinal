<?php
require_once "db.php";
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    die("Ingresar al sistema");
}

// Verificar si se pasa el ID del alumno
if (isset($_GET['id_alumno'])) {
    $id_alumno = $_GET['id_alumno'];
    // Obtener los datos del alumno
    $stmt = $pdo->prepare('SELECT * FROM alumno WHERE id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el alumno no existe, redirigir con mensaje de error
    if ($alumno === false) {
        $_SESSION['error'] = 'Estudiante no encontrado';
        header("Location: index.php");
        return;
    }
    
    // Obtener los cursos del alumno
    $stmt = $pdo->prepare('SELECT * FROM alumno_curso ac JOIN curso c ON ac.id_curso = c.id_curso WHERE ac.id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Setear valores por defecto si no existen campos
$nombres = isset($alumno['nombres']) ? htmlentities($alumno['nombres']) : '';
$apellidos = isset($alumno['apellidos']) ? htmlentities($alumno['apellidos']) : '';
$correo = isset($alumno['correo']) ? htmlentities($alumno['correo']) : '';
$aficiones = isset($alumno['aficiones']) ? htmlentities($alumno['aficiones']) : '';

// Si se envía el formulario, procesar la actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar los datos
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

    // Actualizar los datos del alumno
    $stmt = $pdo->prepare('UPDATE alumno SET nombres = :nom, apellidos = :ape, correo = :cor, aficiones = :afi WHERE id_alumno = :id');
    $stmt->execute(array(
        ':nom' => htmlentities($_POST['nombres']),
        ':ape' => htmlentities($_POST['apellidos']),
        ':cor' => htmlentities($_POST['correo']),
        ':afi' => htmlentities($_POST['aficiones']),
        ':id' => $id_alumno
    ));

    // Actualizar los cursos del alumno
    $stmt = $pdo->prepare('DELETE FROM alumno_curso WHERE id_alumno = :id');
    $stmt->execute(array(':id' => $id_alumno));

    // Insertar los cursos editados
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) continue;
        $anio = $_POST['cur_anio' . $i];
        $curso = $_POST['cur_nombre' . $i];

        // Verificar que el curso tenga un nombre y año
        if (empty($anio) || empty($curso)) continue;

        $id_curso = false;

        // Verificar si el curso ya existe
        $stmt = $pdo->prepare('SELECT id_curso FROM curso WHERE nombre = :nom');
        $stmt->execute(array(':nom' => $curso));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $id_curso = $row['id_curso'];
        } else {
            // Insertar el curso si no existe
            $stmt = $pdo->prepare('INSERT INTO curso(nombre) VALUES (:nom)');
            $stmt->execute(array(':nom' => $curso));
            $id_curso = $pdo->lastInsertId();
        }

        // Insertar el curso del alumno
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

<html>
<head>
    <?php require_once('head.php'); ?>
    <title>Editar estudiante</title>
</head>
<body>
<h1>Editando Estudiante</h1>
<form method="post">
<p>Nombres:
<input type="text" name="nombres" id="fn" value="<?= $nombres ?>"></p>
<p>Apellidos:
<input type="text" name="apellidos" id="ln" value="<?= $apellidos ?>"></p>
<p>Correo:
<input type="text" name="correo" id="em" value="<?= $correo ?>"></p>
<p>Aficiones:
<br>
<textarea name="aficiones" rows="8" cols="80" id="afi"><?= $aficiones ?></textarea>
</p>

<p>
Cursos y/o certificaciones:
<input type="button" id="addCurso" value="+">
</p>
<div id="curso_fields">
<?php
$cuentaCur = 0;
foreach ($cursos as $curso) {
    $cuentaCur++;
    
    $anio = isset($curso['anio']) ? htmlentities($curso['anio']) : '';
    $nombre = isset($curso['nombre']) ? htmlentities($curso['nombre']) : '';

    echo('<div id="curso' . $cuentaCur . '">');
    echo('<p>Año: <input type="text" name="cur_anio' . $cuentaCur . '" value="' . $anio . '" />');
    echo('<input type="button" value="-" onclick="$(\'#curso' . $cuentaCur . '\').remove();return false;"></p>');
    echo('<p>Curso: <input type="text" name="cur_nombre' . $cuentaCur . '" class="cursos" value="' . $nombre . '" /></div>');
}
?>
</div>

<p><input type="submit" value="Guardar" onclick="return doValidate();"/>
<a href="index.php">Cancelar</a></p>
</form>

<script>
    let cuentaCur = <?= $cuentaCur ?>;
    $(document).ready( function() {
        $('#addCurso').click(function(event) {
            event.preventDefault();
            if (cuentaCur >= 9) {
                alert("Número máximo de cursos ingresados");
                return;
            }
            cuentaCur++;
            $('#curso_fields').append(
                '<div id="curso' + cuentaCur + '"> \
                <p>Año: <input type="text" name="cur_anio' + cuentaCur + '" value="" /> \
                <input type="button" value="-" onclick="$(\'#curso' + cuentaCur + '\').remove();return false;"></p> \
                <p>Curso: <input type="text" name="cur_nombre' + cuentaCur + '" class="cursos" value="" /></div>'
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
