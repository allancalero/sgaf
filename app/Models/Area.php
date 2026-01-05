<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubicacion_id',
        'estado',
    ];

    // Relationships
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function activosFijos()
    {
        return $this->hasMany(ActivoFijo::class, 'area_id');
    }

    public function personal()
    {
        return $this->hasMany(Personal::class, 'area_id');
    }
}
