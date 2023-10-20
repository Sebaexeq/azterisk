<!DOCTYPE html>
<html>
<head>
    <title>RapiBnB</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <style>
        /* Nueva Paleta de Colores */
        .navbar {
            background-color: #102C57; /* Color más oscuro de la paleta */
        }
        .navbar-brand, .nav-link {
            color: #F8F0E5; /* Color más claro de la paleta */
        }
        .nav-link:hover {
            color: #DAC0A3; /* Tercer color de la paleta */
        }
        .navbar-toggler-icon {
            filter: invert(1); /* Cambia el color del icono del toggler a blanco para que sea visible en el fondo oscuro */
        }
    </style>
</head>
<body>
    <header>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php"><img class="navbar-brand" src="logo.png" href="index.php"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </ul>
                    <ul class="navbar-nav">
                        <!-- Botón de búsqueda siempre visible -->
                        <li class="nav-item">
                            <a class="nav-link" href="buscador.php">
                                <i class="bi bi-search"></i> Buscar Oferta
                            </a>
                        </li>
                        <?php
                        // Verificar si la sesión está activa
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start(); // Inicia la sesión si no está activa
                        }

                        // Botón "Crear Ofertas de Alquiler" con icono de "+"
                        if (isset($_SESSION["id"])) {
                            echo '<li class="nav-item"><a class="nav-link" href="crear_oferta.php"><i class="bi bi-plus"></i> Crear Oferta</a></li>';
                        }
                        ?>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <?php
                        // Botón "Admin" con icono de una tuerca
                        if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
                            echo '<li class="nav-item"><a class="nav-link" href="admin_panel.php"><i class="bi bi-gear"></i> Panel de Administración</a></li>';
                        }

                        // Botón de usuario (person) en el lado derecho
                        if (isset($_SESSION["id"])) {
                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-fill"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="perfil.php">Mi Perfil</a>
                                    <a class="dropdown-item" href="editar_perfil.php">Editar Perfil</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                                </div>
                            </li>
                        <?php
                        } else {
                            echo '<li class="nav-item"><a class="nav-link" href="login.php">Iniciar sesión</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="registro.php">Registrarse</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Navbar -->
    </header>
	
</body>
</html>
