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
    <title><?php echo $tituloPagina ?? 'TecnoStock - Sistema de Inventario'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<!-- Navbar Principal -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="index.php">
            <i class="fa-solid fa-boxes-stacked fa-lg text-primary"></i>
            <span class="text-white">Tecno<span class="text-primary">Stock</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'index.php') ? 'active fw-bold' : ''; ?>" href="index.php">
                        <i class="fa-solid fa-chart-line me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'productos.php') ? 'active fw-bold' : ''; ?>" href="productos.php">
                        <i class="fa-solid fa-box me-1"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'movimientos.php') ? 'active fw-bold' : ''; ?>" href="movimientos.php">
                        <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Entradas / Salidas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($paginaActual == 'categorias.php') ? 'active fw-bold' : ''; ?>" href="categorias.php">
                        <i class="fa-solid fa-tags me-1"></i> Categorías
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-light small d-none d-md-block text-end">
                    <div class="fw-bold"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Cerrar Sesión">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Mensajes Flotantes de Sesión -->
<div class="container mt-3">
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid <?php echo ($_SESSION['tipo_mensaje'] == 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
            <?php 
                echo htmlspecialchars($_SESSION['mensaje']); 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<main class="container-fluid px-4 py-3">
