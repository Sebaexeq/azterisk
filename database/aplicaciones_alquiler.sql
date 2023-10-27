CREATE TABLE aplicaciones_alquiler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    alquiler_id INT NOT NULL,
    fecha_aplicacion DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('pendiente', 'aceptado', 'completado') NOT NULL DEFAULT 'pendiente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (alquiler_id) REFERENCES alquileres(id)
);
