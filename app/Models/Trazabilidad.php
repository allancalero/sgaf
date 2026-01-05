<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trazabilidad extends Model
{
    use HasFactory;

    protected $table = 'trazabilidad';

    protected $fillable = [
        'activo_id',
        'tipo_movimiento',
        'ubicacion_id',
        'responsable_id',
        'area_id',
        'observaciones',
        'usuario_id',
    ];

    public function activo()
    {
        return $this->belongsTo(ActivoFijo::class, 'activo_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Personal::class, 'responsable_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
