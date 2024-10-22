<?php
require_once "db.php";
session_start();
// verificar que se envió parametro
if ( ! isset($_GET['id_alumno']) ) {
  $_SESSION['error'] = "Alumno no especificado";
  header('Location: index.php');
  return;
}
$stmt = $pdo->prepare("SELECT * FROM alumno where id_alumno = :idx");
$stmt->execute(array(":idx" => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header( 'Location: index.php' ) ;
    return;
}
// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error']."</div>\n";
    unset($_SESSION['error']);
}

// Verificar si las claves existen y no son null antes de usar htmlentities
$fn = isset($row['NOMBRES']) ? htmlentities($row['NOMBRES']) : '';
$ln = isset($row['APELLIDOS']) ? htmlentities($row['APELLIDOS']) : '';
$em = isset($row['CORREO']) ? htmlentities($row['CORREO']) : '';
$af = isset($row['AFICIONES']) ? htmlentities($row['AFICIONES']) : '';
$id = $row['ID_ALUMNO'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Alumno</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container mt-5 px-4">
    <h1 class="mb-4">Consulta de Alumno</h1>
    <form>
        <div class="mb-3">
            <label class="form-label">Nombres:</label>
            <p class="form-control-plaintext"><?php echo($fn);?></p>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellidos:</label>
            <p class="form-control-plaintext"><?php echo($ln);?></p>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo:</label>
            <p class="form-control-plaintext"><?php echo($em);?></p>
        </div>
        <div class="mb-3">
            <label class="form-label">Aficiones:</label>
            <p class="form-control-plaintext"><?php echo($af);?></p>
        </div>
        <div class="mb-3">
            <label class="form-label">Cursos y certificaciones:</label>
            <ul class="list-group">
                <?php
                // Corregir nombres de tablas y columnas en la consulta SQL
                $stmt = $pdo->prepare("SELECT ac.anio, c.nombre FROM alumno_curso ac 
                                        INNER JOIN curso c 
                                        ON c.id_curso = ac.id_curso
                                        WHERE ac.id_alumno = :idx");
                $stmt->execute(array(":idx" => $_GET['id_alumno']));
                $result = $stmt->fetchAll();
                foreach( $result as $row ) {
                    echo "<li class='list-group-item'>".$row['anio']." : ".$row['nombre']."</li>";
                }
                ?>
            </ul>
        </div>
        <input type="hidden" id="id_alumno" name="id_alumno" value="<?php echo($id);?>">
        <a href="index.php" class="btn btn-secondary">Regresar</a>
    </form>
</div>
<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0-alpha3/js/bootstrap.min.js"></script>
</body>
</html>
