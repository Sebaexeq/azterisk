<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_resena = $_POST['id_resena'];
    $id_usuario = $_POST['id_usuario'];
    $respuesta = $_POST['respuesta'];

    // Verificar si ya existe una respuesta para esta reseña por parte del usuario
    $checkQuery = "SELECT * FROM respuestas WHERE id_resena = ? AND id_usuario = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $id_resena, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Ya has respondido a esta reseña.";
    } else {
        $query = "INSERT INTO respuestas (id_resena, id_usuario, respuesta) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $id_resena, $id_usuario, $respuesta);
        if ($stmt->execute()) {
            echo "Respuesta enviada con éxito.";
        } else {
            echo "Error al enviar la respuesta.";
        }
    }
    $stmt->close();
    $conn->close();
}
?>
1