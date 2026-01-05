# Guía de Uso del Sistema de Logging Organizado

## Introducción

Este proyecto utiliza un sistema de logging organizado que separa los logs en carpetas por módulos, facilitando el debugging y el monitoreo del sistema.

## Estructura de Carpetas

Los logs se organizan en las siguientes carpetas dentro de `storage/logs/`:

```
storage/logs/
├── activos/           # Logs de gestión de activos fijos
├── catalogos/         # Logs de catálogos (áreas, ubicaciones, tipos, etc.)
├── cheques/           # Logs del módulo de cheques
├── sistema/           # Logs de administración del sistema
├── reportes/          # Logs de generación de reportes
├── errors/            # Logs de errores generales
├── queries/           # Logs de consultas SQL
└── laravel.log        # Log general (fallback)
```

## Configuración

### Retención de Logs

- **Rotación:** Diaria (se crea un nuevo archivo cada día)
- **Retención:** 30 días (archivos más antiguos se eliminan automáticamente)
- **Formato:** `nombre-YYYY-MM-DD.log`

### Niveles de Log

Los niveles disponibles son (de menor a mayor severidad):

1. `debug` - Información detallada para debugging
2. `info` - Eventos informativos generales
3. `notice` - Eventos normales pero significativos
4. `warning` - Advertencias que no son errores
5. `error` - Errores de runtime
6. `critical` - Condiciones críticas
7. `alert` - Se debe tomar acción inmediatamente
8. `emergency` - El sistema es inusable

## Uso del LogHelper

### Métodos Básicos

```php
use App\Helpers\LogHelper;

// Log en módulo de activos
LogHelper::activos()->info('Activo creado exitosamente', [
    'activo_id' => $activo->id,
    'nombre' => $activo->nombre,
]);

// Log en módulo de catálogos
LogHelper::catalogos()->warning('Área duplicada detectada', [
    'nombre' => $area->nombre,
]);

// Log de errores
LogHelper::errors()->error('Error al procesar archivo', [
    'archivo' => $filename,
    'error' => $e->getMessage(),
]);

// Log de sistema
LogHelper::sistema()->info('Usuario actualizado', [
    'user_id' => $user->id,
]);

// Log de reportes
LogHelper::reportes()->info('Reporte generado', [
    'tipo' => 'PDF',
    'registros' => $count,
]);

// Log de cheques
LogHelper::cheques()->info('Cheque emitido', [
    'cheque_id' => $cheque->id,
    'monto' => $cheque->monto,
]);

// Log de queries SQL
LogHelper::queries()->debug('Query ejecutada', [
    'sql' => $sql,
    'time_ms' => 150,
]);
```

### Log con Contexto de Usuario

```php
// Agregar información del usuario autenticado automáticamente
LogHelper::withUser('activos')->info('Activo modificado', [
    'activo_id' => $activo->id,
]);

// Resultado incluirá: user_id, user_email
```

### Log de Actividades Importantes

```php
// Para registrar actividades con contexto completo
LogHelper::activity('sistema', 'Usuario creado', [
    'nuevo_usuario_id' => $user->id,
    'rol' => $user->role,
]);

// Incluye automáticamente: user_id, user_email, timestamp, IP
```

## Logging de Queries SQL

### Configuración

El logging de queries SQL se activa automáticamente en modo debug o mediante variable de entorno:

```env
# En .env
APP_DEBUG=true
# o
LOG_SQL_QUERIES=true
```

### Comportamiento

- **Queries lentas (>1 segundo):** Se registran con nivel `warning`
- **Queries normales:** Se registran con nivel `debug`
- **Información incluida:** SQL, bindings, tiempo de ejecución

### Ejemplo de Log

```
[2025-12-17 19:00:00] queries.WARNING: Query lenta detectada
{
    "sql": "SELECT * FROM activos WHERE area_id = ?",
    "bindings": [5],
    "time_ms": 1250
}
```

## Middleware de Logging (Opcional)

El middleware `LogRequestMiddleware` registra automáticamente todas las peticiones HTTP en el canal correspondiente.

### Activar el Middleware

1. Registrar en `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\LogRequestMiddleware::class,
];
```

2. O aplicar a rutas específicas:

```php
Route::middleware(['auth', 'log.requests'])->group(function () {
    // Rutas que se registrarán
});
```

### Información Registrada

- Método HTTP
- URL completa
- Nombre de la ruta
- Código de respuesta
- Tiempo de ejecución

## Ejemplos Prácticos por Módulo

### Módulo de Activos

```php
use App\Helpers\LogHelper;

class ActivoFijoController extends Controller
{
    public function store(Request $request)
    {
        try {
            $activo = ActivoFijo::create($request->all());
            
            LogHelper::activos()->info('Activo fijo creado', [
                'activo_id' => $activo->id,
                'nombre' => $activo->nombre,
                'codigo' => $activo->codigo_inventario,
            ]);
            
            return redirect()->route('activos.index');
            
        } catch (\Exception $e) {
            LogHelper::errors()->error('Error al crear activo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors('Error al crear activo');
        }
    }
}
```

### Módulo de Catálogos

