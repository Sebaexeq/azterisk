CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_resena INT NOT NULL, -- ID de la reseña a la que se responde
    id_usuario INT NOT NULL, -- ID del usuario que responde
    respuesta TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_resena) REFERENCES resenia(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
