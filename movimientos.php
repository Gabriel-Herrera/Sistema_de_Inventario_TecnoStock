<?php
$tituloPagina = "Kardex de Movimientos - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Filtros
$tipoFiltro     = $_GET['tipo'] ?? 'ALL';
$productoFiltro = intval($_GET['producto'] ?? 0);

try {
    // Cargar lista de productos activos para los filtros y modal
    $stmtProds = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM productos WHERE estado = 1 ORDER BY nombre ASC");
    $todosProductos = $stmtProds->fetchAll();

    // Consulta de Movimientos
    $sql = "
        SELECT m.*, p.codigo AS producto_codigo, p.nombre AS producto_nombre, p.stock_actual AS producto_stock_actual,
               u.nombre AS usuario_nombre
        FROM movimientos m
        INNER JOIN productos p ON m.id_producto = p.id_producto
        INNER JOIN usuarios u ON m.id_usuario = u.id_usuario
        WHERE 1=1
    ";
    $params = [];

    if ($tipoFiltro === 'ENTRADA' || $tipoFiltro === 'SALIDA') {
        $sql .= " AND m.tipo = :tipo";
        $params[':tipo'] = $tipoFiltro;
    }

    if ($productoFiltro > 0) {
        $sql .= " AND m.id_producto = :producto";
        $params[':producto'] = $productoFiltro;
    }

    $sql .= " ORDER BY m.fecha_hora DESC";

    $stmtMov = $pdo->prepare($sql);
    $stmtMov->execute($params);
    $movimientos = $stmtMov->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar historial de movimientos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h3 fw-bold text-dark mb-0">Control de Entradas y Salidas</h2>
        <p class="text-muted small mb-0">Registro atómico de Kardex y actualización automática del stock de existencias.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoMovimiento">
        <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Registrar Entrada / Salida
    </button>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="movimientos.php" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <select name="producto" class="form-select">
                    <option value="0">-- Todos los Productos --</option>
                    <?php foreach ($todosProductos as $tp): ?>
                        <option value="<?php echo $tp['id_producto']; ?>" <?php echo ($productoFiltro == $tp['id_producto']) ? 'selected' : ''; ?>>
                            [<?php echo htmlspecialchars($tp['codigo']); ?>] <?php echo htmlspecialchars($tp['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-4">
                <select name="tipo" class="form-select">
                    <option value="ALL" <?php echo ($tipoFiltro === 'ALL') ? 'selected' : ''; ?>>Todos los Tipos (Entradas y Salidas)</option>
                    <option value="ENTRADA" <?php echo ($tipoFiltro === 'ENTRADA') ? 'selected' : ''; ?>>Solo Entradas (Ingresos)</option>
                    <option value="SALIDA" <?php echo ($tipoFiltro === 'SALIDA') ? 'selected' : ''; ?>>Solo Salidas (Egresos)</option>
                </select>
            </div>

            <div class="col-6 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filtrar</button>
                <a href="movimientos.php" class="btn btn-outline-secondary rounded-3" title="Limpiar Filtros">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Kardex de Movimientos -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha y Hora</th>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th>Usuario Responsable</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                No se encontraron registros de movimientos.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td><span class="text-muted small">#<?php echo $m['id_movimiento']; ?></span></td>
                                <td class="fw-semibold">
                                    <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                    <?php echo date('d/m/Y H:i:s', strtotime($m['fecha_hora'])); ?>
                                </td>
                                <td>
                                    <?php if ($m['tipo'] === 'ENTRADA'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">
                                            <i class="fa-solid fa-arrow-down-long me-1"></i> ENTRADA
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-6">
                                            <i class="fa-solid fa-arrow-up-long me-1"></i> SALIDA
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($m['producto_codigo']); ?></code></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($m['producto_nombre']); ?></td>
                                <td class="text-center fw-bold fs-6">
                                    <?php echo ($m['tipo'] === 'ENTRADA') ? "+{$m['cantidad']}" : "-{$m['cantidad']}"; ?>
                                </td>
                                <td>
                                    <span class="small text-dark">
                                        <i class="fa-solid fa-user me-1 text-secondary"></i>
                                        <?php echo htmlspecialchars($m['usuario_nombre']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?php echo !empty($m['observacion']) ? htmlspecialchars($m['observacion']) : '<em>Sin observación</em>'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Nuevo Movimiento -->
<div class="modal fade" id="modalNuevoMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-boxes-packing me-2 text-primary"></i>Registrar Entrada / Salida de Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/movimiento_action.php" method="POST" id="formMovimiento">
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label for="mov_id_producto" class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                        <select name="id_producto" id="mov_id_producto" class="form-select" required onchange="actualizarInfoStock(this)">
                            <option value="" data-stock="0">-- Seleccionar Producto --</option>
                            <?php foreach ($todosProductos as $tp): ?>
                                <option value="<?php echo $tp['id_producto']; ?>" data-stock="<?php echo $tp['stock_actual']; ?>">
                                    [<?php echo htmlspecialchars($tp['codigo']); ?>] <?php echo htmlspecialchars($tp['nombre']); ?> (Stock: <?php echo $tp['stock_actual']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="infoStockDisponible">Stock disponible actualmente: <strong>-</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Operación <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="mov_tipo_entrada" value="ENTRADA" checked>
                                <label class="form-check-label text-success fw-bold" for="mov_tipo_entrada">
                                    <i class="fa-solid fa-arrow-down me-1"></i> ENTRADA (Reabastecimiento)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="mov_tipo_salida" value="SALIDA">
                                <label class="form-check-label text-danger fw-bold" for="mov_tipo_salida">
                                    <i class="fa-solid fa-arrow-up me-1"></i> SALIDA (Venta / Egreso)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mov_cantidad" class="form-label fw-bold">Cantidad de Unidades <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="cantidad" id="mov_cantidad" min="1" required placeholder="Ej: 10">
                    </div>

                    <div class="mb-3">
                        <label for="mov_observacion" class="form-label fw-bold">Observación / Justificación</label>
                        <textarea class="form-control" name="observacion" id="mov_observacion" rows="3" placeholder="Ej: Guía de despacho N° 4589, Venta cliente directo..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
