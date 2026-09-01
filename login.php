<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está autenticado, redirigir al Dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-boxes-stacked fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Tecno<span class="text-primary">Stock</span></h3>
                        <p class="text-muted small">Sistema de Control de Inventario</p>
                    </div>

                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <?php 
                                echo htmlspecialchars($_SESSION['login_error']); 
                                unset($_SESSION['login_error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="actions/login_action.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control" id="correo" name="correo" value="admin@tecnostock.cl" required placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="clave" class="form-label fw-bold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="clave" name="clave" value="admin123" required placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
                        </button>
                    </form>

                    <div class="mt-4 p-3 bg-light rounded-3 text-muted small text-center">
                        <i class="fa-solid fa-info-circle me-1"></i> Demo Access: <strong>admin@tecnostock.cl</strong> / <strong>admin123</strong>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
