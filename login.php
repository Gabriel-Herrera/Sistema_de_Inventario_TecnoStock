<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            
            <div class="card card-minimal border-0 shadow-sm p-4 p-md-5">
                
                <div class="text-center mb-4">
                    <div class="bg-dark text-white rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                        <i class="fa-solid fa-box-archive fa-lg"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Tecno<span class="text-primary">Stock</span></h4>
                    <p class="text-muted small">Sistema de Control de Inventario</p>
                </div>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="alert alert-minimal-danger p-3 mb-4 small" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        <?php 
                            echo htmlspecialchars($_SESSION['login_error']); 
                            unset($_SESSION['login_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="actions/login_action.php" method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="correo" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="admin@tecnostock.cl" required placeholder="correo@ejemplo.com">
                    </div>

                    <div class="mb-4">
                        <label for="clave" class="form-label small fw-semibold text-secondary">Contraseña</label>
                        <input type="password" class="form-control" id="clave" name="clave" value="admin123" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-minimal-primary w-100 py-2.5 rounded-3 fw-semibold">
                        Iniciar Sesión &rarr;
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center text-muted" style="font-size: 0.75rem;">
                    Acceso Demo: <strong class="text-dark">admin@tecnostock.cl</strong> &middot; <strong class="text-dark">admin123</strong>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
