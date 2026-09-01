<?php
$tituloPagina = "Dashboard - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

try {
    // 1. Total Productos Activos
    $stmtProd = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 1");
    $totalProductos = $stmtProd->fetchColumn();

    // 2. Stock Total en Unidades
    $stmtStock = $pdo->query("SELECT COALESCE(SUM(stock_actual), 0) FROM productos WHERE estado = 1");
    $totalStock = $stmtStock->fetchColumn();

    // 3. Productos en Alerta de Stock Bajo
    $stmtBajoStockCount = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 1 AND stock_actual < stock_minimo");
    $totalBajoStock = $stmtBajoStockCount->fetchColumn();

    // 4. Total Movimientos
    $stmtMovCount = $pdo->query("SELECT COUNT(*) FROM movimientos");
    $totalMovimientos = $stmtMovCount->fetchColumn();

    // Obtener productos con bajo stock
    $stmtAlertas = $pdo->query("
        SELECT p.*, c.nombre AS categoria_nombre 
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE p.estado = 1 AND p.stock_actual < p.stock_minimo
        ORDER BY (p.stock_minimo - p.stock_actual) DESC
    ");
    $productosBajoStock = $stmtAlertas->fetchAll();

    // Obtener 5 últimos movimientos
    $stmtUltimosMov = $pdo->query("
        SELECT m.*, p.codigo AS producto_codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
        FROM movimientos m
        INNER JOIN productos p ON m.id_producto = p.id_producto
        INNER JOIN usuarios u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC
        LIMIT 5
    ");
    $ultimosMovimientos = $stmtUltimosMov->fetchAll();

    // Obtener todos los productos para el modal
    $stmtAllProd = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM productos WHERE estado = 1 ORDER BY nombre ASC");
    $todosProductos = $stmtAllProd->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-minimal-danger p-3 mb-4'>Error al cargar datos del dashboard: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold text-dark mb-0">Dashboard Operativo</h2>
        <p class="text-muted small mb-0">Resumen sintético del estado del inventario y alertas.</p>
    </div>
    <div>
        <button type="button" class="btn btn-minimal-primary" data-bs-toggle="modal" data-bs-target="#modalMovimientoRapido">
            <i class="fa-solid fa-plus me-1.5" style="font-size: 0.8rem;"></i> Movimiento Rápido
        </button>
    </div>
</div>

<!-- Tarjetas Métricas Minimalistas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-minimal p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="card-metric-title mb-1">Total Productos</div>
                    <div class="card-metric-value"><?php echo number_format($totalProductos); ?></div>
                </div>
                <div class="icon-box-soft bg-light text-primary">
                    <i class="fa-solid fa-box"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-minimal p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="card-metric-title mb-1">Unidades en Stock</div>
                    <div class="card-metric-value"><?php echo number_format($totalStock); ?></div>
                </div>
                <div class="icon-box-soft bg-light text-success">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-minimal p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="card-metric-title mb-1 text-danger">Stock Bajo (Alerta)</div>
                    <div class="card-metric-value text-danger"><?php echo number_format($totalBajoStock); ?></div>
                </div>
                <div class="icon-box-soft style-danger-soft text-danger" style="background-color: var(--accent-danger-soft);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-minimal p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="card-metric-title mb-1">Movimientos</div>
                    <div class="card-metric-value"><?php echo number_format($totalMovimientos); ?></div>
                </div>
                <div class="icon-box-soft bg-light text-secondary">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Banner Soft Minimalista de Alerta -->
<?php if ($totalBajoStock > 0): ?>
    <div class="alert alert-minimal-danger p-3.5 mb-4 d-flex align-items-center gap-3">
        <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
        <div class="small">
            <strong class="d-block fw-semibold mb-0.5">Atención: Productos con Stock Mínimo Crítico</strong>
            Hay <strong><?php echo $totalBajoStock; ?> producto(s)</strong> cuyo stock actual es inferior al stock mínimo requerido. Revisa el catálogo para reabastecer existencias.
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Tabla: Productos en Alerta -->
    <div class="col-12 col-lg-7">
        <div class="card card-minimal overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="fw-semibold text-dark small">
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Productos con Stock Bajo
                </div>
                <span class="badge badge-soft-danger"><?php echo count($productosBajoStock); ?> en riesgo</span>
            </div>
            <div class="table-responsive">
                <table class="table table-minimal">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-center">Stock / Mínimo</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productosBajoStock)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    <i class="fa-solid fa-check text-success me-1"></i> No hay productos en nivel crítico.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productosBajoStock as $p): ?>
                                <tr>
                                    <td><code class="fw-medium text-dark"><?php echo htmlspecialchars($p['codigo']); ?></code></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                        <div class="text-muted small" style="font-size: 0.75rem;"><?php echo htmlspecialchars($p['categoria_nombre']); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-soft-danger me-1"><?php echo $p['stock_actual']; ?></span>
                                        <span class="text-muted small">/ <?php echo $p['stock_minimo']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-minimal-outline btn-sm py-1 px-2.5 btn-quick-entrada" 
                                                data-id="<?php echo $p['id_producto']; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#modalMovimientoRapido">
                                            <i class="fa-solid fa-plus me-1 text-success"></i> Entrar Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla: Últimos Movimientos -->
    <div class="col-12 col-lg-5">
        <div class="card card-minimal overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="fw-semibold text-dark small">
                    <i class="fa-solid fa-history me-2 text-muted"></i> Últimos Movimientos
                </div>
                <a href="movimientos.php" class="text-primary text-decoration-none small fw-medium">Ver Kardex &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-minimal">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th class="text-end">Cant.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimosMovimientos)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No hay movimientos recientes.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimosMovimientos as $m): ?>
                                <tr>
                                    <td class="text-muted small" style="font-size: 0.75rem;">
                                        <?php echo date('d/m H:i', strtotime($m['fecha_hora'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($m['tipo'] === 'ENTRADA'): ?>
                                            <span class="badge badge-soft-success">Entrada</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-danger">Salida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-medium small text-truncate" style="max-width: 140px;">
                                        <?php echo htmlspecialchars($m['producto_nombre']); ?>
                                    </td>
                                    <td class="text-end fw-semibold small">
                                        <?php echo ($m['tipo'] === 'ENTRADA') ? "+{$m['cantidad']}" : "-{$m['cantidad']}"; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Movimiento Rápido Minimalista -->
<div class="modal fade modal-minimal" id="modalMovimientoRapido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold text-dark"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Registrar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/movimiento_action.php" method="POST" id="formMovimientoRapido">
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label for="modal_id_producto" class="form-label small fw-semibold text-secondary">Producto</label>
                        <select name="id_producto" id="modal_id_producto" class="form-select" required>
                            <option value="">-- Seleccionar Producto --</option>
                            <?php foreach ($todosProductos as $tp): ?>
                                <option value="<?php echo $tp['id_producto']; ?>">
                                    [<?php echo htmlspecialchars($tp['codigo']); ?>] <?php echo htmlspecialchars($tp['nombre']); ?> (Stock: <?php echo $tp['stock_actual']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary d-block">Tipo de Operación</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="ENTRADA" checked>
                                <label class="form-check-label text-success fw-medium small" for="tipo_entrada">
                                    <i class="fa-solid fa-arrow-down me-1"></i> ENTRADA (Ingreso)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_salida" value="SALIDA">
                                <label class="form-check-label text-danger fw-medium small" for="tipo_salida">
                                    <i class="fa-solid fa-arrow-up me-1"></i> SALIDA (Egreso)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_cantidad" class="form-label small fw-semibold text-secondary">Cantidad</label>
                        <input type="number" class="form-control" name="cantidad" id="modal_cantidad" min="1" required placeholder="Ej: 5">
                    </div>

                    <div class="mb-3">
                        <label for="modal_observacion" class="form-label small fw-semibold text-secondary">Observación</label>
                        <textarea class="form-control" name="observacion" id="modal_observacion" rows="2" placeholder="Motivo o referencia..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-minimal-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-minimal-primary btn-sm px-4">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
