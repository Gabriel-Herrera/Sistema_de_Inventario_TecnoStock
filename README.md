# TecnoStock - Sistema de Control de Inventario (Caso 1)

Aplicación web desarrollada en **PHP 8 (PDO)** y **MySQL** para la gestión de productos, control de entradas/salidas de existencias, visualización de alertas de bajo stock y administración de catálogo del emprendimiento **TecnoStock**.

---

## Características Principales (MVP)

- **Autenticación de Usuarios**: Inicio de sesión seguro con contraseñas encriptadas mediante `password_hash()` y `password_verify()`.
- **Dashboard Operativo**: Tarjetas métricas en tiempo real con total de productos, unidades en stock, alertas de stock bajo y total de movimientos.
- **Gestión de Productos (CRUD)**: Registrar, consultar, buscar (por nombre, código o categoría), editar y desactivar productos.
- **Control Atómico de Inventario (Kardex)**: Registro de entradas y salidas con actualización automática de `stock_actual` asegurada mediante **transacciones PDO** (`beginTransaction`, `commit`, `rollBack`).
- **Alertas de Stock Bajo**: Indicador visual destacado cuando `stock_actual < stock_minimo`.
- **Desactivación Lógica (Soft Delete)**: Preservación de la integridad referencial para productos con movimientos asociados.
- **Doble Validación**: Validación inmediata en cliente con JavaScript + Validación estricta en servidor con PHP/PDO.
- **Seguridad**: Uso exclusivo de sentencias preparadas (Prepared Statements) para prevenir inyección SQL.

---

## Estructura del Proyecto

```
Sistemas de informacion/
├── actions/                  # Controladores / Procesadores PHP de formularios
│   ├── categoria_action.php  # CRUD de categorías
│   ├── login_action.php      # Autenticación y manejo de sesión
│   ├── movimiento_action.php # Registro atómico de entradas/salidas y stock
│   └── producto_action.php   # CRUD y soft delete de productos
├── assets/                   # Recursos frontend
│   ├── css/
│   │   └── style.css         # Estilos personalizados y alertas
│   └── js/
│       └── main.js           # Validaciones JS y comportamiento de modales
├── config/
│   └── db.php                # Conexión PDO a MySQL (UTF-8, Excepciones)
├── database/
│   └── tecnostock.sql        # Script SQL reproducible con tablas y datos iniciales
├── includes/                 # Layouts y helpers reutilizables
│   ├── auth.php              # Control de sesión
│   ├── footer.php            # Pie de página y scripts JS
│   └── header.php            # Navegación responsiva Bootstrap 5
├── categorias.php            # Vista de gestión de categorías
├── index.php                 # Dashboard principal y panel de alertas
├── login.php                 # Vista de inicio de sesión
├── logout.php                # Cierre de sesión seguro
├── movimientos.php           # Vista de historial Kardex (Entradas/Salidas)
├── productos.php             # Vista de catálogo de productos
├── GUIA_VIDEO_5MIN.md        # Guión estructurado para la grabación del video
├── PRUEBAS_Y_CALIDAD.md      # Matriz de casos de prueba y auditoría de calidad
└── README.md                 # Instrucciones y documentación general
```

---

## Requisitos de Instalación en XAMPP

1. **Servidor Local**: XAMPP (con Apache y MySQL / MariaDB activados).
2. **PHP**: Versión 8.0 o superior con extensión `pdo_mysql` habilitada.
3. **Ubicación**: Copiar la carpeta del proyecto dentro de `htdocs` de XAMPP:
   `C:\xampp\htdocs\Sistemas de informacion\`

---

## Configuración de la Base de Datos

1. Abrir phpMyAdmin en el navegador: `http://localhost/phpmyadmin`.
2. Crear la base de datos o importar directamente el script ubicado en:
   `database/tecnostock.sql`
3. El script creará automáticamente la base de datos `tecnostock_db`, la estructura de 4 tablas relacionales (`usuarios`, `categorias`, `productos`, `movimientos`) e insertará los datos semilla de prueba.

---

## Credenciales de Acceso por Defecto

- **URL de acceso**: `http://localhost/Sistemas%20de%20informacion/login.php`
- **Correo**: `admin@tecnostock.cl`
- **Contraseña**: `admin123`

---

## Verificación de Reglas de Negocio

1. **Código Único**: No se permite crear dos productos con el mismo código.
2. **Valores No Negativos**: Precios, stock actual y stock mínimo se restringen a `>= 0`.
3. **Protección contra Stock Negativo**: No es posible registrar una salida de inventario que supere la cantidad disponible en stock.
4. **Desactivación Lógica**: Si un producto posee movimientos en el Kardex, se deshabilita (`estado = 0`) sin eliminar su registro de la base de datos.