```php
class AreaController extends Controller
{
    public function destroy(Area $area)
    {
        LogHelper::catalogos()->warning('Intentando eliminar área', [
            'area_id' => $area->id,
            'nombre' => $area->nombre,
        ]);
        
        if ($area->activos()->count() > 0) {
            LogHelper::catalogos()->error('No se puede eliminar área con activos', [
                'area_id' => $area->id,
                'activos_count' => $area->activos()->count(),
            ]);
            
            return back()->withErrors('El área tiene activos asignados');
        }
        
        $area->delete();
        
        LogHelper::activity('catalogos', 'Área eliminada', [
            'area_id' => $area->id,
        ]);
        
        return redirect()->route('areas.index');
    }
}
```

### Generación de Reportes

```php
class ReportController extends Controller
{
    public function generarPDF(Request $request)
    {
        $inicio = microtime(true);
        
        LogHelper::reportes()->info('Iniciando generación de reporte PDF', [
            'tipo' => $request->tipo,
            'filtros' => $request->only(['area', 'estado']),
        ]);
        
        try {
            $pdf = PDF::loadView('reportes.inventario', $data);
            $duracion = round((microtime(true) - $inicio) * 1000, 2);
            
            LogHelper::reportes()->info('Reporte PDF generado exitosamente', [
                'tipo' => $request->tipo,
                'registros' => count($data),
                'duracion_ms' => $duracion,
            ]);
            
            return $pdf->download('reporte.pdf');
            
        } catch (\Exception $e) {
            LogHelper::reportes()->error('Error al generar reporte PDF', [
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
```

## Búsqueda y Análisis de Logs

### Ver Logs en Tiempo Real

```bash
# Ver todos los logs
php artisan pail

# Ver logs específicos de un canal
php artisan pail --filter="activos"

# Ver solo errores
php artisan pail --level=error
```

### Buscar en Archivos de Log

```bash
# PowerShell - Buscar en logs de activos
Select-String -Path "storage/logs/activos/*.log" -Pattern "Activo creado"

# Ver últimas líneas de un log
Get-Content "storage/logs/activos/activos-2025-12-17.log" -Tail 50

# Buscar errores del día
Select-String -Path "storage/logs/errors/errors-2025-12-17.log" -Pattern "ERROR"
```

## Mejores Prácticas

### 1. Usa el Canal Apropiado

```php
// ✅ Correcto
LogHelper::activos()->info('Activo creado');

// ❌ Incorrecto
Log::info('Activo creado'); // Va al log general
```

### 2. Incluye Contexto Relevante

```php
// ✅ Correcto - Con contexto útil
LogHelper::activos()->error('Error al actualizar activo', [
    'activo_id' => $id,
    'campos' => $request->only(['nombre', 'estado']),
    'error' => $e->getMessage(),
]);

// ❌ Incorrecto - Sin contexto
LogHelper::activos()->error('Error');
```

### 3. Usa el Nivel Apropiado

```php
// debug: Información detallada para desarrollo
LogHelper::activos()->debug('Validando datos del activo', $datos);

// info: Eventos normales importantes
LogHelper::activos()->info('Activo creado exitosamente', ['id' => $id]);

// warning: Algo inusual pero no es error
LogHelper::catalogos()->warning('Área duplicada detectada');

// error: Errores que necesitan atención
LogHelper::errors()->error('Fallo al conectar a base de datos', ['error' => $e]);
```

### 4. Log de Actividades Críticas

```php
// Para auditoría, siempre registra:
// - Creación/edición/eliminación de registros importantes
// - Cambios de permisos
// - Acceso a información sensible

LogHelper::activity('sistema', 'Permisos modificados', [
    'usuario_id' => $user->id,
    'permisos_nuevos' => $permisos,
]);
```

### 5. No Loguear Información Sensible

```php
// ❌ Nunca loguear
LogHelper::sistema()->info('Login', [
    'password' => $request->password, // ❌ NUNCA
    'token' => $token, // ❌ NUNCA
]);

// ✅ Correcto
LogHelper::sistema()->info('Login exitoso', [
    'user_id' => $user->id,
    'email' => $user->email,
]);
```

## Mantenimiento

### Limpieza Manual

```bash
# Eliminar logs antiguos manualmente (si es necesario)
php artisan cache:clear

# PowerShell - Eliminar logs de más de 60 días
Get-ChildItem -Path "storage/logs" -Recurse -File | 
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-60) } | 
    Remove-Item
```

### Monitorear Tamaño de Logs

```bash
# Ver tamaño de carpetas de logs
Get-ChildItem -Path "storage/logs" -Directory | 
    ForEach-Object { 
        $size = (Get-ChildItem $_.FullName -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB
        [PSCustomObject]@{
            Carpeta = $_.Name
            TamañoMB = [math]::Round($size, 2)
        }
    } | Format-Table
```

## Solución de Problemas

### Los Logs No Se Crean

1. Verificar permisos de escritura en `storage/logs/`
2. Revisar configuración en `config/logging.php`
3. Ejecutar `composer dump-autoload`

### Error "Class LogHelper not found"

```bash
composer dump-autoload
```

### Queries SQL No Aparecen

Verificar que esté activo en `.env`:

```env
APP_DEBUG=true
# o
LOG_SQL_QUERIES=true
```

---

**Última actualización:** 2025-12-17
