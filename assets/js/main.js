/**
 * LÓGICA Y VALIDACIONES JAVASCRIPT CLIENTE + MODO OSCURO
 * Sistema de Inventario TecnoStock
 */

// Inicialización Inmediata de Tema (evita parpadeo al cargar)
(function () {
    const savedTheme = localStorage.getItem('tecnostock_theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

document.addEventListener('DOMContentLoaded', function () {
    
    // Configurar Botón de Alternar Modo Oscuro
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        actualizarIconoTema();
        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = (currentTheme === 'light') ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('tecnostock_theme', newTheme);
            actualizarIconoTema();
        });
    }

    // Auto-cierre de alertas después de 6 segundos
    const alertList = document.querySelectorAll('.alert-dismissible');
    alertList.forEach(function (alertEl) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });

    // Event listener para botones de "Entrar Stock Rápido" en Dashboard
    document.querySelectorAll('.btn-quick-entrada').forEach(btn => {
        btn.addEventListener('click', function() {
            const idProd = this.getAttribute('data-id');
            const selectProd = document.getElementById('modal_id_producto');
            if (selectProd) {
                selectProd.value = idProd;
                document.getElementById('tipo_entrada').checked = true;
            }
        });
    });

    // Validaciones de formularios en cliente
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', function (e) {
            const precio = parseFloat(document.getElementById('prod_precio').value || 0);
            const stockActual = parseInt(document.getElementById('prod_stock_actual').value || 0);
            const stockMinimo = parseInt(document.getElementById('prod_stock_minimo').value || 0);

            if (precio < 0 || stockActual < 0 || stockMinimo < 0) {
                e.preventDefault();
                alert('¡Error de validación! El precio, stock actual y stock mínimo no pueden ser valores negativos.');
                return false;
            }
        });
    }

    const formMovimiento = document.getElementById('formMovimiento');
    if (formMovimiento) {
        formMovimiento.addEventListener('submit', function(e) {
            const selectProd = document.getElementById('mov_id_producto');
            const selectedOpt = selectProd.options[selectProd.selectedIndex];
            const stockDisponible = parseInt(selectedOpt.getAttribute('data-stock') || 0);
            const tipo = document.querySelector('input[name="tipo"]:checked').value;
            const cantidad = parseInt(document.getElementById('mov_cantidad').value || 0);

            if (cantidad <= 0) {
                e.preventDefault();
                alert('La cantidad del movimiento debe ser un número entero mayor a cero.');
                return false;
            }

            if (tipo === 'SALIDA' && cantidad > stockDisponible) {
                e.preventDefault();
                alert(`¡Regla de Negocio Incumplida!\n\nNo se puede registrar la SALIDA de ${cantidad} unidad(es).\nEl stock disponible del producto seleccionado es de solo ${stockDisponible} unidad(es).`);
                return false;
            }
        });
    }
});

// Función para actualizar el icono Sol/Luna en el botón de tema
function actualizarIconoTema() {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (!themeToggleBtn) return;
    
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    if (currentTheme === 'dark') {
        themeToggleBtn.innerHTML = '<i class="fa-solid fa-sun text-warning" title="Cambiar a Modo Claro"></i>';
    } else {
        themeToggleBtn.innerHTML = '<i class="fa-solid fa-moon text-secondary" title="Cambiar a Modo Oscuro"></i>';
    }
}

// Preparar modal para crear nuevo producto
function prepararCrearProducto() {
    document.getElementById('modalProductoTitulo').innerHTML = '<i class="fa-solid fa-box me-2 text-primary"></i>Nuevo Producto';
    document.getElementById('prod_action').value = 'create';
    document.getElementById('prod_id_producto').value = '';
    document.getElementById('formProducto').reset();
    document.getElementById('btnGuardarProducto').textContent = 'Guardar Producto';
}

// Preparar modal para editar producto existente
function prepararEditarProducto(producto) {
    document.getElementById('modalProductoTitulo').innerHTML = '<i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Editar Producto [' + producto.codigo + ']';
    document.getElementById('prod_action').value = 'update';
    document.getElementById('prod_id_producto').value = producto.id_producto;
    
    document.getElementById('prod_codigo').value = producto.codigo;
    document.getElementById('prod_nombre').value = producto.nombre;
    document.getElementById('prod_id_categoria').value = producto.id_categoria;
    document.getElementById('prod_precio').value = producto.precio;
    document.getElementById('prod_stock_actual').value = producto.stock_actual;
    document.getElementById('prod_stock_minimo').value = producto.stock_minimo;
    document.getElementById('prod_descripcion').value = producto.descripcion || '';

    document.getElementById('btnGuardarProducto').textContent = 'Actualizar Cambios';

    const modalEl = document.getElementById('modalProducto');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
}

// Confirmar cambio de estado / desactivación (Soft Delete)
function confirmarCambioEstado(idProducto, nuevoEstado, nombreProducto, totalMovimientos) {
    let accionTexto = (nuevoEstado === 0) ? 'desactivar' : 'reactivar';
    let advertencia = '';

    if (nuevoEstado === 0 && totalMovimientos > 0) {
        advertencia = `\n\nNota: Este producto registra ${totalMovimientos} movimiento(s) de inventario. Se aplicará Desactivación Lógica (Soft Delete) conservando todo su historial intacto.`;
    }

    if (confirm(`¿Está seguro de que desea ${accionTexto} el producto "${nombreProducto}"?${advertencia}`)) {
        document.getElementById('status_id_producto').value = idProducto;
        document.getElementById('status_nuevo_estado').value = nuevoEstado;
        document.getElementById('formCambiarEstado').submit();
    }
}

// Preparar modal de categorías
function prepararCrearCategoria() {
    document.getElementById('modalCategoriaTitulo').innerHTML = '<i class="fa-solid fa-tags me-2 text-primary"></i>Nueva Categoría';
    document.getElementById('cat_action').value = 'create';
    document.getElementById('cat_id_categoria').value = '';
    document.getElementById('group_cat_estado').style.display = 'none';
    document.getElementById('formCategoria').reset();
}

function prepararEditarCategoria(categoria) {
    document.getElementById('modalCategoriaTitulo').innerHTML = '<i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Editar Categoría';
    document.getElementById('cat_action').value = 'update';
    document.getElementById('cat_id_categoria').value = categoria.id_categoria;
    
    document.getElementById('cat_nombre').value = categoria.nombre;
    document.getElementById('cat_descripcion').value = categoria.descripcion || '';
    document.getElementById('cat_estado').value = categoria.estado;
    document.getElementById('group_cat_estado').style.display = 'block';

    const modalEl = document.getElementById('modalCategoria');
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
}

// Actualizar indicador de stock en formulario de movimientos
function actualizarInfoStock(selectEl) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const stock = selectedOpt.getAttribute('data-stock');
    const infoEl = document.getElementById('infoStockDisponible');
    if (infoEl) {
        if (stock !== null && selectEl.value !== '') {
            infoEl.innerHTML = `Stock disponible actualmente: <strong class="${parseInt(stock) > 0 ? 'text-success' : 'text-danger'}">${stock} unidades</strong>`;
        } else {
            infoEl.innerHTML = `Stock disponible actualmente: <strong>-</strong>`;
        }
    }
}
