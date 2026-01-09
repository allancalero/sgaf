# Caso de Uso 01: Iniciar Sesión

## Resumen
Acceso seguro al sistema para usuarios registrados.

## Actores
*   **Usuario** (Administrador, Editor, Consulta)

## Flujo Principal
1.  **Usuario**: Ingresa a la aplicación.
2.  **Sistema**: Muestra formulario de Login (Email y Contraseña).
3.  **Usuario**: Introduce credenciales y confirma.
4.  **Sistema**: Valida datos contra la Base de Datos.
5.  **Sistema**: 
    *   Genera Token de sesión.
    *   Redirige al **Dashboard**.

## Flujos Alternos
*   **Error de Datos**: Si las credenciales son incorrectas, el sistema muestra "Error: Credenciales inválidas" y permite reintentar.

## Diagrama de Caso de Uso (Mermaid)
Puedes visualizar este diagrama instalando una extensión como "Markdown Preview Mermaid Support" o pegando el código en [Mermaid Live Editor](https://mermaid.live).

```mermaid
usecaseDiagram
    actor Usuario
    package "Sistema SGAF2" {
        usecase "Iniciar Sesión" as UC1
        usecase "Validar Credenciales" as UC2
        usecase "Mostrar Dashboard" as UC3
        usecase "Mostrar Error" as UC4
    }

    Usuario --> UC1
    UC1 ..> UC2 : <<include>>
    UC1 --> UC3 : Exitoso
    UC1 --> UC4 : Fallido
```

## Elementos para Diagrama UML (Alternativo)
*   **Caso de Uso**: `<<processing>> Iniciar Sesión`
*   **Actor**: Usuario del Sistema
*   **Relaciones**: 
    - Incluye: `<<include>> Validar Credenciales`
