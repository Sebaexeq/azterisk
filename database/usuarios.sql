CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    intereses TEXT,
    foto_perfil VARCHAR(255),
    verificado BOOLEAN DEFAULT 0,
    fecha_verificacion DATE,
    bio TEXT,
    admin BOOLEAN DEFAULT 0,
	pregunta_seguridad VARCHAR(255),
	respuesta_seguridad VARCHAR(255)
);
