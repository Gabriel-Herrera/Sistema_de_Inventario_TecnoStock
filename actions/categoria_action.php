<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $estado       = intval($_POST['estado'] ?? 1);

    if (empty($nombre)) {
        $_SESSION['mensaje'] = "El nombre de la categoría es obligatorio.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: ../categorias.php');
        exit;
    }

    try {
        if ($action === 'create') {
            // Verificar nombre único
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre");
            $stmtCheck->execute([':nombre' => $nombre]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['mensaje'] = "La categoría '$nombre' ya existe.";
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ../categorias.php');
                exit;
            }

            $stmtIns = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
            $stmtIns->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);

            $_SESSION['mensaje'] = "Categoría '$nombre' registrada exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";

        } elseif ($action === 'update') {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre AND id_categoria != :id");
            $stmtCheck->execute([':nombre' => $nombre, ':id' => $id_categoria]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['mensaje'] = "El nombre de categoría '$nombre' ya está registrado.";
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ../categorias.php');
                exit;
            }

            $stmtUpd = $pdo->prepare("UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, estado = :estado WHERE id_categoria = :id");
            $stmtUpd->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':estado' => $estado, ':id' => $id_categoria]);

            $_SESSION['mensaje'] = "Categoría '$nombre' actualizada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error en base de datos: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }

    header('Location: ../categorias.php');
    exit;
}

header('Location: ../categorias.php');
exit;
