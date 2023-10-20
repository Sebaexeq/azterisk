<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

$id_usuario_mostrar = $_SESSION["id"];

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id_usuario_mostrar = $_GET["id"];
}

// Incluyendo la conexión a la base de datos
include 'config.php';

// Consulta para obtener la información del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario_mostrar);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if ($usuario) {
    $nombre = $usuario["nombre"];
    $apellido = $usuario["apellido"];
    $foto_perfil = $usuario["foto_perfil"];
    $intereses = $usuario["intereses"];
    $bio = $usuario["bio"];
    $es_admin = $usuario["admin"];
} else {
    // Manejar el caso en que el usuario no se encuentra
    echo "Usuario no encontrado";
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Perfil de Usuario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 15px 15px 0 0;
        }
        .img-fluid {
            border: 3px solid #f4f4f4;
        }
        h2, h4, h5 {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <!-- Tarjeta del lado izquierdo -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil" class="img-fluid rounded-circle mb-3" width="150">
                        <h4><?php echo $nombre . ' ' . $apellido; ?></h4>
                        <?php 
                        echo (isset($_SESSION["verificado"]) && $_SESSION["verificado"] == 1) ? '<img height=40 width=40 src="verified.png" title="Usuario verificado">' : ''; 
                        echo ($es_admin == 1) ? ' <img height=40 width=40 src="admin.png" title="Administrador">' : ''; 
                        ?>
                    </div>
                </div>
            </div>
            <!-- Tarjeta del lado derecho -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2>Perfil de Usuario</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5>Intereses</h5>
                            <p><?php echo $intereses; ?></p>
                        </div>
                        <div>
                            <h5>Biografía</h5>
                            <p><?php echo $bio; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once('footer.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
