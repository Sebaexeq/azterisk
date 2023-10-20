CREATE TABLE resenia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_oferta INT,
    id_usuario INT,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5), -- Asumiendo que la puntuación va de 1 a 5
    comentario TEXT,
    fecha_creacion DATE NOT NULL,
    FOREIGN KEY (id_oferta) REFERENCES alquileres(id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
