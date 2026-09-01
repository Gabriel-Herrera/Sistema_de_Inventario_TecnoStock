<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = trim($_POST['clave'] ?? '');

    if (empty($correo) || empty($clave)) {
        $_SESSION['login_error'] = 'Por favor complete todos los campos.';
        header('Location: ../login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, correo, clave, estado FROM usuarios WHERE correo = :correo");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && (int)$usuario['estado'] === 1 && password_verify($clave, $usuario['clave'])) {
            $_SESSION['usuario_id']     = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];

            $_SESSION['mensaje'] = "¡Bienvenido/a de nuevo, " . $usuario['nombre'] . "!";
            $_SESSION['tipo_mensaje'] = "success";

            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['login_error'] = 'Credenciales inválidas o cuenta inactiva.';
            header('Location: ../login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        header('Location: ../login.php');
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
