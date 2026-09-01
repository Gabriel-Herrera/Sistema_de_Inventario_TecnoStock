<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto'] ?? 0);
    $tipo        = strtoupper(trim($_POST['tipo'] ?? ''));
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $observacion = trim($_POST['observacion'] ?? '');
    $id_usuario  = $_SESSION['usuario_id'] ?? 1;

    // 1. Validaciones primarias
    if ($id_producto <= 0 || !in_array($tipo, ['ENTRADA', 'SALIDA']) || $cantidad <= 0) {
        $_SESSION['mensaje'] = "Datos de movimiento inválidos. La cantidad debe ser mayor a cero.";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: ../movimientos.php');
        exit;
    }

    try {
        // 2. Iniciar Transacción Atómica PDO
        $pdo->beginTransaction();

        // Bloquear fila del producto para lectura y actualización segura (FOR UPDATE)
        $stmtLock = $pdo->prepare("SELECT id_producto, nombre, stock_actual, estado FROM productos WHERE id_producto = :id FOR UPDATE");
        $stmtLock->execute([':id' => $id_producto]);
        $producto = $stmtLock->fetch();

        if (!$producto) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = "El producto seleccionado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: ../movimientos.php');
            exit;
        }

        if ((int)$producto['estado'] === 0) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = "No se pueden registrar movimientos en un producto inactivo.";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: ../movimientos.php');
            exit;
        }

        $stockActual = (int)$producto['stock_actual'];

        // 3. Regla de Negocio: Una salida no puede dejar el stock bajo cero
        if ($tipo === 'SALIDA' && $cantidad > $stockActual) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = "Error de regla de negocio: No se puede registrar la salida de $cantidad unidad(es). El stock disponible de '{$producto['nombre']}' es solo de $stockActual unidad(es).";
            $_SESSION['tipo_mensaje'] = "danger";
            header('Location: ../movimientos.php');
            exit;
        }

        // 4. Calcular nuevo stock
        $nuevoStock = ($tipo === 'ENTRADA') ? ($stockActual + $cantidad) : ($stockActual - $cantidad);

        // 5. Actualizar stock actual en la tabla productos
        $stmtUpd = $pdo->prepare("UPDATE productos SET stock_actual = :nuevoStock WHERE id_producto = :id");
        $stmtUpd->execute([':nuevoStock' => $nuevoStock, ':id' => $id_producto]);

        // 6. Registrar el movimiento en el Kardex
        $stmtMov = $pdo->prepare("
            INSERT INTO movimientos (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
            VALUES (NOW(), :tipo, :cantidad, :observacion, :id_producto, :id_usuario)
        ");
        $stmtMov->execute([
            ':tipo'        => $tipo,
            ':cantidad'    => $cantidad,
            ':observacion' => $observacion,
            ':id_producto' => $id_producto,
            ':id_usuario'  => $id_usuario
        ]);

        // 7. Confirmar Transacción
        $pdo->commit();

        $_SESSION['mensaje'] = "Movimiento ($tipo por $cantidad unidades) registrado exitosamente. Nuevo stock de '{$producto['nombre']}': $nuevoStock unidades.";
        $_SESSION['tipo_mensaje'] = "success";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje'] = "Error al procesar el movimiento: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }

    header('Location: ../movimientos.php');
    exit;
}

header('Location: ../movimientos.php');
exit;
