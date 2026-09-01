-- ============================================================
-- BASE DE DATOS: TecnoStock - Sistema de Inventario
-- CASO 1: Proyecto de Control de Inventario y Operaciones CRUD
-- Motor: MySQL 8.x / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tecnostock_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tecnostock_db`;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `movimientos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(100) NOT NULL UNIQUE,
  `clave` VARCHAR(255) NOT NULL,
  `estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: categorias
-- ------------------------------------------------------------
CREATE TABLE `categorias` (
  `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` TEXT NULL,
  `estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activa, 0: Inactiva',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE `productos` (
  `id_producto` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT NULL,
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock_actual` INT NOT NULL DEFAULT 0,
  `stock_minimo` INT NOT NULL DEFAULT 0,
  `estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
  `id_categoria` INT NOT NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `chk_precio_positivo` CHECK (`precio` >= 0),
  CONSTRAINT `chk_stock_actual_positivo` CHECK (`stock_actual` >= 0),
  CONSTRAINT `chk_stock_minimo_positivo` CHECK (`stock_minimo` >= 0),
  CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabla: movimientos
-- ------------------------------------------------------------
CREATE TABLE `movimientos` (
  `id_movimiento` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` ENUM('ENTRADA', 'SALIDA') NOT NULL,
  `cantidad` INT NOT NULL,
  `observacion` TEXT NULL,
  `id_producto` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  CONSTRAINT `chk_cantidad_positiva` CHECK (`cantidad` > 0),
  CONSTRAINT `fk_movimientos_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS SEMILLA (INITIAL SEED DATA)
-- ============================================================

-- Usuario Administrador por defecto (Contraseña: admin123)
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `clave`, `estado`) VALUES
(1, 'Encargado de Inventario', 'admin@tecnostock.cl', '$2y$10$eQT1n84HEnCqY9ORsgGBAuw1ZVXlyMfoX76W.NRgsTDaWalq0IzJq', 1);

-- Categorías iniciales
INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Periféricos', 'Teclados, mouse, audífonos y accesorios de entrada/salida.', 1),
(2, 'Almacenamiento', 'Discos duros, unidades SSD, pendrives y memorias SD.', 1),
(3, 'Conectividad y Cables', 'Cables HDMI, adaptadores USB-C, cables Ethernet y hubs.', 1),
(4, 'Audio y Video', 'Micrófonos, cámaras web, parlantes y captura de video.', 1);

-- Productos iniciales (Algunos con stock_actual < stock_minimo para probar alertas)
INSERT INTO `productos` (`id_producto`, `codigo`, `nombre`, `descripcion`, `precio`, `stock_actual`, `stock_minimo`, `estado`, `id_categoria`) VALUES
(1, 'TEC-001', 'Teclado Mecánico RGB Switch Red', 'Teclado mecánico para gaming y oficina con retroiluminación RGB.', 39990.00, 15, 5, 1, 1),
(2, 'TEC-002', 'Mouse Inalámbrico Ergonómico 1600 DPI', 'Mouse ergonómico receptor USB silencioso.', 14990.00, 3, 5, 1, 1),
(3, 'TEC-003', 'Disco Duro Externo 1TB USB 3.0', 'Disco duro portátil resistente a impactos.', 49990.00, 8, 10, 1, 2),
(4, 'TEC-004', 'Pendrive USB 3.1 64GB Metal', 'Unidad de almacenamiento flash de alta velocidad.', 8990.00, 45, 15, 1, 2),
(5, 'TEC-005', 'Cable HDMI 2.1 Ultra HD 4K 2 metros', 'Cable HDMI mallado de alta velocidad para monitores y TV.', 6990.00, 20, 5, 1, 3),
(6, 'TEC-006', 'Audífonos Gamer 7.1 Surround con Micrófono', 'Audífonos supraaurales con cancelación de ruido pasiva.', 29990.00, 2, 4, 1, 1);

-- Movimientos iniciales de prueba
INSERT INTO `movimientos` (`id_movimiento`, `fecha_hora`, `tipo`, `cantidad`, `observacion`, `id_producto`, `id_usuario`) VALUES
(1, NOW() - INTERVAL 5 DAY, 'ENTRADA', 20, 'Carga inicial de inventario proveedor TecnoImport.', 1, 1),
(2, NOW() - INTERVAL 4 DAY, 'SALIDA', 5, 'Venta local boleta N° 1042.', 1, 1),
(3, NOW() - INTERVAL 3 DAY, 'ENTRADA', 5, 'Ingreso stock inicial.', 2, 1),
(4, NOW() - INTERVAL 2 DAY, 'SALIDA', 2, 'Venta corporativa cliente TecnoSoluciones.', 2, 1),
(5, NOW() - INTERVAL 1 DAY, 'ENTRADA', 10, 'Recepción pedido bodega central.', 3, 1),
(6, NOW() - INTERVAL 1 HOUR, 'SALIDA', 2, 'Ajuste de inventario por muestra en vitrina.', 3, 1);
