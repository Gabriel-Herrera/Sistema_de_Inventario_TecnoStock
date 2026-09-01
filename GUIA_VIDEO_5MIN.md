# Guión y Estructura para el Video Explicativo (Máximo 5 Minutos)
## Sistema de Inventario TecnoStock (Caso 1)

Este guión está optimizado para cubrir exactamente todos los requerimientos solicitados en la rúbrica para el **Criterio 6: Video Explicativo (10 puntos)** en un tiempo estricto de **4:45 a 5:00 minutos**.

---

## Cronograma y Minutaje Recomendado

```
+-----------------------------------------------------------------------------------+
| 0:00 - 0:45 | 1. Introducción, Caso de Negocio y Presentación del MVP             |
+-----------------------------------------------------------------------------------+
| 0:45 - 2:30 | 2. Demostración en Vivo de la Interfaz y Flujo Crítico             |
+-----------------------------------------------------------------------------------+
| 2:30 - 4:15 | 3. Recorrido del Código Fuente (PHP, PDO, JavaScript, MySQL)        |
+-----------------------------------------------------------------------------------+
| 4:15 - 5:00 | 4. Cumplimiento de Reglas de Negocio, Seguridad y Conclusión         |
+-----------------------------------------------------------------------------------+
```

---

## Guión Paso a Paso

### Bloque 1: Introducción y Contexto (0:00 – 0:45)
* **Pantalla**: Pantalla de Login (`login.php`) de TecnoStock.
* **Lo que debes decir**:
  > *"Hola, mi nombre es [Tu Nombre] y a continuación presentaré el Sistema de Inventario para la empresa TecnoStock, correspondiente al Caso 1.*
  > *El objetivo principal de esta aplicación web es resolver los problemas de duplicidad de códigos, inconsistencia en el stock disponible y falta de visibilidad que enfrentaba la empresa, proporcionando un MVP funcional desarrollado con PHP 8, PDO, MySQL y Bootstrap 5 sin frameworks complejos."*

---

### Bloque 2: Demostración de la Interfaz y Flujo Crítico (0:45 – 2:30)

1. **Inicio de Sesión (0:45 – 1:00)**:
   * Ingresa con `admin@tecnostock.cl` y la clave `admin123`.
   * Muestra cómo la sesión se inicia correctamente redirigiendo al Dashboard.

2. **Dashboard y Alerta de Stock Bajo (1:00 – 1:30)**:
   * Muestra las 4 tarjetas métricas (Total Productos, Stock Total, Alertas de Stock Bajo, Movimientos).
   * Destaca el banner de alerta rojo y la tabla de productos donde `stock_actual < stock_minimo` (por ejemplo, el Mouse Ergonómico o los Audífonos Gamer).

3. **Catálogo de Productos y Búsqueda (1:30 – 2:00)**:
   * Ve a la sección **Productos**. Muestra la tabla responsiva con badges de estado y stock.
   * Realiza una búsqueda rápida (ej: escribe "Teclado" o filtra por categoría "Periféricos").
   * Muestra el modal de **Nuevo Producto** señalando las validaciones.

4. **Entradas, Salidas y Reglas de Negocio (2:00 – 2:30)**:
   * Ve a **Entradas / Salidas** (o usa el botón rápido en el Dashboard).
   * Registra una **Entrada** de 10 unidades para un producto con bajo stock y muestra cómo el stock se actualiza automáticamente.
   * **Prueba la regla de stock negativo**: Intenta registrar una **Salida** superior al stock disponible actual (ej: pedir 100 unidades cuando hay 15). Muestra cómo el sistema bloquea la operación y despliega el mensaje de error.

---

### Bloque 3: Recorrido del Código Fuente Relevante (2:30 – 4:15)

Abre Visual Studio Code y muestra los siguientes archivos clave:

1. **Base de Datos y Modelo (`database/tecnostock.sql`) (2:30 – 2:50)**:
   * Muestra las tablas `usuarios`, `categorias`, `productos` y `movimientos`.
   * Señala la restricción `UNIQUE` en el código de producto y las claves foráneas (`FOREIGN KEY`) para integridad referencial.

2. **Conexión Segura PDO (`config/db.php`) (2:50 – 3:15)**:
   * Muestra la inicialización de PDO con `ERRMODE_EXCEPTION` y codificación `utf8mb4`.

3. **Transacciones Atómicas de Inventario (`actions/movimiento_action.php`) (3:15 – 3:45)**:
   * Muestra las líneas de código donde se ejecuta:
     - `$pdo->beginTransaction()`
     - El bloqueo de lectura `FOR UPDATE`.
     - La verificación de que la salida no sea superior al stock disponible.
     - La actualización de `stock_actual` e inserción en `movimientos`.
     - `$pdo->commit()` o `$pdo->rollBack()`.

4. **Validaciones en Cliente (JS) y Servidor (PHP) (`assets/js/main.js` y `actions/producto_action.php`) (3:45 – 4:15)**:
   * Muestra la función en JavaScript que previene el envío de valores negativos y valida límites en el navegador.
   * Muestra la validación idéntica repetida en PHP para garantizar la seguridad en el servidor.

---

### Bloque 4: Reglas de Negocio, Seguridad y Conclusión (4:15 – 5:00)

* **Lo que debes decir**:
  > *"En resumen, el sistema cumple con todas las reglas de negocio exigidas: códigos únicos, no se admiten precios o stock negativos, la actualización de stock es automática y atómica con transacciones PDO, las salidas no pueden dejar stock bajo cero y los productos con movimientos aplican desactivación lógica conservando la integridad referencial.*
  > *Todo el código cuenta con consultas preparadas contra inyección SQL y un historial de versionamiento en Git.*
  > *Muchas gracias por su atención."*

---

## Checklist Previo a la Grabación

- [ ] Verificar que Apache y MySQL estén activos en XAMPP.
- [ ] Base de datos `tecnostock_db` importada con datos de prueba.
- [ ] VS Code abierto con la carpeta del proyecto y las pestañas listas (`movimiento_action.php`, `tecnostock.sql`, `main.js`).
- [ ] Micrófono configurado y pantalla clara.
- [ ] Cronómetro visible para no superar los 5:00 minutos.
