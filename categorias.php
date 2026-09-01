<?php
$tituloPagina = "Gestión de Categorías - TecnoStock";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id_categoria) AS total_productos
        FROM categorias c 
        ORDER BY c.nombre ASC
    ");
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar categorías: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h3 fw-bold text-dark mb-0">Categorías de Productos</h2>
        <p class="text-muted small mb-0">Agrupación y clasificación del catálogo de inventario.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="prepararCrearCategoria()">
        <i class="fa-solid fa-plus-circle me-1"></i> + Nueva Categoría
    </button>
</div>

<!-- Tabla de Categorías -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Categoría</th>
                        <th>Descripción</th>
                        <th class="text-center">Productos Asociados</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No se encontraron categorías registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr class="<?php echo ($cat['estado'] == 0) ? 'table-secondary text-muted' : ''; ?>">
                                <td><span class="text-muted small">#<?php echo $cat['id_categoria']; ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                <td class="small text-muted"><?php echo !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '<em>Sin descripción</em>'; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark fs-6 px-2"><?php echo $cat['total_productos']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($cat['estado'] == 1): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            title="Editar Categoría"
                                            onclick='prepararEditarCategoria(<?php echo json_encode($cat); ?>)'>
                                        <i class="fa-solid fa-pen"></i>
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

<!-- Modal Agregar / Editar Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalCategoriaTitulo"><i class="fa-solid fa-tags me-2 text-primary"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/categoria_action.php" method="POST" id="formCategoria">
                <input type="hidden" name="action" id="cat_action" value="create">
                <input type="hidden" name="id_categoria" id="cat_id_categoria" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="cat_nombre" class="form-label fw-bold">Nombre de la Categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre" id="cat_nombre" required placeholder="Ej: Redes y Telecomunicaciones">
                    </div>

                    <div class="mb-3">
                        <label for="cat_descripcion" class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="cat_descripcion" rows="3" placeholder="Descripción breve de los tipos de productos..."></textarea>
                    </div>

                    <div class="mb-3" id="group_cat_estado" style="display: none;">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="cat_estado" class="form-select">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnGuardarCategoria">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
