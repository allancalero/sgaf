<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudEliminacion extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_eliminacion';

    protected $fillable = [
        'activo_id',
        'solicitante_id',
        'motivo',
        'estado',
        'procesado_por',
        'nota_admin'
    ];

    // Relationships
    public function activo()
    {
        return $this->belongsTo(ActivoFijo::class, 'activo_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function procesador()
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }
}
