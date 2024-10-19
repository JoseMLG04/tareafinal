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
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}

// Verificar si las claves existen y no son null antes de usar htmlentities
$fn = isset($row['NOMBRES']) ? htmlentities($row['NOMBRES']) : '';
$ln = isset($row['APELLIDOS']) ? htmlentities($row['APELLIDOS']) : '';
$em = isset($row['CORREO']) ? htmlentities($row['CORREO']) : '';
$af = isset($row['AFICIONES']) ? htmlentities($row['AFICIONES']) : '';
$id = $row['ID_ALUMNO'];

?>
<html>
<head>
<title>Consulta de Alumno</title>
</head><body>
<h1>Consulta de Alumno</h1>
<form >
<p>Nombres: <?php echo($fn);?></p>
<p>Apellidos: <?php echo($ln);?></p>
<p>Correo:<?php echo($em);?></p>
<p>Aficiones: <?php echo($af);?></p>
<p>Cursos y certificaciones</p>
<ul>
<?php
    // Corregir nombres de tablas y columnas en la consulta SQL
    $stmt = $pdo->prepare("SELECT ac.anio, c.nombre FROM alumno_curso ac 
                            INNER JOIN curso c 
                            ON c.id_curso = ac.id_curso
                            WHERE ac.id_alumno = :idx");
    $stmt->execute(array(":idx" => $_GET['id_alumno']));
    $result = $stmt->fetchAll();
    foreach( $result as $row ) {
        echo "<li>".$row['anio'] ;
        echo ": ";
        echo $row['nombre'];
        echo "</li>";
    }
?>
</ul>

<input type="hidden" id="id_alumno" name="id_alumno" value="<?php echo($id);?>">
 
<a href="index.php">Regresar</a></p>
</form>
</body>
</html>
