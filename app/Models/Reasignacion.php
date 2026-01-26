<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reasignacion extends Model
{
    use HasFactory;

    protected $table = 'reasignaciones';

    protected $fillable = [
        'activo_id',
        'area_anterior_id',
        'responsable_anterior_id',
        'area_nueva_id',
        'responsable_nuevo_id',
        'motivo',
        'observaciones',
        'foto_reasignacion',
        'fecha_reasignacion',
        'estado',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_reasignacion' => 'datetime',
    ];

    // Relationships
    public function activo()
    {
        return $this->belongsTo(ActivoFijo::class, 'activo_id');
    }

    public function areaAnterior()
    {
        return $this->belongsTo(Area::class, 'area_anterior_id');
    }

    public function areaNueva()
    {
        return $this->belongsTo(Area::class, 'area_nueva_id');
    }

    public function responsableAnterior()
    {
        return $this->belongsTo(Personal::class, 'responsable_anterior_id');
    }

    public function responsableNuevo()
    {
        return $this->belongsTo(Personal::class, 'responsable_nuevo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopePorActivo($query, $activoId)
    {
        return $query->where('activo_id', $activoId);
    }
}
