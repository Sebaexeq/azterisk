<?php
// Incluye el archivo de configuración de la base de datos
require_once 'config.php';

// Inicia la sesión de usuario
session_start();

// Verifica si el usuario ya ha iniciado sesión, en cuyo caso redirige al perfil
if (isset($_SESSION["id"])) {
    header("location: perfil.php");
    exit;
}

$error_message = ""; // Inicializar la variable de mensaje de error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera el correo electrónico y la contraseña del formulario
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];

    // Consulta SQL para buscar el usuario por su correo electrónico
    $sql = "SELECT id, nombre, apellido, contraseña, foto_perfil, intereses, bio, admin, verificado FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $nombre, $apellido, $hashed_password, $foto_perfil, $intereses, $bio, $admin, $verificado); // Agregar $verificado
                if (mysqli_stmt_fetch($stmt)) {
                    // Verifica la contraseña
                    if (password_verify($contrasena, $hashed_password)) {
                        // Inicio de sesión exitoso
                        $_SESSION["id"] = $id;
                        $_SESSION["nombre"] = $nombre;
                        $_SESSION["apellido"] = $apellido;
                        $_SESSION["foto_perfil"] = $foto_perfil;
                        $_SESSION["intereses"] = $intereses;
                        $_SESSION["bio"] = $bio;
                        $_SESSION["admin"] = $admin;
                        $_SESSION["verificado"] = $verificado; // Establecer la variable de sesión 'verificado'
                        header("location: perfil.php");
                    } else {
                        $error_message = "Contraseña incorrecta. Intenta de nuevo.";
                    }
                }
            } else {
                $error_message = "El correo electrónico ingresado no está registrado. <a href='registro.php'>Registrarse</a>.";
            }
        } else {
            $error_message = "Error al ejecutar la consulta.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Error de preparación de la consulta.";
    }

    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Iniciar Sesión</title>
    <!-- Agregar los enlaces a los estilos de Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
	<style>
        /* Estilos para la paleta de colores */
        body {
            background-color: #F8F0E5;
            color: #102C57;
        }
        .btn.btn-primary {
            background-color: #DAC0A3;
            border-color: #DAC0A3;
        }
		.card {
        border: none; /* Eliminar el borde por defecto */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra suave */
    }

    .card-title, .card-text {
        color: #102C57; /* Color más oscuro de la paleta */
    }
        .btn.btn-primary:hover, .btn.btn-primary:focus, .btn.btn-primary:active {
            background-color: #EADBC8;
            border-color: #EADBC8;
        }
        .btn.btn-danger {
            background-color: #102C57;
            border-color: #102C57;
        }
        .btn.btn-danger:hover, .btn.btn-danger:focus, .btn.btn-danger:active {
            background-color: #DAC0A3;
            border-color: #DAC0A3;
        }
        .modal-content {
            background-color: #F8F0E5;
        }
        /* Estilo para centrar y separar el botón "Publicar Reseña" */
        .container.mt-4 .form-group button {
            margin-top: 10px; /* Espacio entre el cuadro de texto y el botón "Publicar Reseña" */
        }
        /* Estilo para alinear a la derecha el botón "Eliminar Reseña" */
        .eliminar-resena-button {
            float: right;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Iniciar Sesión</div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control" name="contrasena" required>
                            </div>

                            <div class="text-center">
								<button type="submit" class="btn btn-primary">Iniciar Sesión</button>
							</div>
							<div class="text-center mt-3">
								<a href="recuperar.php">Recuperar Contraseña</a>
							</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir el pie de página -->
    <?php require_once('footer.php'); ?>

    <!-- Agregar los enlaces a los scripts de Bootstrap 5 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
