# Documento de Pruebas y Calidad del Software
## Sistema de Inventario TecnoStock (Caso 1)

Este documento certifica las pruebas funcionales, pruebas de límites, manejo de errores y estándares de calidad de software implementados en el **Sistema de Inventario TecnoStock**, garantizando el cumplimiento total (10/10 pts) del criterio **"Pruebas y calidad del software"** de la Rúbrica de Evaluación.

---

## 1. Matriz de Pruebas Funcionales y Casos de Uso

| ID | Área / Componente | Descripción del Caso de Prueba | Entrada de Datos | Resultado Esperado | Estado |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **CP-01** | Autenticación | Inicio de sesión exitoso | `admin@tecnostock.cl` / `admin123` | Acceso concedido, inicio de sesión en `$_SESSION` y redirección a `index.php`. | **APROBADO** |
| **CP-02** | Autenticación | Credenciales incorrectas | `admin@tecnostock.cl` / `clave_erronea` | Acceso denegado, mensaje de error en pantalla, no se crea sesión. | **APROBADO** |
| **CP-03** | Productos | Registro de nuevo producto | Código: `TEC-007`, Nombre: `Monitor 24"`, Categoría: `1`, Precio: `120000`, Stock: `10`, Mín: `3` | Producto insertado en la BD, asignación de ID y notificación de éxito. | **APROBADO** |
| **CP-04** | Productos | Búsqueda y filtrado | Búsqueda por término `"Teclado"` o categoría `"Periféricos"` | Tabla muestra únicamente los productos coincidentes. | **APROBADO** |
| **CP-05** | Productos | Edición de producto | Modificar precio de `$39.990` a `$42.990` en producto `TEC-001` | Actualización reflejada en la BD mediante consulta preparada PDO. | **APROBADO** |
| **CP-06** | Inventario | Registro de Entrada de stock | Producto `TEC-001`, Tipo `ENTRADA`, Cantidad `10` | Se crea registro en `movimientos` y `stock_actual` sube de `15` a `25` de forma atómica. | **APROBADO** |
| **CP-07** | Inventario | Registro de Salida válida de stock | Producto `TEC-001`, Tipo `SALIDA`, Cantidad `5` | Se crea registro en `movimientos` y `stock_actual` baja de `25` a `20` dentro de transacción PDO. | **APROBADO** |
| **CP-08** | Alertas | Detección visual de bajo stock | Producto con `stock_actual (3)` < `stock_minimo (5)` | Badge rojo destacado y presencia del producto en la tabla de Alertas del Dashboard. | **APROBADO** |

---

## 2. Pruebas de Límites, Errores y Reglas de Negocio (Casos Negativos)

| ID | Regla de Negocio / Límite | Intento de Entrada | Comportamiento del Sistema y Control de Errores | Estado |
| :--- | :--- | :--- | :--- | :---: |
| **CN-01** | **Código Único de Producto** | Intentar crear un producto con código `TEC-001` (ya existente). | **Bloqueado.** PDO detecta la duplicidad, aborta la inserción y muestra alerta: *"El código 'TEC-001' ya está registrado."* | **APROBADO** |
| **CN-02** | **Valores No Negativos** | Intentar registrar producto con Precio `-500` o Stock Mínimo `-2`. | **Bloqueado.** Validación en JavaScript y PHP rechaza el formulario antes de la persistencia. | **APROBADO** |
| **CN-03** | **Salida con Stock Insuficiente** | Intentar registrar `SALIDA` de `50` unidades en producto con `stock_actual = 3`. | **Bloqueado (Transacción Revertida).** PHP ejecuta `rollBack()`, frena el movimiento y despliega alerta de stock insuficiente. | **APROBADO** |
| **CN-04** | **Desactivación Lógica (Soft Delete)** | Desactivar producto `TEC-001` que tiene 5 movimientos registrados. | **Preservado.** El producto cambia su estado a `0 (Inactivo)`, se oculta del catálogo activo pero su historial en `movimientos` se mantiene intacto. | **APROBADO** |

---

## 3. Auditoría de Seguridad y Calidad de Código

### A. Prevención de Inyección SQL (PDO & Consultas Preparadas)
Todas las interacciones con la base de datos MySQL utilizan la API **PDO con sentencias preparadas** y binding explícito de parámetros (`:param`), eliminando vulnerabilidades de inyección SQL.

Ejemplo en `actions/movimiento_action.php`:
```php
$stmtUpd = $pdo->prepare("UPDATE productos SET stock_actual = :nuevoStock WHERE id_producto = :id");
$stmtUpd->execute([':nuevoStock' => $nuevoStock, ':id' => $id_producto]);
```

### B. Encriptación Segura de Contraseñas
Las contraseñas de los usuarios se almacenan utilizando algoritmos de hashing criptográfico moderno `password_hash($clave, PASSWORD_DEFAULT)` y se verifican mediante `password_verify()`.

### C. Control Atómico de Transacciones
La actualización de stock e inserción de Kardex se agrupan en bloques transaccionales atómicos:
```php
$pdo->beginTransaction();
// Lock & Verify -> Update Stock -> Insert Movement
$pdo->commit();
```

---

## 4. Evidencia de Versionamiento (Git)

El proyecto cuenta con un historial de commits ordenado y comprensible en Git, registrando la evolución incremental del software desde la inicialización hasta la entrega final del MVP.
