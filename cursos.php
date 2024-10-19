<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    die(json_encode(array("error" => "ACCESS DENIED")));
}

// Verificar que el parámetro 'term' esté presente y no vacío
if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
    die(json_encode(array("error" => "Parametro no especificado o vacío")));
}

require_once "db.php";

try {
    // Establecer el encabezado para JSON
    header('Content-Type: application/json; charset=utf-8');

    // Preparar la consulta SQL con un límite de resultados
    $stmt = $pdo->prepare('SELECT nombre FROM curso WHERE nombre LIKE :texto LIMIT 20');
    
    // Ejecutar la consulta con el parámetro 'term'
    $stmt->execute(array(':texto' => $_GET['term'] . "%"));

    // Recoger los resultados en un array
    $retval = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $retval[] = $row['nombre'];
    }

    // Devolver los resultados como JSON con formato bonito
    echo json_encode($retval, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // Manejar errores de la base de datos u otros errores
    echo json_encode(array("error" => "Error en la consulta: " . $e->getMessage()));
}
?>
