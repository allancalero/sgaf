# Documentación del Proyecto SGAF2 - Casos de Uso

## 1. Introducción
El **Sistema de Gestión de Activos Fijos (SGAF2)** es una plataforma integral diseñada para el control, monitoreo y administración de los bienes muebles e inmuebles de la institución. El sistema permite el seguimiento del ciclo de vida de los activos, desde su alta hasta su baja, pasando por reasignaciones, mantenimiento y auditorías.

## 2. Actores del Sistema
Basado en la configuración de roles y permisos (`RolePermissionSeeder.php`), se identifican los siguientes actores:

### 2.1 Administrador (`admin`)
*   **Rol:** Superusuario encargado de la configuración global y gestión crítica.
*   **Permisos:** Tienen acceso total a la gestión de activos, usuarios, seguridad y configuraciones del sistema.

### 2.2 Editor (`editor`)
*   **Rol:** Operativo encargado de la gestión diaria del inventario.
*   **Permisos:** Puede ver, crear y editar activos. No tiene acceso a configuraciones avanzadas de seguridad o auditoría del sistema global.

### 2.3 Consulta (`consulta`)
*   **Rol:** Usuario con acceso de solo lectura.
*   **Permisos:** Visualización de inventarios y reportes. Ideal para auditores externos o jefes de área.

---

## 3. Módulos y Casos de Uso

### 3.1 Módulo de Autenticación (`/login`)
**Actores:** Todos
*   **CU-01: Iniciar Sesión**
    *   El usuario ingresa credenciales (email/password).
    *   El sistema valida y otorga token de acceso.
*   **CU-02: Recuperar Contraseña**
    *   El usuario solicita restablecimiento vía correo.
*   **CU-03: Cambiar Contraseña (Primer Ingreso)**
    *   El sistema fuerza el cambio de contraseña si es el primer login.

### 3.2 Tablero de Control (`/dashboard`)
**Actores:** Todos (Vista personalizada según rol)
*   **CU-04: Visualizar Estadísticas Globales**
    *   Ver total de activos, valor monetario total.
    *   Ver gráficos de distribución por estado (Bueno, Malo, Regular).
*   **CU-05: Acciones Rápidas**
    *   Acceso directo a Generación de Etiquetas y Reportes.

### 3.3 Gestión de Activos (`/assets`)
**Actores:** Admin, Editor, Consulta (Solo lectura)
*   **CU-06: Registrar Nuevo Activo**
    *   Ingresar detalles: Código, Descripción, Marca, Modelo, Serie, Costo.
    *   Asignar ubicación y responsable inicial.
*   **CU-07: Editar Activo**
    *   Modificar detalles no críticos de un activo existente.
*   **CU-08: Dar de Baja Activo**
    *   Marcar activo como inactivo indicando motivo (Obsolescencia, Daño, Robo).
*   **CU-09: Consultar Ficha de Activo**
    *   Ver historial completo, depreciación y ubicación actual.

### 3.4 Etiquetas y QR (`/etiquetas-qr`)
**Actores:** Admin, Editor
*   **CU-10: Generar Código QR**
    *   Crear código QR único asociado al ID del activo.
*   **CU-11: Impresión Masiva**
    *   Seleccionar múltiples activos e imprimir planillas de etiquetas.
*   **CU-12: Escaneo de Activo**
    *   Utilizar la cámara del dispositivo para leer un QR y redirigir a la ficha del activo.

### 3.5 Reasignaciones (`/reasignaciones`)
**Actores:** Admin, Editor
*   **CU-13: Registrar Reasignación**
    *   Transferir la responsabilidad de un activo de un empleado a otro o cambio de ubicación.
    *   Generar acta de entrega-recepción digital.
*   **CU-14: Historial de Movimientos**
    *   Rastrear la cadena de custodia de un activo específico.

### 3.6 Reportes (`/reportes`)
**Actores:** Admin, Editor, Consulta
*   **CU-15: Generar Reporte General de Inventario**
    *   Exportar listado completo en PDF/Excel.
*   **CU-16: Reporte de Depreciación**
    *   Calcular valor actual de los activos basado en su vida útil.
*   **CU-17: Reporte por Ubicación/Área**
    *   Listar activos asignados a un departamento específico.

### 3.7 Recursos Humanos y Usuarios (`/recursos-humanos`, `/usuarios`)
**Actores:** Admin
*   **CU-18: Gestión de Empleados**
    *   Registrar personal apto para custodiar activos.
*   **CU-19: Gestión de Usuarios del Sistema**
    *   Crear cuentas de acceso al software y asignar roles.

### 3.8 Auditoría y Trazabilidad (`/auditoria`, `/trazabilidad`)
**Actores:** Admin
*   **CU-20: Ver Logs de Sistema**
    *   Revisar quién hizo qué y cuándo (Login, Ediciones, Eliminaciones).
*   **CU-21: Auditoría Física**
    *   Módulo para cotejar inventario físico vs sistema (Checklist).

### 3.9 Depreciación (`/depreciacion`)
**Actores:** Admin, Editor
*   **CU-22: Configurar Reglas de Depreciación**
    *   Definir porcentajes anuales por tipo de activo.
*   **CU-23: Ejecutar Cálculo de Depreciación**
    *   Proceso batch para actualizar valores contables.

### 3.10 Seguridad y Respaldo (`/seguridad`, `/respaldo`)
**Actores:** Admin
*   **CU-24: Gestión de Roles y Permisos**
    *   Configurar qué puede hacer cada rol.
*   **CU-25: Realizar Respaldo de Base de Datos**
    *   Generar dump SQL de la base de datos.
