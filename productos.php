<?php
$tituloPagina = "Gestión de Productos - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Filtros y búsqueda
$busqueda  = trim($_GET['q'] ?? '');
$categoria = intval($_GET['cat'] ?? 0);
$estado    = $_GET['estado'] ?? '1'; // 1 = Activos por defecto, 'all' = Todos, '0' = Inactivos

try {
    // Cargar categorías para el filtro y los modales
    $stmtCat = $pdo->query("SELECT id_categoria, nombre FROM categorias WHERE estado = 1 ORDER BY nombre ASC");
    $categorias = $stmtCat->fetchAll();

    // Construir consulta dinámica con filtros
    $sql = "
        SELECT p.*, c.nombre AS categoria_nombre,
               (SELECT COUNT(*) FROM movimientos m WHERE m.id_producto = p.id_producto) AS total_movimientos
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE 1=1
    ";
    $params = [];

    if ($estado !== 'all') {
        $sql .= " AND p.estado = :estado";
        $params[':estado'] = intval($estado);
    }

    if ($categoria > 0) {
        $sql .= " AND p.id_categoria = :categoria";
        $params[':categoria'] = $categoria;
    }

    if (!empty($busqueda)) {
        $sql .= " AND (p.codigo LIKE :busqueda OR p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)";
        $params[':busqueda'] = "%$busqueda%";
    }

    $sql .= " ORDER BY p.nombre ASC";

    $stmtProd = $pdo->prepare($sql);
    $stmtProd->execute($params);
    $productos = $stmtProd->fetchAll();

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al consultar productos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h3 fw-bold text-dark mb-0">Catálogo de Productos</h2>
        <p class="text-muted small mb-0">Registro, consulta, actualización y desactivación de existencias.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalProducto" onclick="prepararCrearProducto()">
        <i class="fa-solid fa-box-archive me-1"></i> + Nuevo Producto
    </button>
</div>

<!-- Barra de Filtros y Búsqueda -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="productos.php" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar por código, nombre o categoría...">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="cat" class="form-select">
                    <option value="0">-- Todas las Categorías --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="estado" class="form-select">
                    <option value="1" <?php echo ($estado === '1') ? 'selected' : ''; ?>>Solo Activos</option>
                    <option value="0" <?php echo ($estado === '0') ? 'selected' : ''; ?>>Solo Inactivos</option>
                    <option value="all" <?php echo ($estado === 'all') ? 'selected' : ''; ?>>Todos los estados</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filtrar</button>
                <a href="productos.php" class="btn btn-outline-secondary rounded-3" title="Limpiar Filtros">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock Actual</th>
                        <th class="text-center">Stock Mín.</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-2x mb-2 d-block"></i>
                                No se encontraron productos con los criterios especificados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                            <?php 
                                $esBajoStock = ($p['stock_actual'] < $p['stock_minimo']);
                                $trClass = ($p['estado'] == 0) ? 'table-secondary text-muted' : ($esBajoStock ? 'table-danger-subtle' : '');
                            ?>
                            <tr class="<?php echo $trClass; ?>">
                                <td><code class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($p['codigo']); ?></code></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                    <?php if (!empty($p['descripcion'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($p['descripcion']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle">
                                        <?php echo htmlspecialchars($p['categoria_nombre']); ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    $<?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($esBajoStock && $p['estado'] == 1): ?>
                                        <span class="badge bg-danger fs-6 px-2 py-1" title="¡Stock menor al mínimo (<?php echo $p['stock_minimo']; ?>)!">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $p['stock_actual']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success fs-6 px-2 py-1">
                                            <?php echo $p['stock_actual']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted fw-bold"><?php echo $p['stock_minimo']; ?></td>
                                <td class="text-center">
                                    <?php if ($p['estado'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                title="Editar Producto"
                                                onclick='prepararEditarProducto(<?php echo json_encode($p); ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <?php if ($p['estado'] == 1): ?>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    title="Desactivar Producto"
                                                    onclick="confirmarCambioEstado(<?php echo $p['id_producto']; ?>, 0, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>', <?php echo $p['total_movimientos']; ?>)">
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    title="Reactivar Producto"
                                                    onclick="confirmarCambioEstado(<?php echo $p['id_producto']; ?>, 1, '<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>', <?php echo $p['total_movimientos']; ?>)">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar / Editar Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalProductoTitulo"><i class="fa-solid fa-box me-2 text-primary"></i>Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/producto_action.php" method="POST" id="formProducto">
                <input type="hidden" name="action" id="prod_action" value="create">
                <input type="hidden" name="id_producto" id="prod_id_producto" value="">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <div class="col-12 col-md-4">
                            <label for="prod_codigo" class="form-label fw-bold">Código Único <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="codigo" id="prod_codigo" required placeholder="Ej: TEC-007" uppercase>
                            <div class="form-text">No puede repetirse.</div>
                        </div>

                        <div class="col-12 col-md-8">
                            <label for="prod_nombre" class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="prod_nombre" required placeholder="Ej: Monitor LED 24 FHD">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="prod_id_categoria" class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select name="id_categoria" id="prod_id_categoria" class="form-select" required>
                                <option value="">-- Seleccionar Categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="prod_precio" class="form-label fw-bold">Precio Unitario ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" name="precio" id="prod_precio" required placeholder="0.00">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="prod_stock_actual" class="form-label fw-bold">Stock Inicial / Actual <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="stock_actual" id="prod_stock_actual" required placeholder="0">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="prod_stock_minimo" class="form-label fw-bold">Stock Mínimo (Alerta) <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="stock_minimo" id="prod_stock_minimo" required placeholder="5">
                        </div>

                        <div class="col-12">
                            <label for="prod_descripcion" class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="prod_descripcion" rows="3" placeholder="Detalles técnicos, especificaciones o marca..."></textarea>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGuardarProducto">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Formulario Confirmación Desactivación -->
<form id="formCambiarEstado" action="actions/producto_action.php" method="POST" style="display: none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id_producto" id="status_id_producto" value="">
    <input type="hidden" name="nuevo_estado" id="status_nuevo_estado" value="">
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
