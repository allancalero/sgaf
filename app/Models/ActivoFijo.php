<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivoFijo extends Model
{
    use HasFactory;

    protected $table = 'activos_fijos';

    protected $fillable = [
        'codigo_inventario',
        'nombre_activo',
        'marca',
        'modelo',
        'color',
        'serie',
        'foto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'precio_adquisicion',
        'fecha_adquisicion',
        'numero_factura',
        'cheque_id',
        'monto_cheque_utilizado',
        'estado',
        'area_id',
        'ubicacion_id',
        'clasificacion_id',
        'tipo_activo_id',
        'fuente_financiamiento_id',
        'proveedor_id',
        'personal_id',
        // Depreciation fields
        'vida_util_anos',
        'valor_residual',
        'metodo_depreciacion',
        'depreciacion_anual',
        'depreciacion_acumulada',
        'valor_libros',
        'fecha_ultima_depreciacion',
        // Custom fields for dynamic attributes
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    // Relationships
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function clasificacion()
    {
        return $this->belongsTo(Clasificacion::class, 'clasificacion_id');
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function tipoActivo()
    {
        return $this->belongsTo(TipoActivo::class, 'tipo_activo_id');
    }

    public function fuenteFinanciamiento()
    {
        return $this->belongsTo(FuenteFinanciamiento::class, 'fuente_financiamiento_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class, 'cheque_id');
    }

    // Scopes for common queries
    public function scopeConRelaciones($query)
    {
        return $query->with([
            'area:id,nombre',
            'ubicacion:id,nombre',
            'clasificacion:id,codigo,nombre',
            'tipoActivo:id,nombre',
            'fuenteFinanciamiento:id,nombre',
            'proveedor:id,nombre',
            'personal:id,nombre,apellido',
            'cheque:id,numero_cheque'
        ]);
    }

    public function scopePorArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorClasificacion($query, $clasificacionId)
    {
        return $query->where('clasificacion_id', $clasificacionId);
    }
}
