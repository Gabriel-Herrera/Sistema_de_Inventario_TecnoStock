<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'create' || $action === 'update') {
        $id_producto  = intval($_POST['id_producto'] ?? 0);
        $codigo       = strtoupper(trim($_POST['codigo'] ?? ''));
        $nombre       = trim($_POST['nombre'] ?? '');
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        $precio       = floatval($_POST['precio'] ?? 0);
        $stock_actual = intval($_POST['stock_actual'] ?? 0);
        $stock_minimo = intval($_POST['stock_minimo'] ?? 0);
        $descripcion  = trim($_POST['descripcion'] ?? '');

        // Validaciones en Servidor (PHP)
        if (empty($codigo) || empty($nombre) || $id_categoria <= 0) {
            $_SESSION['mensaje'] = "Por favor complete los campos obligatorios (código, nombre y categoría).";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: ../productos.php');
            exit;
        }

        if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
            $_SESSION['mensaje'] = "El precio, stock actual y stock mínimo no pueden ser valores negativos.";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: ../productos.php');
            exit;
        }

        try {
            // Verificar código único
            if ($action === 'create') {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo = :codigo");
                $stmtCheck->execute([':codigo' => $codigo]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $_SESSION['mensaje'] = "El código '$codigo' ya está registrado para otro producto. Debe ser único.";
                    $_SESSION['tipo_mensaje'] = "danger";
                    header('Location: ../productos.php');
                    exit;
                }

                // Insertar Producto
                $stmtIns = $pdo->prepare("
                    INSERT INTO productos (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria)
                    VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, 1, :id_categoria)
                ");
                $stmtIns->execute([
                    ':codigo'       => $codigo,
                    ':nombre'       => $nombre,
                    ':descripcion'  => $descripcion,
                    ':precio'       => $precio,
                    ':stock_actual' => $stock_actual,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria
                ]);

                $_SESSION['mensaje'] = "Producto '$nombre' registrado exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";

            } elseif ($action === 'update') {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo = :codigo AND id_producto != :id");
                $stmtCheck->execute([':codigo' => $codigo, ':id' => $id_producto]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $_SESSION['mensaje'] = "El código '$codigo' ya pertenece a otro producto.";
                    $_SESSION['tipo_mensaje'] = "danger";
                    header('Location: ../productos.php');
                    exit;
                }

                // Actualizar Producto
                $stmtUpd = $pdo->prepare("
                    UPDATE productos 
                    SET codigo = :codigo,
                        nombre = :nombre,
                        descripcion = :descripcion,
                        precio = :precio,
                        stock_actual = :stock_actual,
                        stock_minimo = :stock_minimo,
                        id_categoria = :id_categoria
                    WHERE id_producto = :id
                ");
                $stmtUpd->execute([
                    ':codigo'       => $codigo,
                    ':nombre'       => $nombre,
                    ':descripcion'  => $descripcion,
                    ':precio'       => $precio,
                    ':stock_actual' => $stock_actual,
                    ':stock_minimo' => $stock_minimo,
                    ':id_categoria' => $id_categoria,
                    ':id'           => $id_producto
                ]);

                $_SESSION['mensaje'] = "Producto '$nombre' actualizado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            }

        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error en base de datos: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }

        header('Location: ../productos.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $id_producto  = intval($_POST['id_producto'] ?? 0);
        $nuevo_estado = intval($_POST['nuevo_estado'] ?? 0);

        try {
            // Regla de Negocio: Desactivación Lógica (Soft Delete)
            $stmtToggle = $pdo->prepare("UPDATE productos SET estado = :estado WHERE id_producto = :id");
            $stmtToggle->execute([':estado' => $nuevo_estado, ':id' => $id_producto]);

            $estadoTexto = ($nuevo_estado === 1) ? 'activado' : 'desactivado (soft delete)';
            $_SESSION['mensaje'] = "Estado del producto cambiado exitosamente a $estadoTexto.";
            $_SESSION['tipo_mensaje'] = "info";

        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "Error al cambiar estado del producto: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }

        header('Location: ../productos.php');
        exit;
    }
}

header('Location: ../productos.php');
exit;
