<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
checkAuth();
$usuario = getUsuarioActual();
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina ?? 'TecnoStock - Inventario Minimalista'; ?></title>
    <!-- Script de Inicialización de Tema para Evitar Parpadeo Flash -->
    <script>
        (function() {
            var theme = localStorage.getItem('tecnostock_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Minimalist Custom CSS (v5.0 Button Hovers Cache Buster) -->
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body>

<!-- Navbar Minimalista -->
<nav class="navbar navbar-expand-lg navbar-light navbar-minimal sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <div class="bg-dark text-white rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fa-solid fa-box-archive text-white" style="font-size: 0.9rem;"></i>
            </div>
            <span>Tecno<span class="text-primary fw-bold">Stock</span></span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fa-solid fa-chart-pie me-1 text-muted"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'productos.php') ? 'active' : ''; ?>" href="productos.php">
                        <i class="fa-solid fa-box me-1 text-muted"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'movimientos.php') ? 'active' : ''; ?>" href="movimientos.php">
                        <i class="fa-solid fa-arrow-right-arrow-left me-1 text-muted"></i> Kardex Movimientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'categorias.php') ? 'active' : ''; ?>" href="categorias.php">
                        <i class="fa-solid fa-layer-group me-1 text-muted"></i> Categorías
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <!-- Botón de Alternar Modo Oscuro -->
                <button type="button" id="themeToggleBtn" class="btn btn-minimal-outline btn-sm px-2.5 py-1.5 rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="Cambiar Tema (Modo Claro / Modo Oscuro)">
                    <i class="fa-solid fa-moon"></i>
                </button>

                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold text-dark small" style="line-height: 1.2;"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                </div>

                <a href="logout.php" class="btn btn-minimal-outline btn-sm px-3" title="Cerrar Sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket me-1 text-danger"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Mensajes Flotantes de Sesión -->
<div class="container mt-3">
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-minimal-<?php echo ($_SESSION['tipo_mensaje'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show p-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid <?php echo ($_SESSION['tipo_mensaje'] == 'success') ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger'; ?>"></i>
                <span class="small fw-medium">
                    <?php 
                        echo htmlspecialchars($_SESSION['mensaje']); 
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    ?>
                </span>
            </div>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<main class="container-fluid px-4 py-4">
