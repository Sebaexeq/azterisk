<?php
require_once 'config.php';

session_start();

if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

$mensaje = "";
$mensaje_clase = "";

$usuario_id = $_SESSION["id"];

$sql = "SELECT nombre, apellido, email, foto_perfil, bio, intereses FROM usuarios WHERE id = ?";
if ($stmt = mysqli_prepare($conexion, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $nombre_actual, $apellido_actual, $email_actual, $foto_perfil_actual, $bio_actual, $intereses_actual);
            mysqli_stmt_fetch($stmt);
        }
    }
    mysqli_stmt_close($stmt);
}

function uploadProfilePicture($file) {
    $directory = "galeria/";
    $filename = basename($file["name"]);
    $file_path = $directory . uniqid() . '-' . $filename;
    $file_type = strtolower(pathinfo($file_path,PATHINFO_EXTENSION));

    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }

    if (file_exists($file_path)) {
        return false;
    }

    if ($file["size"] > 500000) {
        return false;
    }

    if($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "gif" ) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $file_path)) {
        return $file_path;
    } else {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_nuevo = $_POST["nombre"];
    $apellido_nuevo = $_POST["apellido"];
    $email_nuevo = $_POST["email"];
    $bio_nueva = $_POST["bio"];
    $intereses_nuevos = $_POST["intereses"];
    
    $contraseña_nueva = $_POST["contraseña"];
    $confirmar_contraseña = $_POST["confirmar_contraseña"];

    // Verifica si se proporcionó una nueva contraseña y si coincide con la confirmación.
    if (!empty($contraseña_nueva) && $contraseña_nueva == $confirmar_contraseña) {
        $contraseña_encriptada = password_hash($contraseña_nueva, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, contraseña = ?, bio = ?, intereses = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $nombre_nuevo, $apellido_nuevo, $email_nuevo, $contraseña_encriptada, $bio_nueva, $intereses_nuevos, $usuario_id);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Información de perfil actualizada exitosamente.";
                $mensaje_clase = "success";

                $_SESSION["nombre"] = $nombre_nuevo;
                $_SESSION["apellido"] = $apellido_nuevo;
                $_SESSION["email"] = $email_nuevo;
            } else {
                $mensaje = "Hubo un problema al actualizar la información de perfil. Inténtalo nuevamente.";
                $mensaje_clase = "error";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (empty($contraseña_nueva) && empty($confirmar_contraseña)) {
        // Si no se proporciona una nueva contraseña y no se confirma, no actualices la contraseña.
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, bio = ?, intereses = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $nombre_nuevo, $apellido_nuevo, $email_nuevo, $bio_nueva, $intereses_nuevos, $usuario_id);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Información de perfil actualizada exitosamente.";
                $mensaje_clase = "success";

                $_SESSION["nombre"] = $nombre_nuevo;
                $_SESSION["apellido"] = $apellido_nuevo;
                $_SESSION["email"] = $email_nuevo;
            } else {
                $mensaje = "Hubo un problema al actualizar la información de perfil. Inténtalo nuevamente.";
                $mensaje_clase = "error";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $mensaje = "Las contraseñas no coinciden.";
        $mensaje_clase = "error";
    }

    // Procesamiento de la imagen de perfil si se proporciona una.
    if ($_FILES["foto_perfil"]["error"] == UPLOAD_ERR_OK) {
        $new_picture_path = uploadProfilePicture($_FILES["foto_perfil"]);
        if ($new_picture_path) {
            $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $new_picture_path, $usuario_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } else {
            $mensaje = "Hubo un problema al subir la foto de perfil. Asegúrate de que sea una imagen válida.";
            $mensaje_clase = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Perfil</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <link href="estilos/estilo.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">Editar Perfil</h1>
        <?php
        if (!empty($mensaje)) {
            echo '<div class="alert alert-' . $mensaje_clase . '">' . $mensaje . '</div>';
        }
        ?>
        <form action="editar_perfil.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre_actual; ?>">
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo $apellido_actual; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email_actual; ?>">
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Biografía</label>
                <textarea class="form-control" id="bio" name="bio"><?php echo $bio_actual; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="intereses" class="form-label">Intereses</label>
                <input type="text" class="form-control" id="intereses" name="intereses" value="<?php echo $intereses_actual; ?>">
            </div>
            <div class="mb-3">
                <label for="foto_perfil" class="form-label">Foto de perfil</label>
                <input type="file" class="form-control" id="foto_perfil" name="foto_perfil">
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña">
            </div>
            <div class="mb-3">
                <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="confirmar_contraseña" name="confirmar_contraseña">
            </div>
			<div class="text-center">
                <button type="submit" class="btn btn-primary">Actualizar</button>
			</div>
        </form>
    </div>
    <?php require_once('footer.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
