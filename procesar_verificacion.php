<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION["id"];

    // Verificar que los archivos estén presentes y no estén vacíos
    if (
        !isset($_FILES["dni_frente"]) || $_FILES["dni_frente"]["size"] == 0 ||
        !isset($_FILES["dni_dorso"]) || $_FILES["dni_dorso"]["size"] == 0 ||
        !isset($_FILES["selfie"]) || $_FILES["selfie"]["size"] == 0
    ) {
        header("location: perfil.php?error=Por favor, selecciona los 3 archivos.");
        exit;
    }

    // Verificar tipos de archivo (en este ejemplo solo permitimos jpeg, jpg y png)
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    foreach (['dni_frente', 'dni_dorso', 'selfie'] as $filename) {
        if (!in_array($_FILES[$filename]["type"], $allowedTypes)) {
            header("location: perfil.php?error=Formato de archivo no permitido.");
            exit;
        }
    }

    // Procesar archivos
    $dni_frente = $_FILES["dni_frente"]["name"];
    $dni_dorso = $_FILES["dni_dorso"]["name"];
    $selfie = $_FILES["selfie"]["name"];

    // Mover los archivos a una carpeta del servidor (ej. uploads/)
    move_uploaded_file($_FILES["dni_frente"]["tmp_name"], "uploads/" . $dni_frente);
    move_uploaded_file($_FILES["dni_dorso"]["tmp_name"], "uploads/" . $dni_dorso);
    move_uploaded_file($_FILES["selfie"]["tmp_name"], "uploads/" . $selfie);

    // Guardar las rutas en la base de datos
    $sql = "INSERT INTO verificaciones (usuario_id, dni_frente, dni_dorso, selfie) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isss", $usuario_id, $dni_frente, $dni_dorso, $selfie);
    $stmt->execute();

    if ($stmt->affected_rows == 1) {
        header("location: perfil.php");
    } else {
        header("location: perfil.php?error=Hubo un error al enviar los documentos.");
    }
}
?>
