<?php
$tituloPagina = "Kardex de Movimientos - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$tipoFiltro     = $_GET['tipo'] ?? 'ALL';
$productoFiltro = intval($_GET['producto'] ?? 0);

try {
    $stmtProds = $pdo->query("SELECT id_producto, codigo, nombre, stock_actual FROM productos WHERE estado = 1 ORDER BY nombre ASC");
    $todosProductos = $stmtProds->fetchAll();

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
    echo "<div class='alert alert-minimal-danger p-3 mb-4'>Error al cargar historial de movimientos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold text-dark mb-0">Control de Entradas y Salidas</h2>
        <p class="text-muted small mb-0">Registro atómico de Kardex e historial de inventario.</p>
    </div>
    <button type="button" class="btn btn-minimal-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoMovimiento">
        <i class="fa-solid fa-plus me-1.5" style="font-size: 0.8rem;"></i> Registrar Movimiento
    </button>
</div>

<!-- Filtros Minimalistas -->
<div class="card card-minimal mb-4 p-3">
    <form method="GET" action="movimientos.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <select name="producto" class="form-select">
                <option value="0">Todos los Productos</option>
                <?php foreach ($todosProductos as $tp): ?>
                    <option value="<?php echo $tp['id_producto']; ?>" <?php echo ($productoFiltro == $tp['id_producto']) ? 'selected' : ''; ?>>
                        [<?php echo htmlspecialchars($tp['codigo']); ?>] <?php echo htmlspecialchars($tp['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-md-4">
            <select name="tipo" class="form-select">
                <option value="ALL" <?php echo ($tipoFiltro === 'ALL') ? 'selected' : ''; ?>>Todos los Tipos</option>
                <option value="ENTRADA" <?php echo ($tipoFiltro === 'ENTRADA') ? 'selected' : ''; ?>>Solo Entradas</option>
                <option value="SALIDA" <?php echo ($tipoFiltro === 'SALIDA') ? 'selected' : ''; ?>>Solo Salidas</option>
            </select>
        </div>

        <div class="col-6 col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-minimal-primary w-100 py-2">Filtrar</button>
            <a href="movimientos.php" class="btn btn-minimal-outline py-2" title="Limpiar Filtros">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabla Kardex Minimalista -->
<div class="card card-minimal overflow-hidden">
    <div class="table-responsive">
        <table class="table table-minimal">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Tipo</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th>Responsable</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">
                            No se encontraron movimientos de inventario.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><span class="text-muted small" style="font-size: 0.75rem;">#<?php echo $m['id_movimiento']; ?></span></td>
                            <td class="small text-secondary">
                                <?php echo date('d/m/Y H:i', strtotime($m['fecha_hora'])); ?>
                            </td>
                            <td>
                                <?php if ($m['tipo'] === 'ENTRADA'): ?>
                                    <span class="badge badge-soft-success">Entrada</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-danger">Salida</span>
                                <?php endif; ?>
                            </td>
                            <td><code class="fw-medium text-dark"><?php echo htmlspecialchars($m['producto_codigo']); ?></code></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($m['producto_nombre']); ?></td>
                            <td class="text-center fw-semibold small">
                                <?php echo ($m['tipo'] === 'ENTRADA') ? "+{$m['cantidad']}" : "-{$m['cantidad']}"; ?>
                            </td>
                            <td class="small text-secondary">
                                <?php echo htmlspecialchars($m['usuario_nombre']); ?>
                            </td>
                            <td class="small text-muted" style="font-size: 0.75rem;">
                                <?php echo !empty($m['observacion']) ? htmlspecialchars($m['observacion']) : '&mdash;'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Registrar Movimiento Minimalista -->
<div class="modal fade modal-minimal" id="modalNuevoMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold text-dark"><i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Registrar Entrada / Salida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/movimiento_action.php" method="POST" id="formMovimiento">
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label for="mov_id_producto" class="form-label small fw-semibold text-secondary">Producto</label>
                        <select name="id_producto" id="mov_id_producto" class="form-select" required onchange="actualizarInfoStock(this)">
                            <option value="" data-stock="0">-- Seleccionar Producto --</option>
                            <?php foreach ($todosProductos as $tp): ?>
                                <option value="<?php echo $tp['id_producto']; ?>" data-stock="<?php echo $tp['stock_actual']; ?>">
                                    [<?php echo htmlspecialchars($tp['codigo']); ?>] <?php echo htmlspecialchars($tp['nombre']); ?> (Stock: <?php echo $tp['stock_actual']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small" id="infoStockDisponible">Stock disponible actualmente: <strong>-</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary d-block">Tipo de Operación</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="mov_tipo_entrada" value="ENTRADA" checked>
                                <label class="form-check-label text-success fw-medium small" for="mov_tipo_entrada">
                                    <i class="fa-solid fa-arrow-down me-1"></i> ENTRADA (Reabastecimiento)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="mov_tipo_salida" value="SALIDA">
                                <label class="form-check-label text-danger fw-medium small" for="mov_tipo_salida">
                                    <i class="fa-solid fa-arrow-up me-1"></i> SALIDA (Venta / Egreso)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mov_cantidad" class="form-label small fw-semibold text-secondary">Cantidad de Unidades</label>
                        <input type="number" class="form-control" name="cantidad" id="mov_cantidad" min="1" required placeholder="Ej: 10">
                    </div>

                    <div class="mb-3">
                        <label for="mov_observacion" class="form-label small fw-semibold text-secondary">Observación</label>
                        <textarea class="form-control" name="observacion" id="mov_observacion" rows="3" placeholder="Ej: Venta cliente directo, Guía despacho N° 4589..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-minimal-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-minimal-primary btn-sm px-4">Confirmar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
