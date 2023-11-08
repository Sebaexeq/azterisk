<?php
require_once 'config.php';
session_start();

// Verificar si el usuario ya ha iniciado sesión, en cuyo caso redirige al perfil
if (isset($_SESSION["id"])) {
    header("location: perfil.php");
    exit;
}

// Variables para almacenar mensajes de error
$mensaje = "";
$mensaje_clase = "";

// Variables para almacenar valores de campos
$nombre_valor = isset($_POST["nombre"]) ? $_POST["nombre"] : "";
$apellido_valor = isset($_POST["apellido"]) ? $_POST["apellido"] : "";
$email_valor = isset($_POST["email"]) ? $_POST["email"] : "";
$intereses_valor = isset($_POST["intereses"]) ? $_POST["intereses"] : "";
$biografia_valor = isset($_POST["biografia"]) ? $_POST["biografia"] : "";

// Procesar el formulario de registro cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];
    $confirmar_contrasena = $_POST["confirmar_contrasena"];
    $intereses = $_POST["intereses"];
    $biografia = $_POST["biografia"];
    $admin = false; // Valor por defecto para la columna admin

    // Verificar que las contraseñas coincidan
    if ($contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden. Por favor, inténtalo de nuevo.";
        $mensaje_clase = "alert-danger";
    } else {
        // Verificar y manejar la carga de la foto de perfil
        $foto_perfil = $_FILES["foto_perfil"];

        // Directorio donde se guardarán las imágenes
        $directorio_destino = "imagenes/";

        // Nombre de archivo generado aleatoriamente
        $nombre_archivo = uniqid() . "_" . basename($foto_perfil["name"]);
        $ruta_archivo = $directorio_destino . $nombre_archivo;

        // Verificar si es una imagen válida
        $tipo_archivo = pathinfo($ruta_archivo, PATHINFO_EXTENSION);
        $permitidos = array("jpg", "jpeg", "png", "gif");

        if (in_array(strtolower($tipo_archivo), $permitidos)) {
            // Mover el archivo cargado al directorio de imágenes
            if (move_uploaded_file($foto_perfil["tmp_name"], $ruta_archivo)) {
                // Insertar nuevos datos de usuario en la base de datos
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (nombre, apellido, email, contraseña, intereses, foto_perfil, bio, admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = mysqli_prepare($conexion, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sssssssb", $nombre, $apellido, $email, $hashed_password, $intereses, $ruta_archivo, $biografia, $admin);
                    if (mysqli_stmt_execute($stmt)) {
                        $mensaje = "Registro exitoso. ¡Inicia sesión ahora!";
                        $mensaje_clase = "alert-success";
                    } else {
                        $mensaje = "Hubo un problema al registrar el usuario. Inténtalo nuevamente.";
                        $mensaje_clase = "alert-danger";
                    }
                }
            } else {
                $mensaje = "Hubo un problema al cargar la foto de perfil.";
                $mensaje_clase = "alert-danger";
            }
        } else {
            $mensaje = "Formato de archivo no válido. Por favor, elige una imagen válida (jpg, jpeg, png o gif).";
            $mensaje_clase = "alert-danger";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
	<link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        /* Estilos para la paleta de colores */
        body {
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
                    <div class="card-header">Registro de Usuario</div>
                    <div class="card-body">
                        <?php
                        // Mostrar mensaje de éxito o error
                        if (!empty($mensaje)) {
                            echo '<div class="alert ' . $mensaje_clase . '">' . $mensaje . '</div>';
                        }
                        ?>

                        <form action="registro.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo $nombre_valor; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido:</label>
                                <input type="text" class="form-control" name="apellido" value="<?php echo $apellido_valor; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico:</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $email_valor; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control" name="contrasena" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña:</label>
                                <input type="password" class="form-control" name="confirmar_contrasena" required>
                            </div>

                            <div class="mb-3">
                                <label for="intereses" class="form-label">Intereses:</label>
                                <textarea class="form-control" name="intereses" rows="3"><?php echo $intereses_valor; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="biografia" class="form-label">Biografía:</label>
                                <textarea class="form-control" name="biografia" rows="5"><?php echo $biografia_valor; ?></textarea>
                            </div>

                            <div class="mb-3 text-center mx-auto">
                                <label for="foto_perfil" class="form-label">Foto de Perfil:</label>
                                <input type="file" class="form-control-file" name="foto_perfil">
                            </div>

                            <div class="mb-3 text-center mx-auto">
                                <button type="submit" class="btn btn-primary">Registrarse</button>
                            </div>
                        </form>
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
