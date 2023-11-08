<?php
include('config.php');

// Verificar si el usuario es un administrador
session_start();
if (!isset($_SESSION['id']) || $_SESSION['admin'] != 1) {
    // Si no es un administrador, redirigir a otra página o mostrar un mensaje de error
    header("Location: index.php");
    exit();
}

// Obtener el ID del usuario de la URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // Consulta para obtener las ofertas de alquiler del usuario
    $query = "SELECT id, titulo, ubicacion FROM alquileres WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
} else {
    // Si no se proporciona un ID de usuario válido en la URL, redirigir a otra página
    header("Location: admin_panel.php");
    exit();
}

// Procesar la eliminación de una oferta de alquiler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_oferta'])) {
    $oferta_id = $_POST['eliminar_oferta'];
    
    // Consulta para eliminar la oferta de alquiler
    $query = "DELETE FROM alquileres WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $oferta_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Redirigir de nuevo a la página de ver_ofertas.php
    header("Location: ver_ofertas.php?user_id=" . $user_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ofertas de Alquiler del Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="estilos/estilo.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h1>Ofertas de Alquiler del Usuario</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Ubicación</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['titulo']}</td>";
                    echo "<td>{$row['ubicacion']}</td>";
                    echo "<td>";
                    
                    // Agregar botón para eliminar oferta de alquiler con ventana modal de confirmación
                    echo '<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal' . $row['id'] . '">Eliminar Oferta</button>';
                    
                    // Ventana modal de confirmación
                    echo '<div class="modal fade" id="deleteModal' . $row['id'] . '" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">';
                    echo '<div class="modal-dialog">';
                    echo '<div class="modal-content">';
                    echo '<div class="modal-header">';
                    echo '<h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                    echo '</div>';
                    echo '<div class="modal-body">';
                    echo '¿Estás seguro de que deseas eliminar esta oferta de alquiler?';
                    echo '</div>';
                    echo '<div class="modal-footer">';
                    echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
                    
                    // Formulario para enviar la solicitud de eliminación
                    echo '<form method="post">';
                    echo '<input type="hidden" name="eliminar_oferta" value="' . $row['id'] . '">';
                    echo '<button type="submit" class="btn btn-danger">Eliminar</button>';
                    echo '</form>';
                    
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php include('footer.php'); ?>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
