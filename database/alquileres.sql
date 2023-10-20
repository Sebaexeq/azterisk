CREATE TABLE alquileres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- ID del usuario que crea la oferta de alquiler
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    etiquetas TEXT, -- Pueden ser múltiples etiquetas separadas por comas
    galeria_fotos TEXT, -- Almacena rutas a las imágenes en la galería
    servicios TEXT, -- Almacena una lista de servicios en formato JSON o similar
    costo_alquiler DECIMAL(10, 2) NOT NULL,
    tiempo_minimo INT NOT NULL,
    tiempo_maximo INT NOT NULL,
    cupo INT NOT NULL,
    fecha_inicio DATE, -- Fecha de inicio opcional
    fecha_fin DATE, -- Fecha de fin opcional
    activa BOOLEAN DEFAULT 0, -- Indica si la oferta está activa o no
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de publicación
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
);
