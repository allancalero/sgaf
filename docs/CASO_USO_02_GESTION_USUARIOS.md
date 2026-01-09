# Caso de Uso 02: Gestionar Usuarios del Sistema

## Resumen
Permite al administrador administrar las cuentas de acceso al sistema (Altas, Bajas y Modificaciones).

## Actores
*   **Administrador del Sistema**

## Flujo Principal
1.  **Administrador**: Selecciona opción "Usuarios" en el menú.
2.  **Sistema**: Muestra lista de usuarios (Buscador y Tabla).
3.  **Administrador**: Puede realizar acciones de búsqueda o selección.
4.  **Sistema**: Muestra formulario de edición/creación según la acción.

## Diagrama (Estilo UWE - MagicDraw)

### Elementos Principales
*   **Actor**: Administrador del sistema
*   **Caso de Uso Padre**: `<<processing>> Gestionar Usuarios`

### Casos de Uso Incluidos (Relaciones `<<include>>`)
1.  `<<browsing>> Buscar Usuarios`: Filtrar la lista por nombre o rol.
2.  `<<processing>> Editar datos de Usuario`: Modificar email, nombre o rol.
3.  `<<processing>> Asignar contraseña`: Establecer o resetear password.
4.  `<<processing>> Crear Usuario Nuevo`: Registrar un nuevo empleado en el sistema.

## Reglas de Negocio
*   No se puede eliminar al propio usuario logueado.
*   El email debe ser único en el sistema.
*   La contraseña debe tener mínimo 8 caracteres.

## Código Mermaid (Referencia Visual)
```mermaid
usecaseDiagram
    actor Admin as "Administrador del sistema"
    
    package "Gestionar Usuarios del Sistema" {
        usecase "Gestionar Usuarios" as CU_Main <<processing>>
        usecase "Buscar Usuarios" as CU_Search <<browsing>>
        usecase "Editar datos" as CU_Edit <<processing>>
        usecase "Asignar contraseña" as CU_Pass <<processing>>
    }

    Admin --> CU_Main
    CU_Main ..> CU_Search : <<include>>
    CU_Main ..> CU_Edit : <<include>>
    CU_Main ..> CU_Pass : <<include>>
```
