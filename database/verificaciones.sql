CREATE TABLE verificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    dni_frente VARCHAR(255) NOT NULL,
    dni_dorso VARCHAR(255) NOT NULL,
    selfie VARCHAR(255) NOT NULL,
    fecha_solicitud DATE NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
