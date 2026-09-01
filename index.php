<?php
$tituloPagina = "Dashboard - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Consultas para Métricas del Dashboard
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

    // Obtener lista de productos con stock bajo para la tabla de alertas
    $stmtAlertas = $pdo->query("
        SELECT p.*, c.nombre AS categoria_nombre 
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE p.estado = 1 AND p.stock_actual < p.stock_minimo
        ORDER BY (p.stock_minimo - p.stock_actual) DESC
    ");
    $productosBajoStock = $stmtAlertas->fetchAll();

    // Obtener los 5 últimos movimientos
    $stmtUltimosMov = $pdo->query("
        SELECT m.*, p.codigo AS producto_codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
        FROM movimientos m
        INNER JOIN productos p ON m.id_producto = p.id_producto
        INNER JOIN usuarios u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC
        LIMIT 5
    ");
    $ultimosMovimientos = $stmtUltimosMov->fetchAll();

    // Obtener todos los productos activos para el modal de movimiento rápido
    $stmtAllProd = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM productos WHERE estado = 1 ORDER BY nombre ASC");
    $todosProductos = $stmtAllProd->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar datos del dashboard: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-0">Dashboard Principal</h2>
        <p class="text-muted small mb-0">Resumen operativo y control de alerta de inventario TecnoStock.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalMovimientoRapido">
            <i class="fa-solid fa-plus-circle me-1"></i> Registrar Movimiento
        </button>
    </div>
</div>

<!-- Tarjetas Métricas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-primary border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">TOTAL PRODUCTOS</span>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalProductos); ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-box fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">UNIDADES EN STOCK</span>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalStock); ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="fa-solid fa-cubes fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-danger border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-danger small fw-bold">ALERTAS STOCK BAJO</span>
                        <h3 class="fw-bold mb-0 text-danger"><?php echo number_format($totalBajoStock); ?></h3>
                    </div>
                    <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                        <i class="fa-solid fa-triangle-exclamation fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white border-start border-info border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold">MOVIMIENTOS REGISTRADOS</span>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($totalMovimientos); ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="fa-solid fa-arrow-right-arrow-left fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Banner de Alerta Visual de Bajo Stock -->
<?php if ($totalBajoStock > 0): ?>
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center gap-3 p-3 mb-4 rounded-3">
        <i class="fa-solid fa-triangle-exclamation fa-2x text-danger flex-shrink-0"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">¡Alerta Critica de Reabastecimiento!</h5>
            <p class="mb-0 small">
                Se detectaron <strong><?php echo $totalBajoStock; ?> productos</strong> con existencias por debajo del límite mínimo definido. Revisa la tabla a continuación para generar órdenes de compra o entradas de existencias.
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success shadow-sm border-0 d-flex align-items-center gap-3 p-3 mb-4 rounded-3">
        <i class="fa-solid fa-circle-check fa-2x text-success flex-shrink-0"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Inventario en Niveles Óptimos</h5>
            <p class="mb-0 small">
                Todos los productos activos cuentan con un nivel de stock igual o superior a su mínimo requerido.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Tabla: Productos con Stock Bajo -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Productos con Stock Bajo
                </h5>
                <span class="badge bg-danger rounded-pill"><?php echo count($productosBajoStock); ?> ítems</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Stock Actual</th>
                                <th class="text-center">Stock Mínimo</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productosBajoStock)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-check-circle text-success me-1"></i> No hay productos en nivel crítico.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productosBajoStock as $p): ?>
                                    <tr>
                                        <td><code class="fw-bold"><?php echo htmlspecialchars($p['codigo']); ?></code></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['categoria_nombre']); ?></span></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger fs-6 px-2 py-1">
                                                <?php echo $p['stock_actual']; ?>
                                            </span>
                                        </td>
                                        <td class="text-center text-muted fw-bold"><?php echo $p['stock_minimo']; ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill btn-quick-entrada" 
                                                    data-id="<?php echo $p['id_producto']; ?>" 
                                                    data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#modalMovimientoRapido">
                                                <i class="fa-solid fa-plus me-1"></i> Entrar Stock
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
    </div>

    <!-- Tabla: Últimos Movimientos -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Últimos Movimientos
                </h5>
                <a href="movimientos.php" class="btn btn-link btn-sm text-decoration-none">Ver todos &raquo;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimosMovimientos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No hay movimientos registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ultimosMovimientos as $m): ?>
                                    <tr>
                                        <td class="small text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($m['fecha_hora'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($m['tipo'] === 'ENTRADA'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="fa-solid fa-arrow-down me-1"></i> Entrada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="fa-solid fa-arrow-up me-1"></i> Salida
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small fw-semibold"><?php echo htmlspecialchars($m['producto_nombre']); ?></td>
                                        <td class="text-center fw-bold"><?php echo $m['cantidad']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Movimiento Rápido -->
<div class="modal fade" id="modalMovimientoRapido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-box-archive me-2 text-primary"></i>Registrar Movimiento de Inventario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/movimiento_action.php" method="POST" id="formMovimientoRapido">
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label for="modal_id_producto" class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-bold">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="ENTRADA" checked>
                                <label class="form-check-label text-success fw-bold" for="tipo_entrada">
                                    <i class="fa-solid fa-circle-arrow-down me-1"></i> ENTRADA (Ingreso)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_salida" value="SALIDA">
                                <label class="form-check-label text-danger fw-bold" for="tipo_salida">
                                    <i class="fa-solid fa-circle-arrow-up me-1"></i> SALIDA (Egreso)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_cantidad" class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="cantidad" id="modal_cantidad" min="1" required placeholder="Ej: 5">
                    </div>

                    <div class="mb-3">
                        <label for="modal_observacion" class="form-label fw-bold">Observación / Motivo</label>
                        <textarea class="form-control" name="observacion" id="modal_observacion" rows="2" placeholder="Ej: Venta boleta, Ajuste de inventario, Recepción de proveedor"></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
