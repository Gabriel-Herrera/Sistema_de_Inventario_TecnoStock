<?php
/**
 * Control de Autenticación y Sesiones
 * TecnoStock
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getUsuarioActual() {
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['usuario_nombre'] ?? 'Usuario',
        'correo' => $_SESSION['usuario_correo'] ?? ''
    ];
}
