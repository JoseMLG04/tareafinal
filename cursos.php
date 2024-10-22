<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    die(json_encode(array("error" => "ACCESS DENIED")));
}
if (!isset($_GET['term']) || empty(trim($_GET['term']))) {
    die(json_encode(array("error" => "Parametro no especificado o vacío")));
}

require_once "db.php";

try {
    header('Content-Type: application/json; charset=utf-8');
    $stmt = $pdo->prepare('SELECT nombre FROM curso WHERE nombre LIKE :texto LIMIT 20');
    $stmt->execute(array(':texto' => $_GET['term'] . "%"));
    $retval = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $retval[] = $row['nombre'];
    }
    echo json_encode($retval, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(array("error" => "Error en la consulta: " . $e->getMessage()));
}
?>
