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
    ];
}
