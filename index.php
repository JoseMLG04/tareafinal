<?php
require_once "db.php";
session_start();
?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de estudiantes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
    <div class="container mt-5" >
        <h1 class="mb-4">Lista de estudiantes</h1>

        <?php if (isset($_SESSION['id_usuario'])): ?>
            <p><a class="btn btn-danger" href="logout.php">Salir</a></p>
        <?php else: ?>
            <p><a class="btn btn-primary" href="login.php">Ingresar a sistema</a></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <table class="table table-hover ">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Aficiones</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nombres']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['apellidos']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['aficiones']) . "</td>";
                    echo "<td>";
                    echo '<a class="btn btn-info"    href="ver.php?id_alumno=' . $row['id_alumno'] . '">Ver</a>';
                    if (isset($_SESSION['id_usuario'])) {
                        echo ' <a class="btn btn-secondary" href="edit.php?id_alumno=' . $row['id_alumno'] . '">Editar</a>';
                        echo ' <a class="btn btn-danger" href="delete.php?id_alumno=' . $row['id_alumno'] . '">Borrar</a>';
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <?php if (isset($_SESSION['id_usuario'])): ?>
            <a class="btn btn-success" href="nuevo.php">Agregar alumno</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
