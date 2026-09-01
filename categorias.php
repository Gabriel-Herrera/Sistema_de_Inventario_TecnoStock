<?php
$tituloPagina = "Categorías de Productos - TecnoStock";
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
    echo "<div class='alert alert-minimal-danger p-3 mb-4'>Error al cargar categorías: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold text-dark mb-0">Categorías de Productos</h2>
        <p class="text-muted small mb-0">Clasificación y agrupación del catálogo.</p>
    </div>
    <button type="button" class="btn btn-minimal-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria" onclick="prepararCrearCategoria()">
        <i class="fa-solid fa-plus me-1.5" style="font-size: 0.8rem;"></i> Nueva Categoría
    </button>
</div>

<!-- Tabla Minimalista de Categorías -->
<div class="card card-minimal overflow-hidden">
    <div class="table-responsive">
        <table class="table table-minimal">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th class="text-center">Productos Asociados</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted small">
                            No se encontraron categorías registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                        <tr class="<?php echo ($cat['estado'] == 0) ? 'opacity-50' : ''; ?>">
                            <td><span class="text-muted small" style="font-size: 0.75rem;">#<?php echo $cat['id_categoria']; ?></span></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                            <td class="small text-muted" style="font-size: 0.8rem;"><?php echo !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '&mdash;'; ?></td>
                            <td class="text-center">
                                <span class="badge badge-soft-secondary"><?php echo $cat['total_productos']; ?> ítems</span>
                            </td>
                            <td class="text-center">
                                <?php if ($cat['estado'] == 1): ?>
                                    <span class="badge badge-soft-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-minimal-outline py-1 px-2.5 btn-sm" 
                                        title="Editar Categoría"
                                        onclick='prepararEditarCategoria(<?php echo json_encode($cat); ?>)'>
                                    <i class="fa-solid fa-pen me-1" style="font-size: 0.75rem;"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar / Editar Categoría Minimalista -->
<div class="modal fade modal-minimal" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold text-dark" id="modalCategoriaTitulo"><i class="fa-solid fa-tags me-2 text-primary"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/categoria_action.php" method="POST" id="formCategoria">
                <input type="hidden" name="action" id="cat_action" value="create">
                <input type="hidden" name="id_categoria" id="cat_id_categoria" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="cat_nombre" class="form-label small fw-semibold text-secondary">Nombre de Categoría</label>
                        <input type="text" class="form-control" name="nombre" id="cat_nombre" required placeholder="Ej: Redes y Telecomunicaciones">
                    </div>

                    <div class="mb-3">
                        <label for="cat_descripcion" class="form-label small fw-semibold text-secondary">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="cat_descripcion" rows="3" placeholder="Descripción breve..."></textarea>
                    </div>

                    <div class="mb-3" id="group_cat_estado" style="display: none;">
                        <label class="form-label small fw-semibold text-secondary">Estado</label>
                        <select name="estado" id="cat_estado" class="form-select">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-minimal-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-minimal-primary btn-sm px-4" id="btnGuardarCategoria">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
