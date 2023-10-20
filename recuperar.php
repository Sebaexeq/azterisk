<?php
require_once 'config.php';

$mensaje = "";
$mensaje_clase = "";
$etapa = isset($_POST['etapa']) ? intval($_POST['etapa']) : 1;
$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Etapa actual: " . $etapa); // Punto de depuración

    if ($etapa == 1) {
        if (isset($_POST["email"])) {
            $email = $_POST["email"];
            $sql = "SELECT id, pregunta_seguridad, respuesta_seguridad FROM usuarios WHERE email = ?";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $pregunta_seguridad, $respuesta_db);
                    mysqli_stmt_fetch($stmt);
                    $etapa = 2;
                    $id_usuario = $id;
                } else {
                    $mensaje = "No se encontró ningún usuario con este correo electrónico.";
                    $mensaje_clase = "alert-danger";
                }
            } else {
                $mensaje = "Hubo un problema al buscar el usuario. Inténtalo nuevamente.";
                $mensaje_clase = "alert-danger";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($etapa == 2) {
        $respuesta_seguridad = $_POST["respuesta_seguridad"];
        $nueva_contrasena = $_POST["nueva_contrasena"];

        $sql = "SELECT respuesta_seguridad FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_usuario);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $respuesta_db);
            mysqli_stmt_fetch($stmt);

            if ($respuesta_seguridad === $respuesta_db) {
                $nueva_contrasena_hashed = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET contraseña = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexion, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "si", $nueva_contrasena_hashed, $id_usuario);
                if (mysqli_stmt_execute($stmt_update)) {
                    $mensaje = "Contraseña actualizada con éxito.";
                    $mensaje_clase = "alert-success";
                    $etapa = 3;
                } else {
                    $mensaje = "Hubo un problema al actualizar la contraseña. Inténtalo nuevamente.";
                    $mensaje_clase = "alert-danger";
                }
                mysqli_stmt_close($stmt_update);
            } else {
                $mensaje = "La respuesta de seguridad es incorrecta. Por favor, inténtalo de nuevo.";
                $mensaje_clase = "alert-danger";
            }
        } else {
            $mensaje = "Error al ejecutar la consulta: " . mysqli_stmt_error($stmt);
            $mensaje_clase = "alert-danger";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Recuperar Contraseña</title>
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
                    <div class="card-header">Recuperar Contraseña</div>
                    <div class="card-body">
                        <?php
                        // Mostrar mensaje de éxito o error
                        if (!empty($mensaje)) {
                            echo '<div class="alert ' . $mensaje_clase . '">' . $mensaje . '</div>';
                        }
                        ?>

                        <?php if ($etapa == 1): ?>
                            <!-- Etapa 1: Usuario ingresa el correo electrónico -->
                            <form action="recuperar.php" method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico:</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>

                                <div class="mb-3 text-center mx-auto">
                                    <button type="submit" class="btn btn-primary">Continuar</button>
                                </div>
                            </form>
                        <?php elseif ($etapa == 2): ?>
                            <!-- Etapa 2: Usuario ingresa la respuesta de seguridad y nueva contraseña -->
                            <form action="recuperar.php" method="post">
                                <input type="hidden" name="id" value="<?php echo $id_usuario; ?>">
                                <div class="mb-3">
                                    <label for="pregunta_seguridad" class="form-label">Pregunta de Seguridad:</label>
                                    <p><?php echo $pregunta_seguridad; ?></p>
                                </div>

                                <div class="mb-3">
                                    <label for="respuesta_seguridad" class="form-label">Respuesta de Seguridad:</label>
                                    <input type="text" class="form-control" name="respuesta_seguridad" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                                    <input type="password" class="form-control" name="nueva_contrasena" required>
                                </div>

                                <div class="mb-3 text-center mx-auto">
                                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                                </div>
                            </form>
                        <?php elseif ($etapa == 3): ?>
                            <!-- Etapa 3: Contraseña cambiada con éxito -->
                            <div class="alert alert-success">Contraseña cambiada con éxito. Puedes iniciar sesión ahora.</div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">Ir a la página de inicio de sesión</a>
                            </div>
                        <?php endif; ?>
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
